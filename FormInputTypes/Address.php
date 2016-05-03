<?php

/**
 * Address form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Form\FormInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sergsxm\UIBundle\Form\FormBag;

class Address extends FormInput
{

    private $coordinatesValue = null;
    private $mappingCoordinatesProperty = null;

/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param string $prefix Input prefix
 * @param class $mappingObject Object for input value mapping
 */
    public function __construct(ContainerInterface $container, FormBag $formBag, $name, $configuration = array(), $prefix = '', $mappingObject = null)
    {
        parent::__construct($container, $formBag, $name, $configuration, $prefix, $mappingObject);
        if (($this->mappingObject !== null) && ($this->configuration['mappingCoordinatesProperty'] != '')) {
            $reflector = new \ReflectionObject($mappingObject);
            $this->mappingCoordinatesProperty = $reflector->getProperty($this->configuration['mappingCoordinatesProperty']);
            $this->mappingCoordinatesProperty->setAccessible(true);
            $this->coordinatesValue = $this->mappingCoordinatesProperty->getValue($mappingObject);
            if (!is_array($this->coordinatesValue) || (count($this->coordinatesValue) != 2)) {
                $this->coordinatesValue = null;
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
        return 'address';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Address.html.twig';
    }
    
/**
 * Set configuration to default values
 */
    public function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->name,
            'required' => false,
            'requiredError' => 'The field can not be empty',
            'mapEnabled' => false,
            'mappingCoordinatesProperty' => null,
        );
    }

/**
 * Set value
 * 
 * @param mixed $value Value
 * @return boolean There are no errors
 */
    public function setValue($value, $coordinates = null)
    {
        $this->value = $value;
        if (is_array($coordinates) && (count($coordinates) == 2)) {
            $this->coordinatesValue = $coordinates;
        } else {
            $this->coordinatesValue = null;
        }
        if ($this->mappingObject !== null) {
            $this->mappingProperty->setValue($this->mappingObject, $this->value);
            if ($this->configuration['mappingCoordinatesProperty'] != '') {
                $this->mappingCoordinatesProperty->setValue($this->mappingObject, $this->coordinatesValue);
            }
        }
        return $this->validateValue();
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
            $latitude = $request->get($prefix.$this->prefix.$this->name.'_latitude');
            $longitude = $request->get($prefix.$this->prefix.$this->name.'_longitude');
            if (($latitude != null) && ($longitude != null)) {
                $coordinates = array($latitude, $longitude);
            } else {
                $coordinates = null;
            }
            return $this->setValue($value, $coordinates);
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
            'inputNameLatitude' => $this->prefix.$this->name.'_latitude',
            'inputNameLongitude' => $this->prefix.$this->name.'_longitude',
            'inputId' => $idPrefix.$this->prefix.$this->name,
            'inputIdLatitude' => $idPrefix.$this->prefix.$this->name.'_latitude',
            'inputIdLongitude' => $idPrefix.$this->prefix.$this->name.'_longitude',
            'configuration' => $this->configuration,
            'value' => $this->value,
            'valueLatitude' => (isset($this->coordinatesValue[0]) ? $this->coordinatesValue[0] : ''),
            'valueLongitude' => (isset($this->coordinatesValue[1]) ? $this->coordinatesValue[1] : ''),
            'error' => $this->error,
            'disabled' => $this->disabled,
        );
    }
    
/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if (($this->configuration['required'] == true) && ($this->value == '')) {
            $this->error = $this->configuration['requiredError'];
            return false;
        }
        $this->error = null;
        return true;
    }

/**
 * Get javascript validation text for input
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return string Javascript code
 */    
    public function getJsValidation($idPrefix)
    {
        if ($this->disabled == true) {
            return '';
        }
        $code = '';
        if ($this->configuration['required'] == true) {
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';} else'.self::JS_EOL;
        }
        return $code;
    }
    
}
