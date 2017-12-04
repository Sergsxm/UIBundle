<?php

/**
 * Email form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2017 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Form\FormInput;

class Email extends FormInput
{

    private $jsRegExp = '/^\s*[^@\s]{1,50}@[^@\s]{1,40}\.[^@\s]{1,15}\s*$/i';
    private $phpRegExp = '/^(?:(?:(?:(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?(?:[a-zA-Z0-9!#\\$%&\'\\*\\+\\-\\/=\\?\\^_`\\{\\}\\|~]+(\\.[a-zA-Z0-9!#\\$%&\'\\*\\+\\-\\/=\\?\\^_`\\{\\}\\|~]+)*)+(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?)|(?:(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?"((?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21\\x23-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?"(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?))@(?:(?:(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?(?:[a-zA-Z0-9!#\\$%&\'\\*\\+\\-\\/=\\?\\^_`\\{\\}\\|~]+(\\.[a-zA-Z0-9!#\\$%&\'\\*\\+\\-\\/=\\?\\^_`\\{\\}\\|~]+)*)+(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?)|(?:(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?\\[((?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x5A\\x5E-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])))*?(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\](?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))*(?:(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?(\\((?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])|(?:(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x19\\x7F]|[\\x21-\\x27\\x2A-\\x5B\\x5D-\\x7E])|(?:\\\\[\\x00-\\x08\\x0B\\x0C\\x0E-\\x7F])|(?1)))*(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])?\\)))|(?:(?:[ \\t]*(?:\\r\\n))?[ \\t])))?)))$/D';
    
/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'email';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Email.html.twig';
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
            'regexpError' => 'The email address is not valid',
            'trim' => false,
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
        if (($this->value != '') && !preg_match($this->phpRegExp, $this->value)) {
            $this->error = $this->configuration['regexpError'];
            return false;
        }
        if ($this->configuration['validateCallback'] != null) {
            $this->error = call_user_func($this->configuration['validateCallback'], $this->value, $this->configuration['validateCallbackParameters']);
            if ($this->error != null) return false;
        }
        if (($this->configuration['uniqueInDoctrine'] == true) && ($this->mappingObject != null)) {
            $em = $this->container->get('doctrine')->getManager();
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
        if ($this->disabled == true) {
            return '';
        }
        $code = '';
        if ($this->configuration['required'] == true) {
            $code .= 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';} else'.self::JS_EOL;
        }
        $code .= 'if ((form["'.$this->prefix.$this->name.'"].value != "") && !'.$this->jsRegExp.'.test(form["'.$this->prefix.$this->name.'"].value)) {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['regexpError']).';}'.self::JS_EOL;
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
        if ($this->configuration['trim'] == true) {
            $value = trim($value);
        }
        return parent::setValue($value);
    }    
}
