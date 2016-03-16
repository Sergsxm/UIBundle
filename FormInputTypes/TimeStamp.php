<?php

/**
 * Timestamp form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Classes\FormInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use Sergsxm\UIBundle\Classes\FormBag;

class TimeStamp extends FormInput
{

/**
 * Constructor
 * 
 * @param Container $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param string $prefix Input prefix
 * @param object $mappingObject Object for input value mapping
 */
    public function __construct(Container $container, FormBag $formBag, $name, $configuration = array(), $prefix = '', $mappingObject = null)
    {
        parent::__construct($container, $formBag, $name, $configuration, $prefix, $mappingObject);
        if ($this->configuration['timeZone'] != null) {
            if (!$this->configuration['timeZone'] instanceof \DateTimeZone) {
                $this->configuration['timeZone'] = new \DateTimeZone($this->configuration['timeZone']);
            }
            if ($this->value instanceof \DateTime) {
                $this->value->setTimezone($this->configuration['timeZone']);
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
        return 'timestamp';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:TimeStamp.html.twig';
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
            'dateTimeFormat' => 'Y-m-d\TH:i',
            'timeZone' => null,
        );
    }

/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if (($this->configuration['required'] == true) && ($this->value == null)) {
            $this->error = $this->configuration['requiredError'];
            return false;
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
        if ($this->disabled == true) {
            return true;
        }
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $textValue = $request->get($prefix.$this->prefix.$this->name);
            if ($textValue == '') {
                $value = null; 
            } else {
                if (($this->configuration['dateTimeFormat'] == 'Y-m-d\TH:i') || ($this->configuration['dateTimeFormat'] == 'Y-m-d\TH:i:s')) {
                    $value = new \DateTime($textValue, $this->configuration['timeZone']);
                } else {
                    if ($this->configuration['timeZone'] != null) {
                        $value = \DateTime::createFromFormat($this->configuration['dateTimeFormat'], $textValue, $this->configuration['timeZone']);
                    } else {
                        $value = \DateTime::createFromFormat($this->configuration['dateTimeFormat'], $textValue);
                    }
                }
            }
            return $this->setValue($value);
        } else {
            return false;
        }
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
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
        //$code .= 'if (!/^'.$regexp.'$/i.test(form["'.$this->prefix.$this->name.'"].value)) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['regexpError']).';}'.self::JS_EOL;
        return $code;
    }
    
}
