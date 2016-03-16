<?php

/**
 * Number form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Classes\FormInput;

class Number extends FormInput
{

/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'number';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Number.html.twig';
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
            'decimalPoint' => '.',
            'thousandSeparator' => '',
            'decimals' => null,
            'minValue' => null,
            'maxValue' => null,
            'valueError' => 'The number is beyond the set limits',
            'notNumberError' => 'This is not a number',
        );
    }

/**
 * Set value
 * 
 * @param mixed $value Value
 * @return boolean There are no errors
 */
    public function setValue($value)
    {
        if (($value === '') || ($value === null)) {
            $this->value = null;
        } else {
            $this->value = floatval(str_replace(array($this->configuration['thousandSeparator'], $this->configuration['decimalPoint']), array('', '.'), $value));
            if ($this->configuration['decimals'] !== null) {
                $this->value = round($this->value, $this->configuration['decimals']);
            }
        }
        if ($this->mappingObject !== null) {
            $this->mappingProperty->setValue($this->mappingObject, $this->value);
        }
        return $this->validateValue();
    }
    
/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if (($this->configuration['required'] == true) && ($this->value === null)) {
            $this->error = $this->configuration['requiredError'];
            return false;
        }
        if (($this->configuration['minValue'] !== null) && ($this->value < $this->configuration['minValue'])) {
            $this->error = $this->configuration['valueError'];
            return false;
        }
        if (($this->configuration['maxValue'] !== null) && ($this->value > $this->configuration['maxValue'])) {
            $this->error = $this->configuration['valueError'];
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
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
        $code .= 'if (form["'.$this->prefix.$this->name.'"].value != "") {'.self::JS_EOL;
        $code .= 'var v = parseFloat(form["'.$this->prefix.$this->name.'"].value.replace('.json_encode($this->configuration['thousandSeparator']).', \'\').replace('.json_encode($this->configuration['decimalPoint']).', \'.\'));'.self::JS_EOL;
        $code .= 'if (isNaN(v)) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['notNumberError']).';}'.self::JS_EOL;
        if ($this->configuration['minValue'] !== null) {
            $code .= 'else if (v < '.$this->configuration['minValue'].') {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['valueError']).';}'.self::JS_EOL;
        }
        if ($this->configuration['maxValue'] !== null) {
            $code .= 'else if (v > '.$this->configuration['maxValue'].') {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['valueError']).';}'.self::JS_EOL;
        }
        $code .= '}'.self::JS_EOL;
        return $code;
    }

    private function numberFormat($value)
    {
        if ($this->configuration['decimals'] !== null) {
            return number_format($value, $this->configuration['decimals'], $this->configuration['decimalPoint'], $this->configuration['thousandSeparator']);
        }
        $wasNeg = $value < 0;
        $value = abs($value);
        $tmp = explode('.', $value);
        $out = number_format($tmp[0], 0, $this->configuration['decimalPoint'], $this->configuration['thousandSeparator']);
        if (isset($tmp[1])) {
            $out .= $this->configuration['decimalPoint'].$tmp[1];
        }
        if ($wasNeg) {
            $out = '-'.$out;
        }
        return $out; 
    }
    
/**
 * Get view array for input template
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return array View array
 */    
    public function getInputView($idPrefix)
    {
        if ($this->value === null) {
            $value = '';
        } else {
            $value = $this->numberFormat($this->value);
        }
        return array(
            'type' => $this->getType(),
            'defaultTemplate' => $this->getDefaultTemplate(),
            'name' => $this->name,
            'inputName' => $this->prefix.$this->name,
            'inputId' => $idPrefix.$this->prefix.$this->name,
            'configuration' => $this->configuration,
            'value' => $value,
            'error' => $this->error,
            'disabled' => $this->disabled,
        );
    }
    
}
