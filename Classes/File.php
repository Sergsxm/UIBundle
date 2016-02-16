<?php

/**
 * Simple file entity
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */
namespace Sergsxm\UIBundle\Classes;

class File implements FileInterface
{
    
    protected $fileName;
    protected $mimeType;
    protected $contentFile;
    protected $uploadDate;

/**
 * Get file name
 * 
 * @return string File name
 */    
    public function getFileName()
    {
        return $this->fileName;
    }
    
/**
 * Set file name
 * 
 * @param string $name File name
 */    
    public function setFileName($name)
    {
        $this->fileName = $name;
    }

/**
 * Get MIME type of file
 * 
 * @return string MIME type
 */    
    public function getMimeType()
    {
        return $this->mimeType;
    }

/**
 * Set MIME type of file
 * 
 * @param string $mimeType MIME type
 */    
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

/**
 * Get content file
 * 
 * @return string Content file
 */    
    public function getContentFile()
    {
        return $this->contentFile;
    }

/**
 * Set content file
 * 
 * @param string $contentFile Content file
 */    
    public function setContentFile($contentFile)
    {
        $this->contentFile = $contentFile;
    }

/**
 * Get upload date
 * 
 * @return \DateTime Upload date
 */    
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

/**
 * Set upload date
 * 
 * @param \DateTime $uploadDate Upload date
 */    
    public function setUploadDate(\DateTime $uploadDate)
    {
        $this->uploadDate = $uploadDate;
    }

/**
 * Get size of file
 * 
 * @return integer File size
 */    
    public function getSize()
    {
        if (($this->contentFile != '') && (is_file($this->contentFile))) {
            return filesize($this->contentFile);
        }
        return null;
    }

/**
 * Get file ID (in this type same as content file)
 * 
 * @return string File ID
 */    
    public function getId()
    {
        return $this->contentFile;
    }

/**
 * Restore file object by file ID
 * 
 * @param string $fileName File ID (same as content file)
 * @return \self File object
 */    
    public static function restore($fileName)
    {
        if (!file_exists($fileName)) {
            return null;
        }
        $value = new self();
        $value->setContentFile($fileName);
        
        $infoFile = $fileName.'.info';
        if (!file_exists($infoFile)) {
            return $value;
        }
        $info = @json_decode(file_get_contents($infoFile), true);
        if (isset($info['mimeType'])) {
            $value->setMimeType($info['mimeType']);
        }
        if (isset($info['fileName'])) {
            $value->setFileName($info['fileName']);
        }
        if (isset($info['uploadDate']) && is_array($info['uploadDate'])) {
            $value->setUploadDate(new \DateTime($info['uploadDate']['date'], new \DateTimeZone($info['uploadDate']['timezone'])));
        }
        return $value;
    }

/**
 * Store file info
 */    
    public function storeInfo()
    {
        $info = array(
            'mimeType' => $this->mimeType,
            'fileName' => $this->fileName,
            'uploadDate' => $this->uploadDate,
        );
        $infoFile = $this->contentFile.'.info';
        file_put_contents($infoFile, json_encode($info));
    }
    
}
