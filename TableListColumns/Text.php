<?php

/**
 * Text column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class Text extends TableListColumn
{
    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'orderEnabled' => true,
            'searchEnabled' => false,
            'pattern' => '{{text}}',
        );
    }
    
    public function modifyQuery($orderDirection = null, $searchString = '')
    {
        if ($this->columnIndex !== null) {
            return;
        }
        $this->columnIndex = $this->query->addColumn($this->dql);
        if (($searchString != '') && ($this->configuration['searchEnabled'] == true)) {
            $this->query->where($this->columnIndex, 'LIKE :value', $searchString);
        }
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
        $value = str_replace('{{text}}', htmlentities($item[$columnName]), $this->configuration['pattern']);
        $value = $this->wrapWithUrl($value, $item['id']);
        return $value;
    }
    
}
