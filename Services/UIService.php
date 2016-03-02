<?php

/**
 * UI service
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\Services;

use Sergsxm\UIBundle\Exceptions\FormException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class UIService implements CacheWarmerInterface
{
    
    private $container;
    private $cacheDir;
    private $debugTrigger;
    
    public function __construct(Container $container) 
    {
        $this->container = $container;
        $this->cacheDir = $this->container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.'sergsxm';
        $this->debugTrigger = $this->container->getParameter('kernel.debug');
    }

/**
 * Update cache file with list of input types
 * 
 * @return boolean Is result successfull
 */
    private function updateCache($cacheDir = null) 
    {
        if ($cacheDir == null) {
            $cacheDir = $this->cacheDir;
        }
        
        $formInputTypes = array();
        $captchaTypes = array();
        
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            if (file_exists($bundle->getPath().DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'ui.yml')) {
                $parameters = YamlParser::parse($bundle->getPath().DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'ui.yml');
                
                if (isset($parameters['form_input_types']) && is_array($parameters['form_input_types'])) {
                    foreach ($parameters['form_input_types'] as $typeName => $typeClass) {
                        if (is_string($typeClass)) {
                            $formInputTypes[$typeName] = $typeClass;
                        }
                    }
                }
                if (isset($parameters['captcha_types']) && is_array($parameters['captcha_types'])) {
                    foreach ($parameters['captcha_types'] as $typeName => $typeClass) {
                        if (is_string($typeClass)) {
                            $captchaTypes[$typeName] = $typeClass;
                        }
                    }
                }
            }
        }
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir);
        }
        
        $f = fopen($cacheDir.DIRECTORY_SEPARATOR.'formInputTypes.php', "w");
        fwrite($f, "<?php\r\n");
        fwrite($f, "return array(\r\n");
        foreach ($formInputTypes as $typeName => $typeClass) {
            fwrite($f, "\t\t'$typeName' => '$typeClass',\r\n");
        }
        fwrite($f, "\t);\r\n");
        fclose($f);
        
        unset($formInputTypes);
        
        $f = fopen($cacheDir.DIRECTORY_SEPARATOR.'captchaTypes.php', "w");
        fwrite($f, "<?php\r\n");
        fwrite($f, "return array(\r\n");
        foreach ($captchaTypes as $typeName => $typeClass) {
            fwrite($f, "\t\t'$typeName' => '$typeClass',\r\n");
        }
        fwrite($f, "\t);\r\n");
        fclose($f);
        
        unset($captchaTypes);
    }

/**
 * Get list of form input type classes
 * 
 * @return array List of form input types
 */    
    public function getFormInputTypes()
    {
        if (($this->debugTrigger == true) || (!file_exists($this->cacheDir.DIRECTORY_SEPARATOR.'formInputTypes.php'))) {
            $this->updateCache();
            $this->debugTrigger = false;
        }
        
        $types = include($this->cacheDir.DIRECTORY_SEPARATOR.'formInputTypes.php');
        return $types;
    }

/**
 * Get list of captcha type classes
 * 
 * @return array List of captcha types
 */    
    public function getCaptchaTypes()
    {
        if (($this->debugTrigger == true) || (!file_exists($this->cacheDir.DIRECTORY_SEPARATOR.'captchaTypes.php'))) {
            $this->updateCache();
            $this->debugTrigger = false;
        }
        
        $types = include($this->cacheDir.DIRECTORY_SEPARATOR.'captchaTypes.php');
        return $types;
    }
    
/**
 * Create form
 * 
 * @param object $mappingObject Object for input values mapping
 * @param string $action Action URL
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function createForm($mappingObject = null, $action = '')
    {
        return new \Sergsxm\UIBundle\Forms\Form($this->container, $mappingObject, $action);
    }

/**
 * Create table list
 * 
 * @return \Sergsxm\UIBundle\Classes\TableList Table list object
 */    
    public function createTableList()
    {
        return new \Sergsxm\UIBundle\Classes\TableList($this->container);
    }
    
/**
 * Cahce warm up interface
 */    
    public function warmUp($cacheDir) 
    {
        $this->updateCache($cacheDir.DIRECTORY_SEPARATOR.'sergsxm');
    }

    public function isOptional()
    {
        return false;
    }
    
}
