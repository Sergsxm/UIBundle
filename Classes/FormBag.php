<?php

/**
 * Form`s parameters bag 
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

use Sergsxm\UIBundle\Exceptions\FormException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FormBag
{
    
    protected $formId;
    protected $session;
    protected $parameters;

/**
 * Class constructor
 * 
 * @param SessionInterface $session Symfony2 session
 */    
    public function __construct(SessionInterface $session) 
    {
        $this->session = $session;
        $this->formId = null;
        $this->parameters = array();
    }

/**
 * Set form identifier (access to stored data is carried out by this ID)
 * 
 * @param string $formId Form ID
 */    
    public function setFormId($formId)
    {
        $this->formId = $formId;
        $this->restore();
    }

/**
 * Get form identifier
 * 
 * @return string Form ID
 */    
    public function getFormId()
    {
        return $this->formId;
    }
    
/**
 * Store parameters to session
 */    
    private function store()
    {
        $this->session->set('sergsxm_form_'.$this->formId, $this->parameters);
    }

/**
 * Restore parameters from session
 */    
    private function restore()
    {
        if ($this->session->has('sergsxm_form_'.$this->formId)) {
            $this->parameters = $this->session->get('sergsxm_form_'.$this->formId);
        } else {
            $this->parameters = array();
        }
    }

/**
 * Сheck the parameter with the specified name 
 * 
 * @param string $name Parameter name
 * @return boolean Is parameter exists
 */    
    public function has($name)
    {
        if ($this->formId == null) {
            throw new FormException(__CLASS__.' (dev): formId not defined');
        }
        return isset($this->parameters[$name]);
    }

/**
 * Get parameter value
 * 
 * @param string $name Parameter name
 * @return mixed|null Parameter value
 */    
    public function get($name)
    {
        if ($this->formId == null) {
            throw new FormException(__CLASS__.' (dev): formId not defined');
        }
        return (isset($this->parameters[$name]) ? $this->parameters[$name] : null);
    }

/**
 * Set parameter value
 * 
 * @param string $name Parameter name
 * @param mixed $value Parameter value
 */    
    public function set($name, $value)
    {
        if ($this->formId == null) {
            throw new FormException(__CLASS__.' (dev): formId not defined');
        }
        $this->parameters[$name] = $value;
        $this->store();
    }

/**
 * Remove parameter
 * 
 * @param string $name Parameter name
 */    
    public function remove($name)
    {
        if ($this->formId == null) {
            throw new FormException(__CLASS__.' (dev): formId not defined');
        }
        if (isset($this->parameters[$name])) {
            unset($this->parameters[$name]);
            $this->store();
        }
    }

/**
 * Clear form bag
 */    
    public function clear()
    {
        if ($this->session->has('sergsxm_form_'.$this->formId)) {
            $this->session->remove('sergsxm_form_'.$this->formId);
            $this->parameters = array();
        }
    }
            
}
