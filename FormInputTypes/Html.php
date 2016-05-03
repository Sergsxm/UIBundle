<?php

/**
 * HTML form input type
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\FormInputTypes;

use Sergsxm\UIBundle\Form\FormInput;

class Html extends FormInput
{

/**
 * Get type of form input
 * 
 * @return string Type
 */
    public function getType()
    {
        return 'html';
    }
    
/**
 * Get default template for input
 * 
 * @return string Default template
 */
    public function getDefaultTemplate()
    {
        return 'SergsxmUIBundle:FormInputTypes:Html.html.twig';
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
            'disableFilters' => false,
            'allowTags' => null,
            'allowStyleProperty' => true,
            'replaceUrl' => false,
            'replaceUrlPath' => null,
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
        $this->error = null;
        return true;
    }

/**
 * Define an HTML attribute and convert it
 * 
 * @param array $matches
 * @return string
 */    
    private function clearHtmlProperty($matches, $allowedProperties = array())
    {
        if (!isset($matches[1]) || !isset($matches[2])) {
            return '';
        }
        if ($this->configuration['allowStyleProperty'] == true) {
            $allowedProperties[] = 'style';
        }
        if (in_array($matches[1], $allowedProperties)) {
            if (($matches[1] == 'href') && ($this->configuration['replaceUrl'] == true)) {
                if ($this->configuration['replaceUrlPath'] == null) {
                    return 'href="#" onclick="window.open(\''.$matches[2].'\');"';
                } else {
                    if (strpos($this->configuration['replaceUrlPath'], '/') === false) {
                        $replaceUrl = $this->container->get('router')->generate($this->configuration['replaceUrlPath'], array('path' => $matches[2]));
                    } else {
                        $replaceUrl = $this->configuration['replaceUrlPath'].'?path='.urlencode($matches[2]);
                    }
                    return 'href="'.$replaceUrl.'"';
                }
            }
            return $matches[1].'="'.$matches[2].'"';
        }
        return '';
    }
     
    private function clearHtmlPropertyTd($matches)
    {
        $this->clearHtmlProperty($matches, array('colspan', 'rowspan'));
    }
     
    private function clearHtmlPropertyImg($matches)
    {
        $this->clearHtmlProperty($matches, array('src', 'title'));
    }

    private function clearHtmlPropertyA($matches)
    {
        $this->clearHtmlProperty($matches, array('href'));
    }
    
/**
 * Define HTML attributes in tag code and convert it
 * 
 * @param array $matches
 * @return string
 */     
    private function clearHtmlProperties($matches)
    {
        if (!isset($matches[1]) || !isset($matches[2])) {
            return '';
        }
        switch ($matches[1]) {
            case 'a':
                $methodName = 'clearHtmlPropertyA';
                break;
            case 'img':
                $methodName = 'clearHtmlPropertyImg';
                break;
            case 'th':
            case 'td':
                $methodName = 'clearHtmlPropertyTd';
                break;
            default:
                $methodName = 'clearHtmlProperty';
        }
        return '<'.$matches[1].' '.preg_replace_callback('/([A-z][A-z0-9]*)[\s]*\=[\s]*"([^"]*)"/i', array($this, $methodName) , $matches[2]).'>';
    }

/**
 * Clear HTML code
 * 
 * @param string $code HTML code
 * @return string Cleared code
 */    
    private function clearHtmlCode($code)
    {
        if ($this->configuration['disableFilters'] == false) {
            if ($this->configuration['allowTags'] != null) {
                $allowtags = explode(',', $this->configuration['allowTags']);
                $allowtagsstr = '';
                foreach ($allowtags as $tag) {
                    $allowtagsstr .= '<'.trim($tag).'>';
                }
                $code = strip_tags($code, $allowtagsstr);
            }
            $code = preg_replace_callback('/<([A-z][A-z0-9]*)[\s]+([^>]*)>/i', array($this, 'clearHtmlProperties') , $code);
        }
        return $code;
    }

/**
 * Unclear HTML code
 * 
 * @param string $code HTML code
 * @return string Uncleared code
 */    
    private function unclearHtmlCode($code)
    {
        if ($this->configuration['replaceUrl'] == true) {
            $code = preg_replace('/\shref="#" onclick="window\.open\(\'([^"\']*)\'\);"/ui', ' href="$1"', $code);
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
        return parent::setValue($this->clearHtmlCode($value));
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
            'configuration' => $this->configuration,
            'value' => $this->unclearHtmlCode($this->value),
            'error' => $this->error,
            'disabled' => $this->disabled,
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
        if ($this->configuration['required'] == true) {
            return 'if (form["'.$this->prefix.$this->name.'"].value == "") {errors["'.$idPrefix.$this->prefix.$this->name.'"] = '.json_encode($this->configuration['requiredError']).';}'.self::JS_EOL;
        }
    }
    
}
