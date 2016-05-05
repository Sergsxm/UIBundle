<?php

/**
 * Category form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Form\FormInput;
use Sergsxm\UIBundle\Form\FormException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Form\FormBag;

class Category extends FormInput
{

/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param string $prefix Input prefix
 * @param class $mappingObject Object for input value mapping
 */
    public function __construct(ContainerInterface $container, FormBag $formBag, $name, $configuration = array(), $prefix = '', $mappingObject = null)
    {
        $this->container = $container;
        $this->formBag = $formBag;
        
        if (!$name) {
            throw new FormException(__CLASS__.': name cannot be null');
        }
        $this->name = $name;
        
        $this->setDefaults();
        $this->configuration = array_merge($this->configuration, $configuration);
        
        $this->prefix = $prefix;

        if ($this->configuration['loadDoctrineRepository'] !== null) {
            $this->configuration['categories'] = $this->container->get('doctrine')->getRepository($this->configuration['loadDoctrineRepository'])->findAll();
        }
        
        if ((!isset($this->configuration['mapping'])) || ($this->configuration['mapping'] == true)) {
            $this->mappingObject = $mappingObject;
        }
        if ($this->mappingObject !== null) {
            $reflector = new \ReflectionObject($mappingObject);
            $this->mappingProperty = $reflector->getProperty($name);
            $this->mappingProperty->setAccessible(true);
            $value = $this->mappingProperty->getValue($mappingObject);
            if ($this->configuration['multiply'] == true) {
                if (($this->configuration['mapIdToValue'] == false) && is_array($value)) {
                    $this->value = $value;
                } elseif (($this->configuration['mapIdToValue'] == false) && ($value instanceof \Doctrine\Common\Collections\ArrayCollection)) {
                    $this->value = array();
                    foreach ($value->toArray() as $item) {
                        if ($item instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                            $this->value[] = $item->getId();
                        }
                    }
                } else {
                    $this->value = array();
                }
            } else {
                $this->value = $this->mappingProperty->getValue($mappingObject);
                if (($this->configuration['mapIdToValue'] == false) && ($value instanceof \Sergsxm\UIBundle\Classes\TreeInterface)) {
                    $this->value = $value->getId();
                } elseif ($this->configuration['mapIdToValue'] == true) {
                    $this->value = $value;
                } else {
                    $this->value = null;
                }
            }
        }
        if (isset($configuration['disabled'])) {
            $this->disabled = $configuration['disabled'];
        }
    }
    
/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'category';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Category.html.twig';
    }
    
/**
 * Set configuration to default values
 */
    public function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->name,
            'categories' => array(),
            'categoriesError' => 'The field contain bad value',
            'required' => false,
            'requiredError' => 'The field can not be empty',
            'multiply' => false,
            'expanded' => false,
            'mapIdToValue' => false,
            'loadDoctrineRepository' => null,
        );
    }

/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if ($this->configuration['multiply'] == true) {
            if (($this->configuration['required'] == true) && (count($this->value) == 0)) {
                $this->error = $this->configuration['requiredError'];
                return false;
            }
            foreach ($this->value as $value) {
                $valueFounded = false;
                foreach ($this->configuration['categories'] as $category) {
                    if ($category->getId() == $value) {
                        $valueFounded = true;
                        break;
                    }
                }
                if (($value === null) || ($value === '') || ($valueFounded == false)) {
                    $this->error = $this->configuration['choicesError'];
                    return false;
                }
            }
        } else {
            $valueFounded = false;
            foreach ($this->configuration['categories'] as $category) {
                if ($category->getId() == $this->value) {
                    $valueFounded = true;
                    break;
                }
            }
            if (($this->value === null) || ($this->value === '') || ($valueFounded == false)) {
                $this->error = $this->configuration['choicesError'];
                return false;
            }
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
            $value = $request->get($prefix.$this->prefix.$this->name);
            if (($this->configuration['multiply'] == true) && (!is_array($value))) {
                $value = array();
            }
            return $this->setValue($value);
        } else {
            return false;
        }
    }

