<?php

/**
 * Password form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Classes\FormInput;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Classes\FormBag;

class Password extends FormInput
{

    private $valueRepeat = null;
    private $salt = null;
    private $mappingSaltProperty;
    
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
        parent::__construct($container, $formBag, $name, $configuration, $prefix, $mappingObject);
        if (($this->mappingObject !== null) && ($this->configuration['mappingSaltProperty'] != '')) {
            $reflector = new \ReflectionObject($mappingObject);
            $this->mappingProperty = $reflector->getProperty($this->configuration['mappingSaltProperty']);
            $this->mappingProperty->setAccessible(true);
            $this->salt = $this->mappingProperty->getValue($mappingObject);
        }
        $this->valueRepeat = $this->value;
    }    
    
/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'password';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Password.html.twig';
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
            'encoder' => null,
            'repeat' => false,
            'repeatError' => 'Values​do not match',
            'repeatDescription' => '',
            'mapNullValues' => true,
            'regexp' => '/^[\S]{5,99}$/i',
            'regexpError' => 'The field is not valid',
            'randomizeSalt' => true,
            'mappingSaltProperty' => '',
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
        if (($this->configuration['repeat'] == true) && ($this->value !== $this->valueRepeat)) {
            $this->error = $this->configuration['repeatError'];
            return false;
        }
        if (!preg_match($this->configuration['regexp'].'u', $this->value)) {
            $this->error = $this->configuration['regexpError'];
            return false;
        }
        $this->error = null;
        return true;
    }

/**
 * Get value
 * 
 * @return mixed Value
 */    
    public function getValue()
    {
        if (is_object($this->configuration['encoder']) && is_subclass_of($this->configuration['encoder'], '\Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface')) {
            return $this->configuration['encoder']->encodePassword($this->value, $this->salt);
        }
        return $this->value;
    }

/**
 * Get password salt
 * 
 * @return string Salt
 */    
    public function getSalt()
    {
        return $this->salt;
    }
    
/**
 * Set value
 * 
 * @param mixed $value Value
 * @return boolean There are no errors
 */
    public function setValue($value, $valueRepeat = null)
    {
        $this->value = $value;
        $this->valueRepeat = ($valueRepeat == null ? $value : $valueRepeat);
        if ($this->configuration['randomizeSalt'] == true) {
            $randomValue = random_bytes(32);
            $this->salt = rtrim(strtr(base64_encode($randomValue), '/+', '-_'), '=');
        }
        if (($this->mappingObject !== null) && (($this->configuration['mapNullValues'] == true) || ($value != ''))) {
            $this->mappingProperty->setValue($this->mappingObject, $value);
            if ($this->configuration['mappingSaltProperty'] != '') {
                $this->mappingSaltProperty->setValue($this->mappingObject, $this->salt);
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
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $value = $request->get($prefix.$this->prefix.$this->name);
            $valueRepeat = $request->get($prefix.$this->prefix.$this->name.'_repeat');
            return $this->setValue($value, $valueRepeat);
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
        if ($this->configuration['repeat'] == true) {
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value != form["'.$this->prefix.$this->name.'_repeat"].value) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['repeatError']).';}'.self::JS_EOL;
        }
        $code .= 'if (!'.$this->configuration['regexp'].'.test(form["'.$this->prefix.$this->name.'"].value)) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['regexpError']).';}'.self::JS_EOL;
        return $code;
    }
    
}
