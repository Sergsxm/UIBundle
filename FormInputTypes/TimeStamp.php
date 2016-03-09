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

class TimeStamp extends FormInput
{

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
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $textValue = $request->get($prefix.$this->prefix.$this->name);
            if ($textValue == '') {
                $value = null; 
            } else {
                if (($this->configuration['dateTimeFormat'] == 'Y-m-d\TH:i') || ($this->configuration['dateTimeFormat'] == 'Y-m-d\TH:i:s')) {
                    $value = new \DateTime($textValue);
                } else {
                    $value = \DateTime::createFromFormat($this->configuration['dateTimeFormat'], $textValue);
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
        $code = '';
        if ($this->configuration['required'] == true) {
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
        //$code .= 'if (!/^'.$regexp.'$/i.test(form["'.$this->prefix.$this->name.'"].value)) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['regexpError']).';}'.self::JS_EOL;
        return $code;
    }
    
}
