<?php

/**
 * Form input annotation
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Annotations;

/**
 * @Annotation
 */
class FormField
{
    public $type;
    public $configuration;
    public $translate;
    public $translateDomain;
}