/**
 * Set value
 * 
 * @param mixed $value Value
 * @return boolean There are no errors
 */
    public function setValue($value)
    {
        $this->value = $value;
        if ($this->mappingObject !== null) {
            if ($this->configuration['multiply'] == false) {
                if ($this->configuration['mapIdToValue'] == false) {
                    $realValue = null;
                    foreach ($this->configuration['categories'] as $category) {
                        if ($this->value == $category->getId()) {
                            $realValue = $category;
                            break;
                        }
                    }
                } else {
                    $realValue = $this->value;
                }
            } else {
                if ($this->configuration['mapIdToValue'] == false) {
                    $realValue = $this->mappingProperty->getValue($this->mappingObject);
                    if (!$realValue instanceof \Doctrine\Common\Collections\ArrayCollection) {
                        $realValue = new \Doctrine\Common\Collections\ArrayCollection();
                    }
                    $realValue->clear();
                    foreach ($this->configuration['categories'] as $category) {
                        if (in_array($category->getId(), $this->value)) {
                            $realValue->add($category);
                        }
                    }
                } else {
                    $realValue = $this->value;
                }
            }
            $this->mappingProperty->setValue($this->mappingObject, $realValue);
        }
        return $this->validateValue();
    }

/**
 * Sort tree for view and convart to nesting based array
 * 
 * @param array $objects Objects
 * @param array $items Output array
 * @param object $parent Parent to search childs
 * @param int $nesting Current nesting
 */    
    private function sortTreeCategory($objects, &$items, $parent = null, $nesting = 0)
    {
        foreach ($objects as $object) {
            $currentParent = $object->getParent();
            if ($currentParent instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                $currentParent = $currentParent->getId();
            }
            if ($currentParent == $parent) {
                $items[] = array(
                    'id' => $object->getId(),
                    'title' => $object->getTitle(),
                    'nesting' => $nesting,
                );
                $this->sortTreeCategory($objects, $items, $object->getId(), $nesting + 1);
            }
        }
    }

/**
 * Sort tree categories by ordering field
 * 
 * @param type $a
 * @param type $b
 * @return int
 */    
    private function sortTreeOrdering($a, $b)
    {
        if ($a->getOrdering() < $b->getOrdering()) {
            return -1;
        } elseif ($a->getOrdering() > $b->getOrdering()) {
            return 1;
        }
        return 0;
        
    }

/**
 * Sort tree function
 * 
 * @param array $objects Categories
 * @return array Output array
 */    
    private function sortTree($objects)
    {
        $items = array();
        usort($objects, array($this, 'sortTreeOrdering'));
        $this->sortTreeCategory($objects, $items);
        return $items;
    }
    
/**
 * Get view array for input template
 * 
 * @param string $idPrefix Prefix for input`s id property
 * @return array View array
 */    
    public function getInputView($idPrefix)
    {
        $categories = $this->sortTree($this->configuration['categories']);
        return array(
            'type' => $this->getType(),
            'defaultTemplate' => $this->getDefaultTemplate(),
            'name' => $this->name,
            'inputName' => $this->prefix.$this->name,
            'inputId' => $idPrefix.$this->prefix.$this->name,
            'configuration' => $this->configuration,
            'value' => $this->value,
            'error' => $this->error,
            'disabled' => $this->disabled,
            'categories' => $categories,
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
        if (($this->configuration['multiply'] == true) && ($this->configuration['required'] == true)) {
            $code .= 'var j = 0;if (form["'.$this->prefix.$this->name.'[]"] !== undefined) {for (var i in form["'.$this->prefix.$this->name.'[]"]) {if ((form["'.$this->prefix.$this->name.'[]"][i].selected) || (form["'.$this->prefix.$this->name.'[]"][i].checked)) {j++;}}}if (j == 0) {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
        return $code;
    }
    
}
