<?php

/**
 * UI service
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sergsxm\UIBundle\Classes\UIExtensionInterface;

class UIService
{
    
    private $container;
    private $formInputTypes;
    private $captchaTypes;

/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony container
 * @param array $formInputTypes Basic form input types
 * @param array $captchaTypes Basic captcha types
 */    
    public function __construct(ContainerInterface $container, $formInputTypes, $captchaTypes) 
    {
        $this->container = $container;
        $this->formInputTypes = $formInputTypes;
        $this->captchaTypes = $captchaTypes;
    }

/**
 * Add extension to UIBundle
 * 
 * @param UIExtensionInterface $extension Extension
 */
    public function addExtension(UIExtensionInterface $extension)
    {
        $this->formInputTypes = array_merge($this->formInputTypes, $extension->getFormInputTypes());
        $this->captchaTypes = array_merge($this->captchaTypes, $extension->getCaptchaTypes());
    }
    
/**
 * Get list of form input type classes
 * 
 * @return array List of form input types
 */    
    public function getFormInputTypes()
    {
        return $this->formInputTypes;
    }

/**
 * Get list of captcha type classes
 * 
 * @return array List of captcha types
 */    
    public function getCaptchaTypes()
    {
        return $this->captchaTypes;
    }
    
/**
 * Create form
 * 
 * @param object $mappingObject Object for input values mapping
 * @param string $action Action URL
 * @return \Sergsxm\UIBundle\Form\Form Form object
 */    
    public function createForm($mappingObject = null, $action = '')
    {
        return new \Sergsxm\UIBundle\Form\Form($this->container, $mappingObject, $action);
    }

/**
 * Create table list
 * 
 * @return \Sergsxm\UIBundle\TableList\TableList Table list object
 */    
    public function createTableList()
    {
        return new \Sergsxm\UIBundle\TableList\TableList($this->container);
    }
    
/**
 * Create tree form
 * 
 * @param array $configuration Form configuration
 * @param array $objects Tree objects
 * @return \Sergsxm\UIBundle\TreeForm\TreeForm Form object
 */    
    public function createTreeForm($configuration = array(), $objects = array())
    {
        return new \Sergsxm\UIBundle\TreeForm\TreeForm($this->container, $configuration, $objects);
    }

/**
 * Create order form
 * 
 * @param array $configuration Form configuration
 * @param array $objects Order form objects
 * @return \Sergsxm\UIBundle\OrderForm\OrderForm Form object
 */    
    public function createOrderForm($configuration = array(), $objects = array())
    {
        return new \Sergsxm\UIBundle\OrderForm\OrderForm($this->container, $configuration, $objects);
    }
    
}
