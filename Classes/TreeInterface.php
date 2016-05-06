<?php

/**
 * Tree item entity interface
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

interface TreeInterface
{
    
    public function getId();
    
    public function getTitle();
    
    public function getAddEnabled();
    
    public function getParent();
    
    public function setParent($parent);
    
    public function getOrder();
    
    public function setOrder($order);
    
}
