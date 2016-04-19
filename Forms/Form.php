<?php

/**
 * Simple form class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\Forms;

use Sergsxm\UIBundle\Exceptions\FormException;
use Sergsxm\UIBundle\Classes\FormBag;
use Sergsxm\UIBundle\Classes\FormInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Form implements FormInterface
{
    const MO_PARENT = 'parent';
    
    private $container;
    private $formBag;
    private $isFrozen = false;
    private $mappingObject;
    private $currentGroup;
    private $rootGroup;
    private $action;
    private $encType = 'multipart/form-data';
    private $result = true;
    private $readOnly;
    private $captcha = null;
    private $formId = null;
    private $formIdAuto = '';

/**
 * Form constructor
 * 
 * @param Container $container Symfony2 container
 * @param object $mappingObject Object for input values mapping
 * @param string $action Action URL
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function __construct(Container $container, $mappingObject = null, $action = '')
    {
        $this->container = $container;
        $this->mappingObject = $mappingObject;
        $this->action = $action;
        $this->readOnly = false;
        $this->formBag = new FormBag($this->container->get('request_stack')->getMasterRequest()->getSession());
        $this->currentGroup = new \Sergsxm\UIBundle\Classes\FormGroup($container, $this->formBag);
        $this->rootGroup = $this->currentGroup;
    }

/**
 * Add field to form
 * 
 * @param string $type Field type (name of class or form_input_type name)
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param object $mappingObject Object for input value mapping
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 * @throws FormException
 */    
    public function addField($type, $name, $configuration = array(), $mappingObject = self::MO_PARENT)
    {
        if ($this->isFrozen == true) {
            throw new FormException(__CLASS__.': you can`t add fields after bindRequest() or getView() methods are called');
        }
        if (!class_exists($type) || !is_subclass_of($type, '\Embedded\FormBundle\Classes\FormInput')) {
            $types = $this->container->get('sergsxm.ui')->getFormInputTypes();
            if (isset($types[$type])) {
                $type = $types[$type]; 
            } else {
                throw new FormException(__CLASS__.': form input type "'.$type.'" is not exist');
            }
        }
        
        if ($mappingObject === self::MO_PARENT) {
            $mappingObject = $this->mappingObject;
        }
        
        $this->currentGroup->addField($type, $name, $configuration, $mappingObject);
        $this->formIdAuto .= ' '.$name;
        
        return $this;
    }

