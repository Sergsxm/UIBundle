<?php

/**
 * Abstract class for form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Form\FormException;
use Sergsxm\UIBundle\Form\FormBag;

abstract class FormInput
{
    const JS_EOL = "\r\n";
    
    protected $container;
    protected $formBag;
    protected $configuration;
    protected $prefix;
    protected $name;
    protected $value = null;
    protected $mappingObject = null;
    protected $mappingProperty;
    protected $error = null;
    protected $disabled = false;
    
/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param string $prefix Input prefix
 * @param object $mappingObject Object for input value mapping
 */
    public function __construct(ContainerInterface $container, FormBag $formBag, $name, $configuration = array(), $prefix = '', $mappingObject = null)
    {
        $this->container = $container;
        $this->formBag = $formBag;
        
        if (!$name) {
            throw new FormException(__CLASS__.': name cannot be null');
        }
        $this->name = $name;
        
        $this->setDefaults();
        $this->configuration = array_merge($this->configuration, $configuration);
        
        $this->prefix = $prefix;
        
        if ((!isset($this->configuration['mapping'])) || ($this->configuration['mapping'] == true)) {
            $this->mappingObject = $mappingObject;
        }
        if ($this->mappingObject !== null) {
            $reflector = new \ReflectionObject($mappingObject);
            $this->mappingProperty = $reflector->getProperty($name);
            $this->mappingProperty->setAccessible(true);
            $this->value = $this->mappingProperty->getValue($mappingObject);
        }
        if (isset($configuration['disabled'])) {
            $this->disabled = $configuration['disabled'];
        }
    }
    
/**
 * Get type of form input
 * 
 * @return string Type
 */
    abstract public function getType();

/**
 * Get default template for input
 * 
 * @return string Default template
 */
    abstract public function getDefaultTemplate();
    
/**
 * Set configuration to default values
 */
    abstract public function setDefaults();

/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    abstract public function validateValue();
    
/**
 * Get value
 * 
 * @return mixed Value
 */    
    public function getValue()
    {
        return $this->value;
    }

/**
 * Set value
 * 
 * @param mixed $value Value
 * @return boolean There are no errors
 */
    public function setValue($value)
    {
        $this->value = $value;
        if ($this->mappingObject !== null) {
            $this->mappingProperty->setValue($this->mappingObject, $value);
        }
        return $this->validateValue();
    }

/**
 * Get error description
 * 
 * @return string Error description
 */    
    public function getError()
    {
        return $this->error;
    }
    
/**
 * Bind form request
 * 
 * @param Request $request Symfony2 request object
 * @param string $prefix Input prefix
 * @return boolean Value is accepted and there are no errors
 */    
    public function bindRequest(Request $request = null, $prefix = '')
    {
        if ($this->disabled == true) {
            return true;
        }
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $value = $request->get($prefix.$this->prefix.$this->name);
            return $this->setValue($value);
        } else {
            return false;
        }
    }

/**
 * Get view array for input template
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return array View array
 */    
    public function getInputView($idPrefix)
    {
        return array(
            'type' => $this->getType(),
            'defaultTemplate' => $this->getDefaultTemplate(),
            'name' => $this->name,
            'inputName' => $this->prefix.$this->name,
            'inputId' => $idPrefix.$this->prefix.$this->name,
            'configuration' => $this->configuration,
            'value' => $this->value,
            'error' => $this->error,
            'disabled' => $this->disabled,
        );
    }
    
/**
 * Get javascript validation text for input
 * 
 * @param string $idPrefix Prefix for input id element
 * @return string Javascript code
 */    
    abstract public function getJsValidation($idPrefix);    
    
}
