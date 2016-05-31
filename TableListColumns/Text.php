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
    
/**
 * Set configuration to defaults
 */    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'orderEnabled' => true,
            'searchEnabled' => false,
            'pattern' => '{{text}}',
            'textLimit' => null,
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
            $this->query->where($this->columnIndex, 'LIKE :value', $searchString);
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
        $value = $item[$columnName];
        if ($this->configuration['textLimit'] !== null) {
            if (function_exists('mb_strlen') && function_exists('mb_substr') && (mb_strlen($value) > $this->configuration['textLimit'])) {
                $value = mb_substr($value, 0, $this->configuration['textLimit']).'...';
            } elseif (strlen($value) > $this->configuration['textLimit']) {
                $value = substr($value, 0, $this->configuration['textLimit']).'...';
            }
        }
        $value = str_replace('{{text}}', htmlentities($value), $this->configuration['pattern']);
        $value = $this->wrapWithUrl($value, $item['id']);
        return $value;
    }
    
}
