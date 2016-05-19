<?php

/**
 * Image column class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableListColumns;

use Sergsxm\UIBundle\TableList\TableListColumn;

class Image extends TableListColumn
{
    const ST_FILE = 0;
    const ST_DOCTRINE = 1;
    
    private $itemImages = array();

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
            'imageUrl' => null,
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
            $this->itemImages = $this->query->getPartialFields($this->dql, $ids);
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
                $image = \Sergsxm\UIBundle\Form\Image::restore($item[$columnName]);
                if ($image == null) {
                    return null;
                }
                $value = $this->wrapImageWithUrl($image->getFileName(), $item[$columnName]);
            } else {
                if (!is_array($item[$columnName])) {
                    return null;
                }
                $value = array();
                foreach ($item[$columnName] as $imageId) {
                    $image = \Sergsxm\UIBundle\Form\Image::restore($imageId);
                    if ($image == null) {
                        continue;
                    }
                    $value[] = $this->wrapImageWithUrl($image->getFileName(), $imageId);
                }
                $value = implode($this->configuration['implodeSeparator'], $value);
            }
        } elseif ($this->configuration['storeType'] == self::ST_DOCTRINE) {
            $id = $item['id'];
            $itemImage = null;
            foreach ($this->itemImages as $val) {
                if ($val->getId() == $id) {
                    $itemImage = $val;
                    break;
                }
            }
            if ($itemImage == null) {
                return null;
            }
            $reflector = new \ReflectionObject($itemImage);
            $property = $reflector->getProperty($this->dql);
            $property->setAccessible(true);
            $rawValue = $property->getValue($itemImage);
            if ($this->configuration['multiply'] == false) {
                if (!$rawValue instanceof \Sergsxm\UIBundle\Classes\ImageInterface) {
                    return null;
                }
                $value = $this->wrapImageWithUrl($rawValue->getFileName(), $rawValue->getId());
            } else {
                if (!$rawValue instanceof \Doctrine\Common\Collections\ArrayCollection) {
                    return null;
                }
                $value = array();
                foreach ($rawValue->toArray() as $cat) {
                    if (!$cat instanceof \Sergsxm\UIBundle\Classes\ImageInterface) {
                        continue;
                    }
                    $value[] = $this->wrapImageWithUrl($cat->getFileName(), $cat->getId());
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
    protected function wrapImageWithUrl($value, $id)
    {
        if (!isset($this->configuration['imageUrl']) || ($this->configuration['imageUrl'] == '')) {
            return $value;
        }
        if (strpos($this->configuration['imageUrl'], '/') === false) {
            $actionUrl = $this->container->get('router')->generate($this->configuration['imageUrl'], array('id' => $id));
        } else {
            $actionUrl = str_replace('{{id}}', $id, $this->configuration['imageUrl']);
        }
        return '<a href="'.$actionUrl.'"><img src="'.$actionUrl.'" title="'.$value.'" /></a>';
    }
    
}
