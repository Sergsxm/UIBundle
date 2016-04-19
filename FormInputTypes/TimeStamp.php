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

    private $regularExpression;
    private $placeholder;

/**
 * Convert datetime format to regular expression
 * 
 * @param string $format
 * @return string Regular expression
 */    
    private function getRegExpFromFormat($format)
    {
        $regulars = array(
            'd' => '[0-9]{2}',
            'D' => '(Mon|Tue|Wed|Thu|Fri|Sat|Sun)',
            'j' => '[0-9]{1,2}',
            'l' => '(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)',
            'N' => '[1-7]',
            'S' => '(st|nd|rd|th)',
            'w' => '[0-6]',
            'z' => '[0-9]{1,3}',
            'W' => '[0-9]{1,2}',
            'F' => '(January|February|March|April|May|June|July|August|September|October|November|December)',
            'm' => '[0-9]{2}',
            'M' => '(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)',
            'n' => '[0-9]{1,2}',
            't' => '(28|29|30|31)',
            'L' => '(0|1)',
            'o' => '[0-9]{4}',
            'Y' => '[0-9]{4}',
            'y' => '[0-9]{2}',
            'a' => '(am|pm)',
            'A' => '(AM|PM)',
            'B' => '[0-9]{3}',
            'g' => '[0-9]{1,2}',
            'G' => '[0-9]{1,2}',
            'h' => '[0-9]{2}',
            'H' => '[0-9]{2}',
            'i' => '[0-9]{2}',
            's' => '[0-9]{2}',
            'u' => '[0-9]{1,6}',
            'e' => '[A-z\/\-]+',
            'I' => '(0|1)',
            'O' => '(\+|\-)[0-9]{4}',
            'P' => '(\+|\-)[0-9]{2}:[0-9]{2}',
            'T' => '[A-Z]{3}',
            'Z' => '(\+|\-)?[0-9]{1,5}',
            'c' => '[0-9]{4}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(\+|\-)[0-9]{2}:[0-9]{2}',
            'r' => '(Mon|Tue|Wed|Thu|Fri|Sat|Sun),[\s]+[0-9]{2}[\s]+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[\s]+[0-9]{4}[\s]+[0-9]{1,2}:[0-9]{2}:[0-9]{2}[\s]+(\+|\-)[0-9]{4}',
            'U' => '[0-9]+',
        );
        $result = '';
        for ($i = 0; $i < strlen($format); $i++) {
            $letter = $format[$i];
            if ($letter == '\\') {
                $i++;
                $letter = $format[$i];
                if (in_array($letter, array('-', '+', '[', ']', '(', ')', '{', '}', '\\', '/', '|', '.', '?'))) {
                    $result .= '\\';
                }
                $result .= $letter;
                continue;
            }
            if (isset($regulars[$letter])) {
                $result .= $regulars[$letter];
            } else {
                if (in_array($letter, array('-', '+', '[', ']', '(', ')', '{', '}', '\\', '/', '|', '.', '?'))) {
                    $result .= '\\';
                }
                $result .= $letter;
            }
        }
        return '/^'.$result.'$/i';
    }
    
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
            if ($this->configuration['timeZone'] == 'default') {
                $this->configuration['timeZone'] = date_default_timezone_get();
            }
            if (!$this->configuration['timeZone'] instanceof \DateTimeZone) {
                $this->configuration['timeZone'] = new \DateTimeZone($this->configuration['timeZone']);
            }
            if ($this->value instanceof \DateTime) {
                $this->value->setTimezone($this->configuration['timeZone']);
            }
        }
        $this->placeholder = date($this->configuration['dateTimeFormat'], mktime(1, 0, 0, 1, 1, 1999));
        $this->regularExpression = $this->getRegExpFromFormat($this->configuration['dateTimeFormat']);
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
            'dateTimeFormat' => 'Y-m-d\TH:i:s',
            'formatError' => 'Bad datetime format',
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
            'placeholder' => $this->placeholder,
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
        $code = '';
        if ($this->configuration['required'] == true) {
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
        $code .= 'if ((form["'.$this->prefix.$this->name.'"].value != "") && !'.$this->regularExpression.'.test(form["'.$this->prefix.$this->name.'"].value)) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['formatError']).';}'.self::JS_EOL;
        return $code;
    }
    
}
