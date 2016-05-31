<?php

/**
 * Abstract class for form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Form\FormException;
use Sergsxm\UIBundle\Form\FormGroup;
use Sergsxm\UIBundle\Form\FormBag;

class FormGroup
{
    const JS_EOL = "\r\n";

    private $container;
    private $formBag;
    private $name;
    private $prefix;
    private $description;
    private $parent;
    private $fields;
    private $groups;

/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param type $name Group name
 * @param type $description Group description
 * @param FormGroup $parent Group parent
 */    
    public function __construct(ContainerInterface $container, FormBag $formBag, $name = '', $description = '', FormGroup $parent = null)
    {
        if (($parent !== null) && ($name == '')) {
            throw new FormException(__CLASS__.': group name cannot by null');
        }
        
        $this->container = $container;
        $this->formBag = $formBag;
        $this->name = $name;
        $this->prefix = ($name != '' ? $name.'_' : '');
        if ($parent !== null) {
            $this->prefix = $parent->getPrefix().$this->prefix;
        }
        $this->description = $description;
        $this->parent = $parent;
        $this->fields = array();
        $this->groups = array();
    }

/**
 * Get default template for group
 * 
 * @return string Template
 */
    public function getDefaultTemplate() 
    {
        return 'SergsxmUIBundle:Form:FormGroup.html.twig';
    }

/**
 * Get input prefix (will added to input`s name property)
 * 
 * @return string Input prefix
 */    
    public function getPrefix()
    {
        return $this->prefix;
    }

/**
 * Add field to the group
 * 
 * @param string $type Input class
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param object $mappingObject Object for input value mapping
 */    
    public function addField($type, $name, $configuration = array(), $mappingObject = null)
    {
        if (isset($this->fields[$name]) || isset($this->groups[$name])) {
            throw new FormException(__CLASS__.': form input or group named "'.$name.'" is already exist');
        }
        
        if (!class_exists($type) || !is_subclass_of($type, '\Sergsxm\UIBundle\Form\FormInput')) {
            throw new FormException(__CLASS__.': form input type "'.$type.'" is not exist');
        }
        
        $this->fields[$name] = new $type($this->container, $this->formBag, $name, $configuration, $this->prefix, $mappingObject);
    }

/**
 * Add subgroup to the group
 * 
 * @param string $name Subgroup name
 * @param string $description Subgroup description
 * @param string $condition The condition under which the subgroup will be processed
 * @return FormGroup Subgroup object
 */    
    public function addGroup($name, $description, $condition)
    {
        if (!preg_match('/^[A-Za-z0-9]+$/ui', $name)) {
            throw new FormException(__CLASS__.': group name must contain only letters and numbers');
        }
        if (isset($this->fields[$name]) || isset($this->groups[$name])) {
            throw new FormException(__CLASS__.': form input or group named "'.$name.'" is already exist');
        }
        
        $this->checkConditionFormat($condition);
        
        $group = new FormGroup($this->container, $this->formBag, $name, $description, $this);
        $this->groups[$name] = array('condition' => $condition, 'group' => $group);
        
        return $group;
    }

/**
 * Get parent group
 * 
 * @return FormGroup|null Parent group object
 */    
    public function getParentGroup()
    {
        if ($this->parent === null) {
            throw new FormException(__CLASS__.': it is impossible to return to the parent group');
        }
        return $this->parent;
    }

/**
 * Bind form request
 * 
 * @param Request $request Symfony2 request object
 * @param string $prefix Input prefix
 * @return boolean Values is accepted and there are no errors
 */    
    public function bindRequest(Request $request = null, $prefix = '')
    {
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        if ($request->getMethod() == 'POST') {
            $result = true;
            foreach ($this->fields as $field) {
                if ($field->bindRequest($request, $prefix) == false) {
                    $result = false;
                }
            }
            foreach ($this->groups as $group) {
                if ($this->checkCondition($group['condition']) == true) {
                    if ($group['group']->bindRequest($request, $prefix) == false) {
                        $result = false;
                    }
                }
            }
            return $result;
        } else {
            return false;
        }
    }
    
/**
 * Get view array for group template
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return array View array
 */
    public function getView($idPrefix)
    {
        $view = array(
            'name' => $this->name,
            'description' => $this->description,
            'defaultTemplate' => $this->getDefaultTemplate(),
            'groupId' => $idPrefix.$this->prefix,
            'fields' => array(),
            'groups' => array(),
        );
        foreach ($this->fields as $filedname => $field) {
            $view['fields'][$filedname] = $field->getInputView($idPrefix);
        }
        foreach ($this->groups as $groupname => $group) {
            $view['groups'][$groupname] = $group['group']->getView($idPrefix);
        }
        
        return $view;
    }    

