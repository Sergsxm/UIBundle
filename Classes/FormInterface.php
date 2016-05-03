<?php

/**
 * Form interface
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

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

}
