<?php

/**
 * Number column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class Number extends TableListColumn
{
    
/**
 * Set configuration to defaults
 */    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'decimalPoint' => '.',
            'thousandSeparator' => '',
            'decimals' => null,
            'orderEnabled' => true,
            'searchEnabled' => false,
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
        if (($searchString != '') && ($this->configuration['searchEnabled'] == true)) {
            $this->query->where($this->columnIndex, '= :value', floatval($searchString));
        }
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
        $value = number_format($item[$columnName], $this->configuration['decimals'], $this->configuration['decimalPoint'], $this->configuration['thousandSeparator']);
        $value = $this->wrapWithUrl($value, $item['id']);
        return $value;
    }
    
}
