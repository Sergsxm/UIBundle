<?php

/**
 * Tree form class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TreeForm;

use Sergsxm\UIBundle\TreeForm\TreeFormException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TreeForm
{
    private $container;
    private $configuration;
    private $isFrozen = false;
    private $encType = 'multipart/form-data';
    private $result = true;
    private $formId = null;

/**
 * Constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param array $configuration Configuration
 * @param array $objects Tree objects
 */    
    public function __construct(ContainerInterface $container, $configuration = array(), $objects = array())
    {
        $this->container = $container;
        $this->configuration = array_merge($this->getDefaultConfiguration(), $configuration);
        if (($this->configuration['createEnabled'] == true) && (!is_callable($this->configuration['createCallback']))) {
            throw new TreeFormException(__CLASS__.': createCallback must be specified in configuration');
        }
        if ($this->configuration['loadDoctrineRepository'] !== null) {
            $this->objects = $this->container->get('doctrine')->getRepository($this->configuration['loadDoctrineRepository'])->findAll();
        } else {
            $this->objects = $objects;
        }
        if (!is_array($this->objects)) {
            $this->objects = array();
        }
        foreach ($this->objects as $object) {
            if (!$object instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                throw new TreeFormException(__CLASS__.': objects must be instance of \Sergsxm\UIBundle\Classes\TreeInterface');
            }
        }
    }

/**
 * Get default configuration
 * 
 * @return array Defaults
 */    
    private function getDefaultConfiguration()
    {
        return array(
            'createCallback' => null,
            'changeCallback' => null,
            'removeCallback' => null,
            'url' => null,
            'createEnabled' => false,
            'removeEnabled' => false,
            'readOnly' => false,
            'mapIdToParentProperty' => false,
            'loadDoctrineRepository' => null,
        );
    }
    
/**
 * Get CSFR token for validation
 * 
 * @return string Token
 */    
    private function getCsrfToken()
    {
        return $this->container->get('request_stack')->getMasterRequest()->getSession()->get('sergsxm_treeform_csrf');
    }

/**
 * Generate CSFR token
 * 
 * @return string Token
 */    
    private function generateCsrfToken()
    {
        if ($this->container->get('request_stack')->getMasterRequest()->getSession()->has('sergsxm_treeform_csrf')) {
            return $this->getCsrfToken();
        }
        $randomValue = random_bytes(32);
        $token = rtrim(strtr(base64_encode($randomValue), '/+', '-_'), '=');
        $this->container->get('request_stack')->getMasterRequest()->getSession()->set('sergsxm_treeform_csrf', $token);
        return $token;
    }

/**
 * Sort tree for view and convart to nesting based array
 * 
 * @param array $objects Objects
 * @param array $items Output array
 * @param object $parent Parent to search childs
 * @param int $nesting Current nesting
 */    
    private function sortTreeCategory($objects, &$items, $parent = null, $nesting = 0)
    {
        foreach ($objects as $object) {
            $currentParent = $object->getParent();
            if ($currentParent instanceof \Sergsxm\UIBundle\Classes\TreeInterface) {
                $currentParent = $currentParent->getId();
            }
            if ($currentParent == $parent) {
                $url = null;
                if ($this->configuration['url'] != null) {
                    if (strpos($this->configuration['url'], '/') === false) {
                        $url = $this->container->get('router')->generate($this->configuration['url'], array('id' => $object->getId()));
                    } else {
                        $url = str_replace('{{id}}', $object->getId(), $this->configuration['url']);
                    }
                }
                $items[] = array(
                    'id' => $object->getId(),
                    'title' => $object->getTitle(),
                    'nesting' => $nesting,
                    'url' => $url,
                );
                $this->sortTreeCategory($objects, $items, $object->getId(), $nesting + 1);
            }
        }
    }

/**
 * Sort tree categories by ordering field
 * 
 * @param type $a
 * @param type $b
 * @return int
 */    
    private function sortTreeOrdering($a, $b)
    {
        if ($a->getOrdering() < $b->getOrdering()) {
            return -1;
        } elseif ($a->getOrdering() > $b->getOrdering()) {
            return 1;
        }
        return 0;
        
    }

/**
 * Sort tree function
 * 
 * @param array $objects Categories
 * @return array Output array
 */    
    private function sortTree($objects)
    {
        $items = array();
        usort($objects, array($this, 'sortTreeOrdering'));
        $this->sortTreeCategory($objects, $items);
        return $items;
    }
    
/**
 * Get view parameters for template
 * 
 * @return array View parameters
 */    
    public function getView()
    {
        $items = $this->sortTree($this->objects);
        $this->isFrozen = true;
        $formId = $this->getFormId();
        return array(
            'encType' => $this->encType,
            'rest' => '<input type="hidden" name="form_token" value="'.$this->generateCsrfToken().'" /><input type="hidden" name="form_id" value="'.$formId.'" />',
            'result' => $this->result,
            'readOnly' => $this->configuration['readOnly'],
            'configuration' => $this->configuration,
            'items' => $items,
            'formId' => $formId,
        );
    }

/**
 * Get view of form as content
 * 
 * @param string $template Template
 * @param array $parameters Additional parameters
 * @return string View content
 */    
    public function renderView($template = 'SergsxmUIBundle:TreeForm:TreeForm.html.twig', $parameters = array())
    {
        return $this->container->get('templating')->render($template, array_merge($parameters, $this->getView()));
    }

/**
 * Get view of form as response
 * 
 * @param string $template Template
 * @param array $parameters Additional parameters
 * @return Response View response
 */    
    public function render($template = 'SergsxmUIBundle:TreeForm:TreeForm.html.twig', $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($template, array_merge($parameters, $this->getView()), $response);
    }

