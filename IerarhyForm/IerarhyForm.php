<?php

/**
 * Ierarhy form class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\IerarhyForm;

use Sergsxm\UIBundle\Exceptions\FormException;
use Sergsxm\UIBundle\Classes\FormBag;
use Sergsxm\UIBundle\Classes\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IerarhyForm
{
    private $container;
    private $configuration;
    
    
    
    
    private $formBag;
    private $isFrozen = false;
    private $mappingObject;
    private $action;
    private $encType = 'multipart/form-data';
    private $result = true;
    private $readOnly;
    private $formId = null;
    private $formIdAuto = '';
    

    
    
    
    public function __construct(ContainerInterface $container, $configuration = array(), $objects = array())
    {
        $this->container = $container;
        $this->configuration = array_merge($this->getDefaultConfiguration(), $configuration);
        $this->readOnly = false;
        
        
        
        
        $this->objects = $objects;
        
        
        
        /*
        $this->container = $container;
        $this->mappingObject = $mappingObject;
        $this->action = $action;
        $this->readOnly = false;
        $this->formBag = new FormBag($this->container->get('request_stack')->getMasterRequest()->getSession());
        $this->currentGroup = new \Sergsxm\UIBundle\Classes\FormGroup($container, $this->formBag);
        $this->rootGroup = $this->currentGroup;*/
    }

    private function getDefaultConfiguration()
    {
        return array(
            
            
            
        );
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
 * Clear form data
 */    
    public function clear()
    {
        $this->formBag->clear();
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

}
