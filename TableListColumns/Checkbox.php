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
    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'uncheckedValue' => false,
            'checkedValue' => true,
            'orderEnabled' => true,
            'uncheckedPattern' => '<i class="fa fa-times"></i>',
            'checkedPattern' => '<i class="fa fa-check"></i>',
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
        if ($item[$columnName] === $this->configuration['checkedValue']) {
            $value = $this->configuration['checkedPattern'];
        } else {
            $value = $this->configuration['uncheckedPattern'];
        }
        $value = $this->wrapWithUrl($value, $item['id']);
        return $value;
    }
    
}
