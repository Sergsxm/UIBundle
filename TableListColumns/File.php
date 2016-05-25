<?php

/**
 * File column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class File extends TableListColumn
{
    const ST_FILE = 0;
    const ST_DOCTRINE = 1;
    
    private $itemFiles = array();

/**
 * Set configuration to defaults
 */    
    protected function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->dql,
            'multiply' => false,
            'storeType' => self::ST_FILE,
            'implodeSeparator' => ',',
            'fileUrl' => null,
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
        if ($this->configuration['storeType'] == self::ST_FILE) {
            $this->columnIndex = $this->query->addColumn($this->dql);
        }
    }

/**
 * Process post queries
 * 
 * @param array $items All selected rows from database
 */    
    public function postQuery($items)
    {
        if ($this->configuration['storeType'] == self::ST_DOCTRINE) {
            $ids = array();
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
            $this->itemFiles = $this->query->getPartialFields($this->dql, $ids);
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
        if ($this->configuration['storeType'] == self::ST_FILE) {
            $columnName = $this->query->getColumnName($this->columnIndex);
            if (!isset($item[$columnName])) {
                return null;
            }
            if ($this->configuration['multiply'] == false) {
                $file = \Sergsxm\UIBundle\Form\File::restore($item[$columnName]);
                if ($file == null) {
                    return null;
                }
                $value = $this->wrapFileWithUrl($file->getFileName(), $item[$columnName]);
            } else {
                if (!is_array($item[$columnName])) {
                    return null;
                }
                $value = array();
                foreach ($item[$columnName] as $fileId) {
                    $file = \Sergsxm\UIBundle\Form\File::restore($fileId);
                    if ($file == null) {
                        continue;
                    }
                    $value[] = $this->wrapFileWithUrl($file->getFileName(), $fileId);
                }
                $value = implode($this->configuration['implodeSeparator'], $value);
            }
        } elseif ($this->configuration['storeType'] == self::ST_DOCTRINE) {
            $id = $item['id'];
            $itemFile = null;
            foreach ($this->itemFiles as $val) {
                if ($val->getId() == $id) {
                    $itemFile = $val;
                    break;
                }
            }
            if ($itemFile == null) {
                return null;
            }
            $reflector = new \ReflectionObject($itemFile);
            $property = $reflector->getProperty($this->dql);
            $property->setAccessible(true);
            $rawValue = $property->getValue($itemFile);
            if ($this->configuration['multiply'] == false) {
                if (!$rawValue instanceof \Sergsxm\UIBundle\Classes\FileInterface) {
                    return null;
                }
                $value = $this->wrapFileWithUrl($rawValue->getFileName(), $rawValue->getId());
            } else {
                if (!$rawValue instanceof \Doctrine\Common\Collections\Collection) {
                    return null;
                }
                $value = array();
                foreach ($rawValue->toArray() as $cat) {
                    if (!$cat instanceof \Sergsxm\UIBundle\Classes\FileInterface) {
                        continue;
                    }
                    $value[] = $this->wrapFileWithUrl($cat->getFileName(), $cat->getId());
                }
                $value = implode($this->configuration['implodeSeparator'], $value);
            }
        } else {
            return null;
        }
        return $value;
    }

/**
 * Wrap string with url
 * 
 * @param midex $value Value
 * @param int $id ID
 * @return string Output value
 */    
    protected function wrapFileWithUrl($value, $id)
    {
        if (!isset($this->configuration['fileUrl']) || ($this->configuration['fileUrl'] == '')) {
            return $value;
        }
        if (strpos($this->configuration['fileUrl'], '/') === false) {
            $actionUrl = $this->container->get('router')->generate($this->configuration['fileUrl'], array('id' => $id));
        } else {
            $actionUrl = str_replace('{{id}}', $id, $this->configuration['fileUrl']);
        }
        return '<a href="'.$actionUrl.'">'.$value.'</a>';
    }
    
}