/**
 * Set read only configuration
 * 
 * @param boolean $readOnly Read only
 * @return \Sergsxm\UIBundle\TreeForm\TreeForm Tree form object
 */    
    public function setReadOnly($readOnly)
    {
        $this->configuration['readOnly'] = $readOnly;
        return $this;
    }
    
/**
 * Get bind result
 * 
 * @return boolean Values are accepted and there are no errors
 */    
    public function getResult()
    {
        return $this->result;
    }

/**
 * Create tree item
 * 
 * @param string $title Title
 * @param object|null $parent Parent
 * @param int $ordering Ordering
 * @return object Created item object
 */    
    private function createItem($title, $parent, $ordering)
    {
        if ($this->configuration['createEnabled'] == false) {
            throw new TreeFormException(__CLASS__.': somthing wrong, item create is disabled but create function is called');
        }
        if (($this->configuration['mapIdToParentProperty'] == true) && ($parent !== null)) {
            $parent = $parent->getId();
        }
        return call_user_func($this->configuration['createCallback'], $title, $parent, $ordering);
    }

/**
 * Change tree item
 * 
 * @param object $item Item object
 * @param object|null $parent Parent
 * @param int $ordering Ordering
 */    
    private function changeItem($item, $parent, $ordering)
    {
        if (($this->configuration['mapIdToParentProperty'] == true) && ($parent !== null)) {
            $parent = $parent->getId();
        }
        $item->setParent($parent);
        $item->setOrdering($ordering);
        if (is_callable($this->configuration['changeCallback'])) {
            call_user_func($this->configuration['changeCallback'], $item, $parent, $ordering);
        }
    }

/**
 * Remove tree item
 * 
 * @param object $item Item object
 */    
    private function removeItem($item)
    {
        if ($this->configuration['removeEnabled'] == false) {
            throw new TreeFormException(__CLASS__.': somthing wrong, item remove is disabled but remove function is called');
        }
        $this->container->get('doctrine')->getManager()->remove($item);
        if (is_callable($this->configuration['removeCallback'])) {
            call_user_func($this->configuration['removeCallback'], $item);
        }
    }

/**
 * Find tree item by ID
 * 
 * @param int $id ID
 * @return object|null Item object
 */    
    private function findItem($id)
    {
        foreach ($this->objects as $object) {
            if ($object->getId() == $id) {
                return $object;
            }
        }
        return null;
    }

/**
 * Sort POST tree function
 * 
 * @param type $a
 * @param type $b
 * @return int 
 */    
    private function sortPostTree($a, $b)
    {
        if ($a['ordering'] < $b['ordering']) {
            return -1;
        } elseif ($a['ordering'] > $b['ordering']) {
            return 1;
        }
        return 0;
    }

/**
 * Filter POST tree function
 * 
 * @param type $a
 * @return boolean
 */    
    private function filterPostTree($a)
    {
        if (is_array($a) && isset($a['ordering']) && isset($a['nesting'])) {
            return true;
        }
        return false;
    }
    
/**
 * Bind form request
 * 
 * @param Request $request Symfony2 request object
 * @return boolean Values are accepted and there are no errors
 */
    public function bindRequest(Request $request = null)
    {
        $this->isFrozen = true;
        if ($this->configuration['readOnly'] == true) {
            $this->result = false;
            return false;
        }
        if ($request == null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        if (($request->getMethod() == 'POST') && ($request->get('form_id') == $this->getFormId())) {
            if ($request->get('form_token') !== $this->getCsrfToken()) {
                $this->result = false;
                return false;
            }
            $tree = $request->get('tree');
            $foundedIds = array();
            $curentParents = array();
            $newObjects = array();
            if (is_array($tree)) {
                $tree = array_filter($tree, array($this, 'filterPostTree'));
                uasort($tree, array($this, 'sortPostTree'));
                foreach ($tree as $treeId=>$treeParameters) {
                    $currentParent = (isset($curentParents[$treeParameters['nesting'] - 1]) ? $curentParents[$treeParameters['nesting'] - 1] : null);
                    $item = $this->findItem($treeId);
                    if ($item == null) {
                        $item = $this->createItem((isset($treeParameters['title']) ? $treeParameters['title'] : ''), $currentParent, $treeParameters['ordering']);
                    } else {
                        $this->changeItem($item, $currentParent, $treeParameters['ordering']);
                    }
                    $foundedIds[] = $item->getId();
                    $curentParents[$treeParameters['nesting']] = $item;
                    $newObjects[] = $item;
                }
            }
            foreach ($this->objects as $object) {
                if (!in_array($object->getId(), $foundedIds)) {
                    $this->removeItem($object);
                }
            }
            $this->container->get('doctrine')->getManager()->flush();
            $this->objects = $newObjects;
        } else {
            $this->result = false;
        }
        return $this->result;
    }

/**
 * Clear form data
 */    
    public function clear()
    {
    }
    
/**
 * Return form id
 * 
 * @return string Form id
 */    
    public function getFormId()
    {
        if ($this->formId !== null) {
            return $this->formId;
        }
        return 'formtree';
    }

/**
 * Set form id
 * 
 * @param string $formId Form id
 * @return \Sergsxm\UIBundle\TreeForm\TreeForm Form object
 */    
    public function setFormId($formId)
    {
        if ($this->isFrozen == true) {
            throw new FormException(__CLASS__.': you can`t set formId after bindRequest() or getView() methods are called');
        }
        $this->formId = $formId;
        return $this;
    }

}
