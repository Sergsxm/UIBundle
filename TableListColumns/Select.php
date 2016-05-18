<?php

/**
 * Select column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class Select extends TableListColumn
{
    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'choices' => array(),
            'multiply' => false,
            'explodeValue' => false,
            'explodeSeparator' => ',',
            'orderEnabled' => true,
            'implodeSeparator' => ',',
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
        if ($this->configuration['multiply'] == false) {
            if (!isset($this->configuration['choices'][$item[$columnName]])) {
                return null;
            }
            $value = $this->configuration['choices'][$item[$columnName]];
        } else {
            $inValue = $item[$columnName];
            $outValue = array();
            if ($this->configuration['explodeValue']) {
                $inValue = explode($this->configuration['explodeSeparator'], $inValue);
            }
            if (!is_array($inValue)) {
                return null;
            }
            foreach ($inValue as $val) {
                if (isset($this->configuration['choices'][$val])) {
                    $outValue = $this->configuration['choices'][$val];
                }
            }
            $value = implode($this->configuration['implodeSeparator'], $outValue);
        }
        $value = $this->wrapWithUrl($value, $item['id']);
        return $value;
    }
    
}
