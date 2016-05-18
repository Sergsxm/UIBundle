<?php

/**
 * Table list column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableList;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sergsxm\UIBundle\TableList\TableListException;
use Sergsxm\UIBundle\TableList\TableListQuery;

abstract class TableListColumn
{
    
    protected $container;
    protected $dql;
    protected $query;
    protected $configuration;
    protected $columnIndex = null;
    
    public function __construct(ContainerInterface $container, TableListQuery $query, $dql, $configuration = array())
    {
        $this->container = $container;
        $this->query = $query;
        $this->dql = $dql;
        $this->setDefaults();
        $this->configuration = array_merge($this->configuration, $configuration);
    }

    abstract protected function setDefaults();
    
    public function modifyQuery($orderDirection = null, $searchString = '')
    {
        if ($this->columnIndex !== null) {
            return;
        }
        $this->columnIndex = $this->query->addColumn($this->dql);
        if ($orderDirection !== null) {
            $this->query->order($this->columnIndex, $orderDirection);
        }
    }
    
    public function convertValue($item)
    {
        $columnName = $this->query->getColumnName($this->columnIndex);
        return (isset($item[$columnName]) ? $item[$columnName] : null);
    }
    
    public function getView()
    {
        return array(
            'configuration' => $this->configuration,
            'columnIndex' => $this->columnIndex,
            'description' => (isset($this->configuration['description']) ? $this->configuration['description'] : $this->dql),
            'hidden' => (isset($this->configuration['hidden']) ? $this->configuration['hidden'] : false),
            'orderEnabled' => (isset($this->configuration['orderEnabled']) ? $this->configuration['orderEnabled'] : false),
            'searchEnabled' => (isset($this->configuration['searchEnabled']) ? $this->configuration['searchEnabled'] : false),
        );
    }
    
    protected function wrapWithUrl($value, $id)
    {
        if (!isset($this->configuration['url']) || ($this->configuration['url'] == '')) {
            return $value;
        }
        if (strpos($this->configuration['url'], '/') === false) {
            $actionUrl = $this->container->get('router')->generate($this->configuration['url'], array('id' => $id));
        } else {
            $actionUrl = str_replace('{{id}}', $id, $this->configuration['url']);
        }
        return '<a href="'.$actionUrl.'">'.$value.'</a>';
    }
    
    
}
