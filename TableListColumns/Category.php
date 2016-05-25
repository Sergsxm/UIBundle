<?php

/**
 * Category column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class Category extends TableListColumn
{
    private $itemCategories = array();
    
/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony container
 * @param TableListQuery $query Table list query object
 * @param string $dql DQL string of column
 * @param array $configuration Column configuration
 */    
    public function __construct(ContainerInterface $container, TableListQuery $query, $dql, $configuration = array())
    {
        parent::__construct($container, $query, $dql, $configuration);
        if (($this->configuration['loadDoctrineRepository'] !== null) && ($this->configuration['mapIdToValue'] == true)) {
            $this->configuration['categories'] = $this->container->get('doctrine')->getRepository($this->configuration['loadDoctrineRepository'])->findAll();
        }
    }    
/**
 * Set configuration to defaults
 */    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'categories' => array(),
            'multiply' => false,
            'mapIdToValue' => false,
            'loadDoctrineRepository' => null,
            'implodeSeparator' => ',',
            'categoryUrl' => null,
        );
    }
    
/**
 * Modify table list query 
 * 
 * @param int|null $orderDirection Order direction (null - none, 0 - asc, 1 -desc)
 * @param string $searchString Search string for text fields
 */    
    public function modifyQuery($orderDirection = null, $searchString = '')
    {
        if ($this->columnIndex !== null) {
            return;
        }
        if ($this->configuration['mapIdToValue'] == true) {
            $this->columnIndex = $this->query->addColumn($this->dql);
        }
    }

/**
 * Process post queries
 * 
 * @param array $items All selected rows from database
 */    
    public function postQuery($items)
    {
        if ($this->configuration['mapIdToValue'] == false) {
            $ids = array();
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
            $this->itemCategories = $this->query->getPartialFields($this->dql, $ids);
        }
    }
    
/**
 * Convert value from database to output HTML
 * 
 * @param array $item Row from database
 * @return string Output HTML
 */    
    public function convertValue($item)
    {
        if ($this->configuration['mapIdToValue'] == true) {
            $columnName = $this->query->getColumnName($this->columnIndex);
            if (!isset($item[$columnName])) {
                return null;
            }
            if ($this->configuration['multiply'] == false) {
                $value = null;
                foreach ($this->configuration['categories'] as $category) {
                    if (!$category instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                        continue;
                    }
                    if ($category->getId() == $item[$columnName]) {
                        $value = $this->wrapCategoryWithUrl($category->getTitle(), $category->getId());
                        break;
                    }
                }
            } else {
                if (!is_array($item[$columnName])) {
                    return null;
                }
                $value = array();
                foreach ($this->configuration['categories'] as $category) {
                    if (!$category instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                        continue;
                    }
                    if (in_array($category->getId(), $item[$columnName])) {
                        $value[] = $this->wrapCategoryWithUrl($category->getTitle(), $category->getId());
                    }
                }
                $value = implode($this->configuration['implodeSeparator'], $value);
            }
        } else {
            $id = $item['id'];
            $itemCategory = null;
            foreach ($this->itemCategories as $val) {
                if ($val->getId() == $id) {
                    $itemCategory = $val;
                    break;
                }
            }
            if ($itemCategory == null) {
                return null;
            }
            $reflector = new \ReflectionObject($itemCategory);
            $property = $reflector->getProperty($this->dql);
            $property->setAccessible(true);
            $rawValue = $property->getValue($itemCategory);
            if ($this->configuration['multiply'] == false) {
                if (!$rawValue instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                    return null;
                }
                $value = $this->wrapCategoryWithUrl($rawValue->getTitle(), $rawValue->getId());
            } else {
                if (!$rawValue instanceof \Doctrine\Common\Collections\Collection) {
                    return null;
                }
                $value = array();
                foreach ($rawValue->toArray() as $cat) {
                    if (!$cat instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                        continue;
                    }
                    $value[] = $this->wrapCategoryWithUrl($cat->getTitle(), $cat->getId());
                }
                $value = implode($this->configuration['implodeSeparator'], $value);
            }
        }
        return $value;
    }

/**
 * Wrap string with url
 * 
 * @param midex $value Value
 * @param int $id ID
 * @return string Output value
 */    
    protected function wrapCategoryWithUrl($value, $id)
    {
        if (!isset($this->configuration['categoryUrl']) || ($this->configuration['categoryUrl'] == '')) {
            return $value;
        }
        if (strpos($this->configuration['categoryUrl'], '/') === false) {
            $actionUrl = $this->container->get('router')->generate($this->configuration['categoryUrl'], array('id' => $id));
        } else {
            $actionUrl = str_replace('{{id}}', $id, $this->configuration['categoryUrl']);
        }
        return '<a href="'.$actionUrl.'">'.$value.'</a>';
    }
    
}
