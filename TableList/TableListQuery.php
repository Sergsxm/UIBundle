<?php

/**
 * Table list query class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableList;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sergsxm\UIBundle\TableList\TableListException;
use Sergsxm\UIBundle\TableList\TableListTab;

class TableListQuery
{
    const WT_AND = 1;
    const WT_OR = 2;
    const WT_XOR = 3;
    
    private $entityManager;
    private $repository;
    private $cols;
    private $colNames;
    private $joins = array();
    private $whereRoot;
    private $whereCurrent;
    private $order = '';
    private $parameters = array();

    public function __construct(EntityManagerInterface $em, $repository, $whereType = self::WT_AND)
    {
        $this->entityManager = $em;
        $this->repository = $repository;
        $this->cols = array('item.id');
        $this->colNames = array('id');
        $this->whereRoot = new TableListQueryWhere($whereType);
        $this->whereCurrent = $this->whereRoot;
    }
    
    public function addColumn($dql)
    {
        $colIndex = count($this->cols);
        if (strpos(strtolower($dql), 'select ') !== false) {
            $this->cols[$colIndex] = '('.$dql.')';
            $this->colNames[$colIndex] = 'col'.$colIndex;
        } elseif (preg_match('/^(([\w\d]+)\.)?([\w\d]+)\s+((((left|right|inner|outer)\s+)?join\s+[\w\d:\.]+)(\s+[\w\d]+)?(\s+on\s+.+)?)$/ui', $dql, $matches)) {
            $normalizeJoin = trim(preg_replace('/\s+/', ' ', $matches[5].' t{{index}} '.str_replace($matches[2].'.', 't{{index}}.', (isset($matches[9]) ? $matches[9] : ''))));
            $joinIndex = null;
            foreach ($this->joins as $key=>$join) {
                if (strtolower($join) == strtolower(str_replace('{{index}}', $key, $normalizeJoin))) {
                    $joinIndex = $key;
                    break;
                }
            }
            if ($joinIndex === null) {
                $joinIndex = count($this->joins);
                $this->joins[$joinIndex] = str_replace('{{index}}', $joinIndex, $normalizeJoin);
            }
            $this->cols[$colIndex] = "t$joinIndex.{$matches[3]}";
            $this->colNames[$colIndex] = 'col'.$colIndex;
        } else {
            if (preg_match('/^[a-zA-Z]+$/ui', $dql)) {
                $dql = 'item.'.$dql;
            }
            $this->cols[$colIndex] = $dql;
            $this->colNames[$colIndex] = 'col'.$colIndex;
        }
        return $colIndex;
    }
    
    public function getColumnName($columnIndex)
    {
        if (isset($this->colNames[$columnIndex])) {
            return $this->colNames[$columnIndex];
        }
        return null;
    }

    public function openWhereGroup($whereType)
    {
        $group = $this->whereCurrent->openGroup($whereType);
        $this->whereCurrent = $group;
        return $group;
    }
    
    public function closeWhereGroup()
    {
        $group = $this->whereCurrent->closeGroup();
        $this->whereCurrent = $group;
        return $group;
    }
    
    public function where($columnIndex, $condition, $parameter)
    {
        if (!isset($this->cols[$columnIndex])) {
            throw new TableListException(__CLASS__.": column $columnIndex not found");
        }
        $parameterIndex = null;
        foreach ($this->parameters as $parameterKey=>$parameterVal) {
            if ($parameterVal === $parameter) {
                $parameterIndex = $parameterKey;
                break;
            }
        }
        if ($parameterIndex === null) {
            $parameterIndex = 'param'.count($this->parameters);
            $this->parameters[$parameterIndex] = $parameter;
        }
        $dql = $this->cols[$columnIndex].' '.preg_replace('/:[\w\d]+/', ":$parameterIndex", $condition);
        $this->whereCurrent->add($dql);
    }
    
    public function order($columnIndex, $direction)
    {
        if (!isset($this->cols[$columnIndex])) {
            throw new TableListException(__CLASS__.": column $columnIndex not found");
        }
        $this->order = $this->colNames[$columnIndex].' '.($direction == 0 ? 'ASC' : 'DESC');
    }
    
    private function getQuery()
    {
        $select = array();
        foreach ($this->cols as $key=>$col) {
            $select[] = $col.' as '.$this->colNames[$key];
        }
        $where = $this->whereRoot->getDql();
        return 'SELECT '.implode(', ', $select).' FROM '.$this->repository.' item '.implode(' ', $this->joins).($where != '' ? ' WHERE '.$where : '').($this->order != '' ? 'ORDER BY '.$this->order : '');
    }
    
    private function getCountQuery()
    {
        $where = $this->whereRoot->getDql();
        return 'SELECT count(item.id) FROM '.$this->repository.' item '.implode(' ', $this->joins).($where != '' ? ' WHERE '.$where : '');
    }
    
    public function getCount()
    {
        return $this->entityManager->createQuery($this->getCountQuery())->setParameters($this->parameters)->getSingleScalarResult();
    }
    
    public function getResult($start, $count)
    {
        return $this->entityManager->createQuery($this->getQuery())->setParameters($this->parameters)->setFirstResult($start)->setMaxResults($count)->getResult();
    }
    
}

class TableListQueryWhere
{
    private $parent;
    private $glue;
    private $conditions = array();
    private $groups = array();
    
    public function __construct($type, $parent = null)
    {
        if (!in_array($type, array(TableListQuery::WT_AND, TableListQuery::WT_OR, TableListQuery::WT_XOR))) {
            throw new TableListException(__CLASS__.': unkown WHERE type');
        }
        if ($type == TableListQuery::WT_AND) {
            $this->glue = ' AND ';
        } elseif ($type == TableListQuery::WT_OR) {
            $this->glue = ' OR ';
        } elseif ($type == TableListQuery::WT_XOR) {
            $this->glue = ' XOR ';
        }        
        $this->parent = $parent;
    }
    
    public function add($dql)
    {
        $this->conditions[] = $dql;
    }
    
    public function openGroup($type)
    {
        $gorup = new TableListQueryWhere($type, $this);
        $this->groups[] = $gorup;
        return $group;
    }
    
    public function closeGroup()
    {
        return $this->parent;
    }
    
    public function getDql()
    {
        $groupConditions = array();
        foreach ($this->groups as $group) {
            $dql = $group->getDql();
            if ($dql != '') {
                $groupConditions[] = '('.$dql.')';
            }
        }
        return implode($this->glue, array_merge($groupConditions, $this->conditions));
    }
    
}