/**
 * Get javascript validation code
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return string Javascript code
 */    
    public function getJsValidation($idPrefix)
    {
        $code = '';
        foreach ($this->fields as $field) {
            $code .= $field->getJsValidation($idPrefix);
        }
        foreach ($this->groups as $group) {
            if (preg_match('/^(\S+)\s+(==|<|>|!=|<=|>=)([\s\S]+)$/ui', $group['condition'], $matches)) {
                $value = trim($matches[3]);
                if (substr($value, 0, 1) == '\'') {
                    $value = "'".substr($value, 1, strrpos($value, '\'') - 1)."'";
                } else {
                    $value = floatval($value);
                }
                $code .= 'if (form["'.$this->prefix.$matches[1].'"].value '.$matches[2].' '.$value.') {'.self::JS_EOL.$group['group']->getJsValidation($idPrefix).'}'.self::JS_EOL;
            } else {
                $code .= $group['group']->getJsValidation($idPrefix);
            }
        }
        return $code;
    }

/**
 * Get javascript visibility code
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return string Javascript code
 */    
    public function getJsVisibility($idPrefix)
    {
        $code = '';
        foreach ($this->groups as $group) {
            if (preg_match('/^(\S+)\s+(==|<|>|!=|<=|>=)([\s\S]+)$/ui', $group['condition'], $matches)) {
                $value = trim($matches[3]);
                if (substr($value, 0, 1) == '\'') {
                    $value = "'".substr($value, 1, strrpos($value, '\'') - 1)."'";
                } elseif (strtolower($value) == 'true') {
                    $value = 'true';
                } elseif (strtolower($value) == 'false') {
                    $value = 'false';
                } elseif (strtolower($value) == 'null') {
                    $value = 'null';
                } else {
                    $value = floatval($value);
                }
                $code .= 'if (form["'.$this->prefix.$matches[1].'"].value '.$matches[2].' '.$value.') {document.getElementById("'.$idPrefix.$group['group']->getPrefix().'").style.display="block";} else {document.getElementById("'.$idPrefix.$group['group']->getPrefix().'").style.display="none";}'.self::JS_EOL;
            }
            $code .= $group['group']->getJsVisibility($idPrefix);
        }
        return $code;
    }

/**
 * Check condition
 * 
 * @param string $condition Condition
 * @return boolean Result
 */    
    private function checkCondition($condition) 
    {
        if (preg_match('/^(\S+)\s+(==|<|>|!=|<=|>=)([\s\S]+)$/ui', $condition, $matches)) {
            $value = trim($matches[3]);
            if (substr($value, 0, 1) == '\'') {
                $value = substr($value, 1, strrpos($value, '\'') - 1);
            } elseif (strtolower($value) == 'true') {
                $value = true;
            } elseif (strtolower($value) == 'false') {
                $value = false;
            } elseif (strtolower($value) == 'null') {
                $value = null;
            } else {
                $value = floatval(trim($value));
            }
            if ($matches[2] == '==') {
                return ($this->fields[$matches[1]]->getValue() == $value);
            } elseif ($matches[2] == '<') {
                return ($this->fields[$matches[1]]->getValue() < $value);
            } elseif ($matches[2] == '>') {
                return ($this->fields[$matches[1]]->getValue() > $value);
            } elseif ($matches[2] == '!=') {
                return ($this->fields[$matches[1]]->getValue() != $value);
            } elseif ($matches[2] == '<=') {
                return ($this->fields[$matches[1]]->getValue() <= $value);
            } elseif ($matches[2] == '>=') {
                return ($this->fields[$matches[1]]->getValue() >= $value);
            }
        }
        return true;
    }

/**
 * Check condition format
 * 
 * @param string $condition Condition
 */    
    private function checkConditionFormat($condition) 
    {
        if (preg_match('/^(\S+)\s+(==|<|>|!=|<=|>=)([\s\S]+)$/ui', $condition, $matches)) {
            if (!isset($this->fields[$matches[1]])) {
                throw new FormException(__CLASS__.': bad field name "'.$matches[1].'" in group condition');
            }
        }
    }

/**
 * Find input by name
 * 
 * @param string $name Input name with group prefix
 * @return \Sergsxm\UIBundle\Form\FormInput|null Input object
 */    
    public function findInputByName($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        $group = substr($name, 0, strpos($name, '_'));
        if (($group != '') && isset($this->groups[$group])) {
            return $this->groups[$group]['group']->findInputByName(substr($name, strpos($name, '_') + 1));
        }
        return null;
    }

/**
 * Get group value
 * 
 * @return array Group value
 */    
    public function getValue()
    {
        $result = array();
        foreach ($this->fields as $filedname => $field) {
            $result[$filedname] = $field->getValue();
        }
        foreach ($this->groups as $groupname => $group) {
            $result[$groupname] = $group['group']->getValue();
        }
        return $result;
    }

/**
 * Set group value
 * 
 * @param array $value Group value
 */    
    public function setValue($value)
    {
        foreach ($value as $valueKey=>$valueVal) {
            if (isset($this->fields[$valueKey])) {
                $this->fields[$valueKey]->setValue($valueVal);
            } elseif (isset($this->groups[$valueKey])) {
                $this->groups[$valueKey]['group']->setValue($valueVal);
            }
        }
    }
    
}
