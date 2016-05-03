<?php

/**
 * Simple image entity
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */
namespace Sergsxm\UIBundle\Form;

use Sergsxm\UIBundle\Classes\ImageInterface;

class Image extends File implements ImageInterface
{
    
/**
 * Get image size
 * 
 * @return array Height and width array
 */    
    public function getImageSize()
    {
        if (($this->contentFile == '') || !is_file($this->contentFile)) {
            return null;
        }
        $imageInfo = @getimagesize($this->contentFile);
        if (!is_array($imageInfo) || !isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return null;
        }
        return array(
            'width' => $imageInfo[0],
            'height' => $imageInfo[1]
        );
    }

/**
 * Get image width
 * 
 * @return int Image width
 */    
    public function getImageWidth()
    {
        if (($this->contentFile == '') || !is_file($this->contentFile)) {
            return null;
        }
        $imageInfo = @getimagesize($this->contentFile);
        if (!is_array($imageInfo) || !isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return null;
        }
        return $imageInfo[0];
    }

/**
 * Get image height
 * 
 * @return ing Image height
 */    
    public function getImageHeight()
    {
        if (($this->contentFile == '') || !is_file($this->contentFile)) {
            return null;
        }
        $imageInfo = @getimagesize($this->contentFile);
        if (!is_array($imageInfo) || !isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return null;
        }
        return $imageInfo[1];
    }

}
