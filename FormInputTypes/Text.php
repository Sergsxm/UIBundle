<?php

/**
 * Text form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Classes\FormInput;

class Text extends FormInput
{

/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'text';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Text.html.twig';
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
            'regexp' => '/^[\s\S]*$/i',
            'regexpError' => 'The field is not valid',
            'validateCallback' => null,
            'validateCallbackParameters' => null,
            'uniqueInDoctrine' => false,
            'uniqueError' => 'This value already exists in the database',
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
        if (!preg_match($this->configuration['regexp'].'u', $this->value)) {
            $this->error = $this->configuration['regexpError'];
            return false;
        }
        if ($this->configuration['validateCallback'] != null) {
            $this->error = call_user_func($this->configuration['validateCallback'], $this->value, $this->configuration['validateCallbackParameters']);
            if ($this->error != null) return false;
        }
        if (($this->configuration['uniqueInDoctrine'] == true) && ($this->mappingObject != null)) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $ids = $em->getClassMetadata(get_class($this->mappingObject))->getIdentifierValues($this->mappingObject);
            $queryConditions = array();
            foreach ($ids as $idKey=>$idVal) {
                $queryConditions[] = 'e.'.$idKey.' != :'.$idKey;
            }
            $queryConditions[] = 'e.'.$this->name.' = :value ';
            $result = $em->createQuery('SELECT count(e) FROM '.get_class($this->mappingObject).' e WHERE '.implode(' AND ',$queryConditions))->setParameters($ids)->setParameter('value', $this->value)->getSingleScalarResult();
            if ($result > 0) {
                $this->error = $this->configuration['uniqueError'];
                return false;
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
        $code = '';
        if ($this->configuration['required'] == true) {
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';} else'.self::JS_EOL;
        }
        $code .= 'if (!'.$this->configuration['regexp'].'.test(form["'.$this->prefix.$this->name.'"].value)) {errors["'.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['regexpError']).';}'.self::JS_EOL;
        return $code;
    }
    
}
