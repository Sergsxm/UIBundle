<?php

/**
 * File entity interface
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

interface FileInterface
{
    
    public function getFileName();
    
    public function setFileName($name);
    
    public function getMimeType();
    
    public function setMimeType($mimeType);
    
    public function getContentFile();
    
    public function setContentFile($contentFile);
    
    public function getUploadDate();
    
    public function setUploadDate(\DateTime $uploadDate);
            
    public function getSize();
    
    public function getId();
    
}
