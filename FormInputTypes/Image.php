<?php

/**
 * Image form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Form\FormInput;
use Sergsxm\UIBundle\Classes\ImageInterface;
use Sergsxm\UIBundle\Form\FormBag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Form\FormException;

class Image extends FormInput
{

    const ST_FILE = 0;
    const ST_DOCTRINE = 1;

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
        
        $this->configuration['storeFolder'] = rtrim($this->configuration['storeFolder'], '/\\');
        if (!in_array($this->configuration['storeType'], array(self::ST_FILE, self::ST_DOCTRINE))) {
            throw new FormException(__CLASS__.': unknown storeType');
        }
        if ((!isset($this->configuration['mapping'])) || ($this->configuration['mapping'] == true)) {
            $this->mappingObject = $mappingObject;
        }
        if ($this->mappingObject !== null) {
            $reflector = new \ReflectionObject($mappingObject);
            $this->mappingProperty = $reflector->getProperty($name);
            $this->mappingProperty->setAccessible(true);
            $value = $this->mappingProperty->getValue($mappingObject);
            if ($this->configuration['multiply'] == false) {
                if (($this->configuration['storeType'] == self::ST_FILE) && is_string($value)) {
                    $this->value = $this->restoreFromFile($value);
                } elseif ($value instanceof ImageInterface) {
                    $this->value = $value;
                } else {
                    $this->value = null;
                }
            } else {
                $this->value = array();
                if (($this->configuration['storeType'] == self::ST_FILE) && (is_array($value))) {
                    foreach ($value as $file) {
                        $this->value[] = $this->restoreFromFile($file);
                    }
                } elseif (($this->configuration['storeType'] == self::ST_DOCTRINE) && ($value instanceof \Doctrine\Common\Collections\Collection)) {
                    foreach ($value->toArray() as $file) {
                        if ($file instanceof ImageInterface) {
                            $this->value[] = $file;
                        }
                    }
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
        return 'image';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Image.html.twig';
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
            'multiply' => false,
            'maxSize' => null,
            'maxSizeError' => 'File size is larger than allowed',
            'minWidth' => null,
            'minHeight' => null,
            'maxWidth' => null,
            'maxHeight' => null,
            'imageSizeError' => 'Wrong image size',
            'notImageError' => 'The file is not an image',
            'storeType' => self::ST_FILE,
            'storeFolder' => 'uploads',
            'storeDoctrineClass' => '',
        );
    }

/**
 * Validate value
 * 
 * @return boolean There are no errors
 */
    public function validateValue()
    {
        if ($this->configuration['multiply'] == false) {
            if (($this->configuration['required'] == true) && ($this->value == null)) {
                $this->error = $this->configuration['requiredError'];
                return false;
            }
            if (($this->configuration['maxSize'] != null) && ($this->value != null) && ($this->value->getSize() > $this->configuration['maxSize'])) {
                $this->error = $this->configuration['maxSizeError'];
                return false;
            }
            if ($this->value != null) {
                $imageSize = $this->value->getImageSize();
                if ($imageSize === null) {
                    $this->error = $this->configuration['notImageError'];
                    return false;
                }
                if (($this->configuration['minWidth'] !== null) && ($imageSize['width'] < $this->configuration['minWidth'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
                if (($this->configuration['maxWidth'] !== null) && ($imageSize['width'] > $this->configuration['maxWidth'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
                if (($this->configuration['minHeight'] !== null) && ($imageSize['height'] < $this->configuration['minHeight'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
                if (($this->configuration['maxHeight'] !== null) && ($imageSize['height'] > $this->configuration['maxHeight'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
            }
        } else {
            if (($this->configuration['required'] == true) && (count($this->value) == 0)) {
                $this->error = $this->configuration['requiredError'];
                return false;
            }
            foreach ($this->value as $value) {
                if (($this->configuration['maxSize'] != null) && ($value->getSize() > $this->configuration['maxSize'])) {
                    $this->error = $this->configuration['maxSizeError'];
                    return false;
                }
                $imageSize = $value->getImageSize();
                if ($imageSize === null) {
                    $this->error = $this->configuration['notImageError'];
                    return false;
                }
                if (($this->configuration['minWidth'] !== null) && ($imageSize['width'] < $this->configuration['minWidth'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
                if (($this->configuration['maxWidth'] !== null) && ($imageSize['width'] > $this->configuration['maxWidth'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
                if (($this->configuration['minHeight'] !== null) && ($imageSize['height'] < $this->configuration['minHeight'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
                if (($this->configuration['maxHeight'] !== null) && ($imageSize['height'] > $this->configuration['maxHeight'])) {
                    $this->error = $this->configuration['imageSizeError'];
                    return false;
                }
            }
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
        if ($this->configuration['multiply'] == false) {
            if ($this->configuration['required'] == true) {
                $code .= 'if ((form["'.$this->prefix.$this->name.'"].value == "") && (form["'.$this->prefix.$this->name.'_file"].value == "")) {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
            }
            $code .= 'if ((form["'.$this->prefix.$this->name.'_file"] != undefined) && (form["'.$this->prefix.$this->name.'_file"].files != undefined) && (form["'.$this->prefix.$this->name.'_file"].files[0] != undefined)) {'.self::JS_EOL;
            if ($this->configuration['maxSize'] != null) {
                $code .= 'if (form["'.$this->prefix.$this->name.'_file"].files[0].size > '.$this->configuration['maxSize'].') {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['maxSizeError']).';}'.self::JS_EOL;
            }
            $mimeTypes = array('image/jpeg', 'image/png', 'image/gif');
            $code .= 'if (function (v) {var a = '.json_encode($mimeTypes).';for(var i in a) {if(a[i] == v) return true;}return false;}(form["'.$this->prefix.$this->name.'_file"].files[0].type) == false) {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['notImageError']).';}'.self::JS_EOL;
            $code .= '}'.self::JS_EOL;
        } else {
            if ($this->configuration['required'] == true) {
                $code .= 'if (((form["'.$this->prefix.$this->name.'[]"] === undefined) || (form["'.$this->prefix.$this->name.'[]"].length == 0)) && (form["'.$this->prefix.$this->name.'_file"].value == "")) {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
            }
            $code .= 'if ((form["'.$this->prefix.$this->name.'_file"] != undefined) && (form["'.$this->prefix.$this->name.'_file"].files != undefined) && (form["'.$this->prefix.$this->name.'_file"].files[0] != undefined)) {'.self::JS_EOL;
            if ($this->configuration['maxSize'] != null) {
                $code .= 'if (form["'.$this->prefix.$this->name.'_file"].files[0].size > '.$this->configuration['maxSize'].') {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['maxSizeError']).';}'.self::JS_EOL;
            }
            $mimeTypes = array('image/jpeg', 'image/png', 'image/gif');
            $code .= 'if (function (v) {var a = '.json_encode($mimeTypes).';for(var i in a) {if(a[i] == v) return true;}return false;}(form["'.$this->prefix.$this->name.'_file"].files[0].type) == false) {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['notImageError']).';}'.self::JS_EOL;
            $code .= '}'.self::JS_EOL;
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
            if ($this->configuration['multiply'] == false) {
                if (($this->configuration['storeType'] == self::ST_FILE) && ($this->value != null)) {
                    $this->mappingProperty->setValue($this->mappingObject, $value->getId());
                } else {
                    $this->mappingProperty->setValue($this->mappingObject, $value);
                }
            } else {
                if ($this->configuration['storeType'] == self::ST_FILE) {
                    $collectionValue = array();
                    foreach ($value as $file) {
                        $collectionValue[] = $file->getId();
                    }
                    $this->mappingProperty->setValue($this->mappingObject, $collectionValue);
                } else {
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
        if ($this->disabled == true) {
            return true;
        }
        if ($request === null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        
        if ($request->getMethod() == 'POST') {
            $newValue = null;
            if ($request->files->has($prefix.$this->prefix.$this->name.'_file') && (($file = $request->files->get($prefix.$this->prefix.$this->name.'_file')) != null)) {
                if ($this->configuration['storeType'] == self::ST_FILE) {
                    $newValue = new \Sergsxm\UIBundle\Form\Image();
                } elseif ($this->configuration['storeType'] == self::ST_DOCTRINE) {
                    $newValue = new $this->configuration['storeDoctrineClass'];
                }
                $newValue->setFileName($file->getClientOriginalName());
                $newValue->setMimeType($file->getMimeType());
                $newValue->setUploadDate(new \DateTime('now'));
                do {
                    $randomBytes = pack('L', time()).random_bytes(20);
                    $newFileName = rtrim(strtr(base64_encode($randomBytes), '/+', '-_'), '=');
                } while (file_exists($this->configuration['storeFolder'].DIRECTORY_SEPARATOR.$newFileName));
                $file->move($this->configuration['storeFolder'], $newFileName);
                $newValue->setContentFile($this->configuration['storeFolder'].DIRECTORY_SEPARATOR.$newFileName);
                if ($this->configuration['storeType'] == self::ST_FILE) {
                    $newValue->storeInfo();
                } elseif ($this->configuration['storeType'] == self::ST_DOCTRINE) {
                    $em = $this->container->get('doctrine')->getManager();
                    $em->persist($newValue);
                    $em->flush($newValue);
                }
            }
            if ($this->configuration['multiply'] == false) {
                if ($newValue !== null) {
                    $value = $newValue;
                } else {
                    $valueId = $request->get($prefix.$this->prefix.$this->name);
                    if ($this->configuration['storeType'] == self::ST_FILE) {
                        $value = $this->restoreFromFile($valueId);
                    } elseif ($this->configuration['storeType'] == self::ST_DOCTRINE) {
                        $value = $this->restoreFromDoctrine($valueId);
                    }
                }
            } else {
                $value = array();
                $valueIds = $request->get($prefix.$this->prefix.$this->name);
                if (is_array($valueIds)) {
                    foreach ($valueIds as $valueId) {
                        if ($this->configuration['storeType'] == self::ST_FILE) {
                            $value[] = $this->restoreFromFile($valueId);
                        } elseif ($this->configuration['storeType'] == self::ST_DOCTRINE) {
                            $value[] = $this->restoreFromDoctrine($valueId);
                        }
                    }
                }
                if ($newValue !== null) {
                    $value[] = $newValue;
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
        $this->formBag->set($this->prefix.$this->name.'_file', array(
            'type' => 'image',
            'maxSize' => $this->configuration['maxSize'],
            'minWidth' => $this->configuration['minWidth'],
            'maxWidth' => $this->configuration['maxWidth'],
            'minHeight' => $this->configuration['minHeight'],
            'maxHeight' => $this->configuration['maxHeight'],
            'storeType' => $this->configuration['storeType'],
            'storeFolder' => $this->configuration['storeFolder'],
            'storeDoctrineClass' => $this->configuration['storeDoctrineClass'],
            'maxSizeError' => $this->configuration['maxSizeError'],
            'imageSizeError' => $this->configuration['imageSizeError'],
            'notImageError' => $this->configuration['notImageError'],
            'multiply' => $this->configuration['multiply'],
        ));
        $thumbnails = array();
        if ($this->configuration['multiply'] == false) {
            if ($this->value != null) {
                $thumbnails[$this->value->getId()] = $this->container->get('router')->generate('sergsxm_ui_file_thumbnail', array(
                    'form_id' => $this->formBag->getFormId(), 
                    'input_name' => $this->prefix.$this->name.'_file',
                    'id' => $this->value->getId()
                ));
            }
        } else {
            foreach ($this->value as $file) {
                $thumbnails[$file->getId()] = $this->container->get('router')->generate('sergsxm_ui_file_thumbnail', array(
                    'form_id' => $this->formBag->getFormId(), 
                    'input_name' => $this->prefix.$this->name.'_file',
                    'id' => $file->getId()
                ));
            }
        }
        return array(
            'type' => $this->getType(),
            'defaultTemplate' => $this->getDefaultTemplate(),
            'name' => $this->name,
            'inputName' => $this->prefix.$this->name,
            'inputNameFile' => $this->prefix.$this->name.'_file',
            'inputId' => $idPrefix.$this->prefix.$this->name,
            'inputIdFile' => $idPrefix.$this->prefix.$this->name.'_file',
            'configuration' => $this->configuration,
            'value' => $this->value,
            'error' => $this->error,
            'disabled' => $this->disabled,
            'thumbnails' => $thumbnails,
        );
    }

/**
 * Restore image entity from doctrine store
 * 
 * @param string $id Image id
 * @return Image Image entity
 */    
    private function restoreFromDoctrine($id)
    {
        return $this->container->get('doctrine')->getRepository($this->configuration['storeDoctrineClass'])->find($id);
    }
    
/**
 * Restore image entity from file store
 * 
 * @param string $id Image id
 * @return Image Image entity
 */    
    private function restoreFromFile($id)
    {
        return \Sergsxm\UIBundle\Form\Image::restore($id);
    }
    
}
