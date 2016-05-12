<?php

/**
 * Tree item entity interface with nested set fields
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

interface TreeNSInterface extends TreeInterface
{
    
    public function setLeftKey($left);
    
    public function setRightKey($right);
    
    public function setLevel($level);
    
}
