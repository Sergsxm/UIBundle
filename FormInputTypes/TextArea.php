<?php

/**
 * Textarea form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Classes\FormInput;

class TextArea extends FormInput
{

/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'textarea';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:TextArea.html.twig';
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
            'regexp' => '/^[\s\S]*$/i',
            'regexpError' => 'Field is not valid',
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
        if (($this->value != '') && !preg_match($this->configuration['regexp'].'u', $this->value)) {
            $this->error = $this->configuration['regexpError'];
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
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';} else'.self::JS_EOL;
        }
        $code .= 'if ((form["'.$this->prefix.$this->name.'"].value != "") && !'.$this->configuration['regexp'].'.test(form["'.$this->prefix.$this->name.'"].value)) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['regexpError']).';}'.self::JS_EOL;
        return $code;
    }
    
}
