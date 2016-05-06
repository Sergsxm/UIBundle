<?php

/**
 * Order item entity interface
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

interface OrderInterface
{
    
    public function getId();
    
    public function getTitle();
    
    public function getOrder();
    
    public function setOrder($order);
    
}
