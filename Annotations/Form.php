<?php

/**
 * Form annotation
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Annotations;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Annotation
 */
class Form
{
    public $name;
    
    public $groups;
    
    public $fields;
    
    public function getGroups(TranslatorInterface $translator = null, $domain = null)
    {
        if (!is_array($this->groups)) {
            return array();
        }
        $items = array();
        foreach ($this->groups as $group) {
            if (!isset($group['name'])) {
                continue;
            }
            $description = (isset($group['description']) ? $group['description'] : '');
            if (($translator != null) && ($description != '')) {
                $description = $translator->trans($description, array(), $domain);
            }
            $items[$group['name']] = array(
                'name' => $group['name'],
                'description' => $description,
                'condition' => (isset($group['condition']) ? $group['condition'] : ''),
            );
        }
        return $items;
    }
    
}
