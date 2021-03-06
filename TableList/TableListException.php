<?php

/**
 * Table list Exception Class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableList;

class TableListException extends \Exception
{
    
    public function __construct($message, $code = 0, \Exception $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
    
}
