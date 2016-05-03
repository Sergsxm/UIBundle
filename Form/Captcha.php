<?php

/**
 * Abstract class for captcha type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sergsxm\UIBundle\Form\FormException;
use Sergsxm\UIBundle\Form\FormBag;

abstract class Captcha
{
    const JS_EOL = "\r\n";

    protected $container;
    protected $formBag;
    protected $configuration;
    protected $error = null;
    protected $isValueGenerated = false;

/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param array $configuration Captcha configuration
 */
    public function __construct(ContainerInterface $container, FormBag $formBag, $configuration = array())
    {
        $this->container = $container;
        $this->formBag = $formBag;
        
        $this->setDefaults();
        $this->configuration = array_merge($this->configuration, $configuration);
    }
    
/**
 * Get type of captcha
 * 
 * @return string Type
 */
    abstract public function getType();

/**
 * Get default template for captcha
 * 
 * @return string Default template
 */
    abstract public function getDefaultTemplate();
    
/**
 * Set configuration to default values
 */
    abstract public function setDefaults();

/**
 * Validate session value with $value
 * 
 * @param string $value Value for comparation
 * @return boolean There are no errors
 */
    abstract public function validateValue($value);

/**
 * Get unique value for captcha
 * 
 * @return string Unique value
 */    
    abstract public function getUniqueValue();
    
/**
 * Generate new value and store in session
 * 
 * @return string New value
 */    
    public function generateValue()
    {
        if ($this->isValueGenerated == true) {
            return $this->getValue();
        }
        
        $value = $this->getUniqueValue();
        $this->formBag->set('captcha', $value);
        
        return $value;
    }

/**
 * Get value from session
 * 
 * @return string Captcha value
 */    
    public function getValue()
    {
        return $this->formBag->get('captcha');
    }

/**
 * Get HTML tags with imaged captcha value
 * 
 * @return string HTML tags
 */    
    abstract public function getValueTag();
    
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
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $value = $request->get($prefix.'captcha');
            return $this->validateValue($value);
        } else {
            return false;
        }
    }

/**
 * Get view array for template
 * 
 * @return array View array
 */    
    public function getCaptchaView($idPrefix)
    {
        return array(
            'type' => $this->getType(),
            'defaultTemplate' => $this->getDefaultTemplate(),
            'inputName' => 'captcha',
            'inputId' => $idPrefix.'captcha',
            'valueTag' => $this->getValueTag(),
            'configuration' => $this->configuration,
            'error' => $this->error,
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
