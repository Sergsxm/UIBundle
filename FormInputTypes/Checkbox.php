<?php

/**
 * Checkbox form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Classes\FormInput;

class Checkbox extends FormInput
{

/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'checkbox';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Checkbox.html.twig';
    }
    
/**
 * Set configuration to default values
 */
    public function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->name,
            'required' => false,
            'requiredError' => 'The field must be checked',
            'uncheckedValue' => false,
            'checkedValue' => true,
        );
    }

/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if (($this->configuration['required'] == true) && ($this->value !== $this->configuration['checkedValue'])) {
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
            $checked = $request->get($prefix.$this->prefix.$this->name);
            return $this->setValue(($checked ? $this->configuration['checkedValue'] : $this->configuration['uncheckedValue']));
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
            'value' => 'on',
            'checked' => ($this->configuration['checkedValue'] === $this->value ? true : false),
            'error' => $this->error,
            'disabled' => $this->disabled,
        );
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
        if ($this->configuration['required'] == true) {
            return 'if (!form["'.$this->prefix.$this->name.'"].checked) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
    }
    
}
