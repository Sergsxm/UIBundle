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
        $this->container = $container;
        $this->query = $query;
        $this->dql = $dql;
        $this->setDefaults();
        $this->configuration = array_merge($this->configuration, $configuration);
    }

/**
 * Set configuration to defaults
 */    
    abstract protected function setDefaults();

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
        $this->columnIndex = $this->query->addColumn($this->dql);
        if ($orderDirection !== null) {
            $this->query->order($this->columnIndex, $orderDirection);
        }
    }

/**
 * Process post queries
 * 
 * @param array $items All selected rows from database
 */    
    public function postQuery($items)
    {
    }
    
/**
 * Convert value from database to output HTML
 * 
 * @param array $item Row from database
 * @return string Output HTML
 */    
    public function convertValue($item)
    {
        $columnName = $this->query->getColumnName($this->columnIndex);
        return (isset($item[$columnName]) ? $item[$columnName] : null);
    }

/**
 * Get view of column
 * 
 * @return array View array
 */    
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

/**
 * Wrap string with url
 * 
 * @param midex $value Value
 * @param int $id ID
 * @return string Output value
 */    
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
