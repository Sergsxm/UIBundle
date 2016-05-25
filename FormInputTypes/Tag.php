<?php

/**
 * Tag form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Form\FormInput;
use Sergsxm\UIBundle\Classes\TagInterface;
use Sergsxm\UIBundle\Form\FormBag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Form\FormException;

class Tag extends FormInput
{

/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param FormBag $formBag Form`s parameters bag
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param string $prefix Input prefix
 * @param object $mappingObject Object for input value mapping
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
        
        if (!class_exists($this->configuration['doctrineClass'])) {
            throw new FormException(__CLASS__.': unknown tag class '.$this->configuration['doctrineClass']);
        }
        if (!is_subclass_of($this->configuration['doctrineClass'], '\Sergsxm\UIBundle\Classes\TagInterface')) {
            throw new FormException(__CLASS__.': class '.$this->configuration['doctrineClass'].' must implements \Sergsxm\UIBundle\Classes\TagInterface');
        }
        if ((!isset($this->configuration['mapping'])) || ($this->configuration['mapping'] == true)) {
            $this->mappingObject = $mappingObject;
        }
        $this->value = array();
        if ($this->mappingObject !== null) {
            $reflector = new \ReflectionObject($mappingObject);
            $this->mappingProperty = $reflector->getProperty($name);
            $this->mappingProperty->setAccessible(true);
            $value = $this->mappingProperty->getValue($mappingObject);
            if ($value instanceof \Doctrine\Common\Collections\Collection) {
                foreach ($value->toArray() as $tag) {
                    if ($tag instanceof TagInterface) {
                        $this->value[] = $tag;
                    }
                }
            }
        }
        if (isset($configuration['disabled'])) {
            $this->disabled = $configuration['disabled'];
        }
        if (($this->configuration['createCallback'] != null) && !is_callable($this->configuration['createCallback'])) {
            throw new FormException(__CLASS__.': createCallback parameter must be callable');
        }
    }
    
/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'tag';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Tag.html.twig';
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
            'doctrineClass' => null,
            'tagProperty' => 'tag',
            'createCallback' => null,
        );
    }

/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if (($this->configuration['required'] == true) && (count($this->value) == 0)) {
            $this->error = $this->configuration['requiredError'];
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
            $code .= 'if (((form["'.$this->prefix.$this->name.'[]"] === undefined) || (form["'.$this->prefix.$this->name.'[]"].length == 0)) && (form["'.$this->prefix.$this->name.'_new"].value == "")) {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
        return $code;
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
            $collectionValue = $this->mappingProperty->getValue($this->mappingObject);
            if (!$collectionValue instanceof \Doctrine\Common\Collections\Collection) {
                $collectionValue = new \Doctrine\Common\Collections\ArrayCollection();
            }
            $collectionValue->clear();
            foreach ($value as $file) {
                $collectionValue->add($file);
            }
            $this->mappingProperty->setValue($this->mappingObject, $collectionValue);
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
        if ($this->disabled == true) {
            return true;
        }
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $value = array();
            $valueTags = $request->get($prefix.$this->prefix.$this->name);
            if (!is_array($valueTags)) {
                $valueTags = array();
            }
            $new = $request->get($prefix.$this->prefix.$this->name.'_new');
            if ($new != null) {
                $valueTags = array_merge($valueTags, explode(',', $new));
            }
            $valueTags = array_filter(array_map('trim', $valueTags));
            $em = $this->container->get('doctrine')->getManager();
            foreach ($valueTags as $valueTag) {
                $tagEntity = $this->getTagFromDoctrine($valueTag);
                if (empty($tagEntity)) {
                    $tagEntity = new $this->configuration['doctrineClass'];
                    $tagEntity->setTag($valueTag);
                    if ($this->configuration['createCallback'] != null) {
                        call_user_func($this->configuration['createCallback'], $tagEntity);
                    }
                    $em->persist($tagEntity);
                    $em->flush($tagEntity);
                }
                if (!in_array($tagEntity, $value)) {
                    $value[] = $tagEntity;
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
            'inputNameNew' => $this->prefix.$this->name.'_new',
            'inputIdNew' => $idPrefix.$this->prefix.$this->name.'_new',
            'configuration' => $this->configuration,
            'value' => $this->value,
            'error' => $this->error,
            'disabled' => $this->disabled,
        );
    }
    
/**
 * Get tag entity from doctrine repository
 * 
 * @param string $tag Tag
 * @return object|null Tag entity
 */
    private function getTagFromDoctrine($tag)
    {
        $tagPropertyName = $this->configuration['tagProperty'];
        return $this->container->get('doctrine')->getRepository($this->configuration['doctrineClass'])->findOneBy(array($tagPropertyName => $tag));
    }
    
}
