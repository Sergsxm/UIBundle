<?php

/**
 * Checkbox column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class Checkbox extends TableListColumn
{
    
/**
 * Set configuration to defaults
 */    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'uncheckedValue' => false,
            'checkedValue' => true,
            'orderEnabled' => true,
            'uncheckedPattern' => '<i class="glyphicon glyphicon-remove"></i>',
            'checkedPattern' => '<i class="glyphicon glyphicon-ok"></i>',
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
        $this->columnIndex = $this->query->addColumn($this->dql);
        if (($orderDirection !== null) && ($this->configuration['orderEnabled'] == true)) {
            $this->query->order($this->columnIndex, $orderDirection);
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
        $columnName = $this->query->getColumnName($this->columnIndex);
        if (!isset($item[$columnName])) {
            return null;
        }
        if ($item[$columnName] === $this->configuration['checkedValue']) {
            $value = $this->configuration['checkedPattern'];
        } else {
            $value = $this->configuration['uncheckedPattern'];
        }
        $value = $this->wrapWithUrl($value, $item['id']);
        return $value;
    }
    
}
