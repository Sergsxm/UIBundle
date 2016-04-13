<?php

/**
 * Image entity interface
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

interface ImageInterface extends FileInterface
{
    
    public function getImageSize();
    
    public function getImageWidth();
    
    public function getImageHeight();
    
}