/**
 * Bind form request
 * 
 * @param Request $request Symfony2 request object
 * @return boolean Values are accepted and there are no errors
 */
    public function bindRequest(Request $request = null)
    {
        $this->isFrozen = true;
        $this->formBag->setFormId($this->getFormId());
        if ($this->readOnly == true) {
            $this->result = false;
            return false;
        }
        if ($request == null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        if (($request->getMethod() == 'POST') && ($request->get('form_id') == $this->getFormId())) {
            $this->result = $this->rootGroup->bindRequest($request);
            if ($this->captcha != null) {
                if ($this->captcha->bindRequest($request) == false) {
                    $this->result = false;
                }
            }
            if ($request->get('form_token') !== $this->getCsrfToken()) {
                $this->result = false;
            }
        } else {
            $this->result = false;
        }
        return $this->result;
    }

/**
 * Get bind result
 * 
 * @return boolean Values are accepted and there are no errors
 */    
    public function getResult()
    {
        return $this->result;
    }

/**
 * Get view parameters for template
 * 
 * @return array View parameters
 */    
    public function getView()
    {
        $this->isFrozen = true;
        $this->formBag->setFormId($this->getFormId());
        $formId = $this->getFormId();
        $view = array_merge(array(
            'action' => $this->action,
            'encType' => $this->encType,
            'rest' => '<input type="hidden" name="form_token" value="'.$this->generateCsrfToken().'" /><input type="hidden" name="form_id" value="'.$formId.'" />',
            'result' => $this->result,
            'readOnly' => $this->readOnly,
            'formId' => $formId,
            'root' => true,
            'jsValidation' => $this->rootGroup->getJsValidation($formId.'_'),
            'jsVisibility' => $this->rootGroup->getJsVisibility($formId.'_'),
        ), $this->rootGroup->getView($formId.'_'));
        
        if ($this->captcha != null) {
            $this->captcha->generateValue();
            $view['captcha'] = $this->captcha->getCaptchaView($formId.'_');
            $view['jsValidation'] .= $this->captcha->getJsValidation($formId.'_');
        }
        
        return $view;
    }

/**
 * Get view of form as content
 * 
 * @param string $template Template
 * @param array $parameters Additional parameters
 * @return string View content
 */    
    public function renderView($template = 'SergsxmUIBundle:Forms:Form.html.twig', $parameters = array())
    {
        return $this->container->get('templating')->render($template, array_merge($parameters, $this->getView()));
    }

/**
 * Get view of form as response
 * 
 * @param string $template Template
 * @param array $parameters Additional parameters
 * @return Response View response
 */    
    public function render($template = 'SergsxmUIBundle:Forms:Form.html.twig', $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($template, array_merge($parameters, $this->getView()), $response);
    }
    
/**
 * Get CSFR token for validation
 * 
 * @return string Token
 */    
    private function getCsrfToken()
    {
        return $this->container->get('request_stack')->getMasterRequest()->getSession()->get('sergsxm_form_csrf');
    }

/**
 * Generate CSFR token
 * 
 * @return string Token
 */    
    private function generateCsrfToken()
    {
        if ($this->container->get('request_stack')->getMasterRequest()->getSession()->has('sergsxm_form_csrf')) {
            return $this->getCsrfToken();
        }
        $randomValue = random_bytes(32);
        $token = rtrim(strtr(base64_encode($randomValue), '/+', '-_'), '=');
        $this->container->get('request_stack')->getMasterRequest()->getSession()->set('sergsxm_form_csrf', $token);
        return $token;
    }

/**
 * Set read only form flag
 * 
 * @param boolean $readOnly Read only flag
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }
    
/**
 * Enable captcha
 * 
 * @param string $type Type of captcha (alias or class)
 * @param array $configuration Captcha configuration
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 * @throws FormException
 */
    public function enableCaptcha($type, $configuration = array())
    {
        if ($this->isFrozen == true) {
            throw new FormException(__CLASS__.': you can`t enable captcha after bindRequest() or getView() methods are called');
        }
        
        if (!class_exists($type) || !is_subclass_of($type, '\Sergsxm\UIBundle\Classes\Captcha')) {
            $types = $this->container->get('sergsxm.ui')->getCaptchaTypes();
            if (isset($types[$type])) {
                $type = $types[$type]; 
            } else {
                throw new FormException(__CLASS__.': captcha type "'.$type.'" is not exist');
            }
        }
        $this->captcha = new $type($this->container, $this->formBag, $configuration);
        
        return $this;
    }

/**
 * Disable captcha
 * 
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function disableCaptcha()
    {
        if ($this->isFrozen == true) {
            throw new FormException(__CLASS__.': you can`t disable captcha after bindRequest() or getView() methods are called');
        }
        
        $this->captcha = null;
        
        return $this;
    }

    private function hex2char($hc)
    {
        return chr(hexdec($hc));
    }
    
/**
 * Return unique form id
 * 
 * @return string Form id
 */    
    public function getFormId()
    {
        if ($this->formId !== null) {
            return $this->formId;
        }
        $hash = '';
        if ($this->mappingObject != null) {
            $hash .= get_class($this->mappingObject);
            if (property_exists($this->mappingObject, 'id')) {
                $reflector = new \ReflectionObject($this->mappingObject);
                $property = $reflector->getProperty('id');
                $property->setAccessible(true);
                $val = $property->getValue($this->mappingObject);
                if (in_array(gettype($val), array('boolean', 'integer', 'double', 'string'))) {
                    $hash .= '_'.$val;
                } else {
                    $hash .= '_'.gettype($val);
                }
                unset($property);
                unset($reflector);
            }
        }
        $hash .= $this->formIdAuto;
        return 'f'.crc32($hash);
    }

/**
 * Set unique form id
 * 
 * @param string $formId Form id
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function setFormId($formId)
    {
        if ($this->isFrozen == true) {
            throw new FormException(__CLASS__.': you can`t set formId after bindRequest() or getView() methods are called');
        }
        
        $this->formId = $formId;
        
        return $this;
    }

/**
 * Open group
 * 
 * @param string $name Subgroup name
 * @param string $description Subgroup description
 * @param string $condition The condition under which the subgroup will be processed
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function openGroup($name, $description = '', $condition = '')
    {
        if ($this->isFrozen == true) {
            throw new FormException(__CLASS__.': you can`t open group after bindRequest() or getView() methods are called');
        }
        
        $this->currentGroup = $this->currentGroup->addGroup($name, $description, $condition);
        
        return $this;
    }

/**
 * Close current group and return to the parent group
 * 
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function closeGroup()
    {
        if ($this->isFrozen == true) {
            throw new FormException(__CLASS__.': you can`t close group after bindRequest() or getView() methods are called');
        }
        
        $this->currentGroup = $this->currentGroup->getParentGroup();
        
        return $this;
    }
    
/**
 * Find input by name
 * 
 * @param string $name Input name with group prefix
 * @return \Sergsxm\UIBundle\Classes\FormInput|null Input object
 */    
    public function findInputByName($name)
    {
        return $this->rootGroup->findInputByName($name);
    }
    
/**
 * Get form value
 * 
 * @return array Form value
 */    
    public function getValue()
    {
        return $this->rootGroup->getValue();
    }
    
/**
 * Set form value
 * 
 * @param array $value Form value
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function setValue($value)
    {
        $this->rootGroup->setValue($value);
        return $this;
    }
    
/**
 * Load form from class annotations
 * 
 * @param string $tag Form tag
 * @param object $mappingObject Mapping object
 * @return \Sergsxm\UIBundle\Forms\Form Form object
 */    
    public function fromAnnotations($tag = null, $mappingObject = self::MO_PARENT)
    {
        if ($mappingObject == self::MO_PARENT) {
            $mappingObject = $this->mappingObject;
        }
        $reader = $this->container->get('annotation_reader');
        $translator = $this->container->get('translator');
        if (empty($reader)) {
            throw new FormException(__CLASS__.': annotation reader service not found');
        }
        if (!is_object($mappingObject)) {
            throw new FormException(__CLASS__.': you must specify mapping object for import from annotations');
        }
        $object = new \ReflectionObject($mappingObject);
        foreach ($object->getProperties() as $property) {
            if ($tag != null) {
                $tags = $reader->getPropertyAnnotation($property, '\Sergsxm\UIBundle\Annotations\Tags');
                if (empty($tags) || !is_array($tags->forms) || !in_array($tag, $tags->forms)) {
                    continue;
                }
            }
            $input = $reader->getPropertyAnnotation($property, '\Sergsxm\UIBundle\Annotations\Input');
            if (!empty($input)) {
                if (is_array($input->translate) && is_array($input->configuration)) {
                    foreach ($input->translate as $transKey) {
                        if (isset($input->configuration[$transKey])) {
                            $input->configuration[$transKey] = $translator->trans($input->configuration[$transKey], array(), $input->translateDomain);
                        }
                    }
                }
                $this->addField($input->type, $property->getName(), $input->configuration, $mappingObject);
            }
        }
        return $this;
    }
    
}
