<?php

/**
 * Time stamp column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class TimeStamp extends TableListColumn
{
    
/**
 * Set configuration to defaults
 */    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'dateTimeFormat' => 'Y-m-d\TH:i:s',
            'timeZone' => null,
            'orderEnabled' => true,
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
        if ($this->configuration['timeZone'] != null) {
            if ($this->configuration['timeZone'] == 'default') {
                $this->configuration['timeZone'] = date_default_timezone_get();
            }
            if (!$this->configuration['timeZone'] instanceof \DateTimeZone) {
                $this->configuration['timeZone'] = new \DateTimeZone($this->configuration['timeZone']);
            }
        }
        $columnName = $this->query->getColumnName($this->columnIndex);
        if (!isset($item[$columnName])) {
            return null;
        }
        
        if (($item[$columnName] instanceof \DateTime) && ($this->configuration['timeZone'] != null)) {
            $item[$columnName]->setTimezone($this->configuration['timeZone']);
        }
        $value = ($item[$columnName] instanceof \DateTime ? $item[$columnName]->format($this->configuration['dateTimeFormat']) : '');
        $value = $this->wrapWithUrl($value, $item['id']);
        return $value;
    }
    
}
