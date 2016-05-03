<?php

/**
 * Form Exception Class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2015 SergSXM
 */

namespace Sergsxm\UIBundle\Form;

class FormException extends \Exception
{
    
    public function __construct($message, $code = 0, \Exception $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
    
}
