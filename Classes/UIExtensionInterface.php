<?php

/**
 * UIBundle extension module interface
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

interface UIExtensionInterface
{
    
    public function getFormInputTypes();
    
    public function getCaptchaTypes();
    
    public function getTableListColumns();
    
}
