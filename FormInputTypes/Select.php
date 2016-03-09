<?php

/**
 * Select form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Classes\FormInput;
use Sergsxm\UIBundle\Exceptions\FormException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Classes\FormBag;

class Select extends FormInput
{

/**
 * Constructor
 * 
 * @param Container $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param string $prefix Input prefix
 * @param class $mappingObject Object for input value mapping
 */
    public function __construct(Container $container, FormBag $formBag, $name, $configuration = array(), $prefix = '', $mappingObject = null)
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
            if (($this->configuration['explodeValue'] == true) && ($this->configuration['multiply'] == true)) {
                $this->value = explode($this->configuration['explodeSeparator'], $this->mappingProperty->getValue($mappingObject));
            } else {
                $this->value = $this->mappingProperty->getValue($mappingObject);
            }
        }
    }
    
/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'select';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Select.html.twig';
    }
    
/**
 * Set configuration to default values
 */
    public function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->name,
            'choices' => array(),
            'choicesError' => 'The field contain bad value',
            'required' => false,
            'requiredError' => 'The field can not be empty',
            'multiply' => false,
            'expanded' => false,
            'explodeValue' => false,
            'explodeSeparator' => ',',
        );
    }

/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if ($this->configuration['multiply'] == true) {
            if (($this->configuration['required'] == true) && (count($this->value) == 0)) {
                $this->error = $this->configuration['requiredError'];
                return false;
            }
            foreach ($this->value as $value) {
                if (($value === null) || ($value === '') || !isset($this->configuration['choices'][$value])) {
                    $this->error = $this->configuration['choicesError'];
                    return false;
                }
            }
        } else {
            if (($this->value === null) || ($this->value === '') || !isset($this->configuration['choices'][$this->value])) {
                $this->error = $this->configuration['choicesError'];
                return false;
            }
        }
        $this->error = null;
        return true;
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
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $value = $request->get($prefix.$this->prefix.$this->name);
            if (($this->configuration['multiply'] == true) && (!is_array($value))) {
                $value = array();
            }
            return $this->setValue($value);
        } else {
            return false;
        }
    }

/**
 * Get value
 * 
 * @return mixed Value
 */    
    public function getValue()
    {
        if (($this->configuration['explodeValue'] == true) && ($this->configuration['multiply'] == true)) {
            return implode($this->configuration['explodeSeparator'], $this->value);
        }
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
            if (($this->configuration['explodeValue'] == true) && ($this->configuration['multiply'] == true)) {
                $this->mappingProperty->setValue($this->mappingObject, implode($this->configuration['explodeSeparator'], $value));
            } else {
                $this->mappingProperty->setValue($this->mappingObject, $value);
            }
        }
        return $this->validateValue();
    }
    
/**
 * Get javascript validation text for input
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return string Javascript code
 */    
    public function getJsValidation($idPrefix)
    {
        $code = '';
        if (($this->configuration['multiply'] == true) && ($this->configuration['required'] == true)) {
            $code .= 'var j = 0;if (form["'.$this->prefix.$this->name.'"].length != undefined) {for (var i in form["'.$this->prefix.$this->name.'"]) {if ((form["'.$this->prefix.$this->name.'"][i].selected) || (form["'.$this->prefix.$this->name.'"][i].checked)) {j++;}}}if (j == 0) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
        return $code;
    }
    
}
