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
    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'decimalPoint' => '.',
            'thousandSeparator' => '',
            'decimals' => null,
            'orderEnabled' => true,
        );
    }
    
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
