<?php

/**
 * Tag column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class Tag extends TableListColumn
{
    private $itemTags = array();
    
/**
 * Set configuration to defaults
 */    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'implodeSeparator' => ' ',
            'pattern' => '<span class="label sergsxmui-label">{{tag}}</span>',
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
    }

/**
 * Process post queries
 * 
 * @param array $items All selected rows from database
 */    
    public function postQuery($items)
    {
        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item['id'];
        }
        $this->itemTags = $this->query->getPartialFields($this->dql, $ids);
    }
    
/**
 * Convert value from database to output HTML
 * 
 * @param array $item Row from database
 * @return string Output HTML
 */    
    public function convertValue($item)
    {
        $id = $item['id'];
        $itemTag = null;
        foreach ($this->itemTags as $val) {
            if ($val->getId() == $id) {
                $itemTag = $val;
                break;
            }
        }
        if ($itemTag == null) {
            return null;
        }
        $reflector = new \ReflectionObject($itemTag);
        $property = $reflector->getProperty($this->dql);
        $property->setAccessible(true);
        $rawValue = $property->getValue($itemTag);
        if (!$rawValue instanceof \Doctrine\Common\Collections\Collection) {
            return null;
        }
        $value = array();
        foreach ($rawValue->toArray() as $tag) {
            if (!$tag instanceof \Sergsxm\UIBundle\Classes\TagInterface) {
                continue;
            }
            $value[] = str_replace('{{tag}}', $cat->getTag(), $this->configuration['pattern']);
        }
        $value = implode($this->configuration['implodeSeparator'], $value);
        return $value;
    }

}
