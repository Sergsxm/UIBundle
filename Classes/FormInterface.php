<?php

/**
 * Form interface
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

use Symfony\Component\HttpFoundation\Request;
use Sergsxm\UIBundle\Exceptions\FormException;

interface FormInterface
{
    
/**
 * Add field to form
 * 
 * @param string $type Field type (name of class or form_input_type name)
 * @param string $name Input name
 * @param array $configuration Input configuration
 * @param object $mappingObject Object for input value mapping
 * @return \Sergsxm\UIBundle\Classes\FormInterface Form object
 */    
    public function addField($type, $name, $configuration = array(), $mappingObject = 'parent');

/**
 * Bind form request
 * 
 * @param Request $request Symfony2 request object
 * @return boolean Values are accepted and there are no errors
 */
    public function bindRequest(Request $request = null);

/**
 * Get bind result
 * 
 * @return boolean Values are accepted and there are no errors
 */    
    public function getResult();
    
/**
 * Open group
 * 
 * @param string $name Subgroup name
 * @param string $description Subgroup description
 * @param string $condition The condition under which the subgroup will be processed
 * @return \Sergsxm\UIBundle\Classes\FormInterface Form object
 */    
    public function openGroup($name, $description = '', $condition = '');
    
/**
 * Close current group and return to the parent group
 * 
 * @return \Sergsxm\UIBundle\Classes\FormInterface Form object
 */    
    public function closeGroup();

/**
 * Get form value
 * 
 * @return array Form value
 */    
    public function getValue();
            
/**
 * Set form value
 * 
 * @param array $value Form value
 * @return \Sergsxm\UIBundle\Classes\FormInterface Form object
 */    
    public function setValue($value);
            
}
