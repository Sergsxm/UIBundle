<?php

/**
 * Table list class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Classes;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sergsxm\UIBundle\Exceptions\TableListException;
use Sergsxm\UIBundle\Classes\TableListTab;

class TableList
{
    
    private $container;
    private $tabs;
    private $currentTab = null;
    private $activeTab = null;
    private $ajaxMode = false;

/**
 * Table list constructor
 * 
 * @param Container $container Symfony2 container
 * @return TableList Table list object
 */    
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->tabs = array();
        
        return $this;
    }

/**
 * Add tab to list and select it to the current tab
 * 
 * @param string $repository Doctrine main repository for list
 * @param string $name Tab name
 * @param string $description Tab description
 * @return TableList Table list object
 */    
    public function addTab($repository, $name, $description = null)
    {
        if (isset($this->tabs[$name])) {
            throw new TableListException(__CLASS__.': tab "'.$name.'" already exist');
        }
        
        $this->tabs[$name] = new TableListTab($this->container, $repository, $name, $description);
        $this->currentTab = $this->tabs[$name];
        return $this;
    }

/**
 * Get tab object by name
 * 
 * @param string $name Tab name
 * @return TableListTab|null Tab object
 */    
    public function getTab($name)
    {
        if (isset($this->tabs[$name])) {
            return $this->tabs[$name];
        }
        return null;
    }

/**
 * Select tab as current by name
 * 
 * @param string $name Tab name
 * @return TableList Table list object
 */    
    public function selectTab($name)
    {
        if (!isset($this->tabs[$name])) {
            throw new TableListException(__CLASS__.': tab "'.$name.'" not exist');
        }
        $this->currentTab = $this->tabs[$name];
        return $this;
    }
    
/**
 * Add column to table list
 * 
 * @param string $type Type of column
 * @param string $name Name of entity field (or sql)
 * @param array $configuration Additional configuration
 * @return TableList Table list object
 */    
    public function addColumn($type, $name, $configuration = array())
    {
        if ($this->currentTab == null) {
            throw new TableListException(__CLASS__.': select tab before add column');
        }
        $this->currentTab->addColumn($type, $name, $configuration);
        return $this;
    }   
    
/**
 * Add url button into action panel of tab 
 * 
 * @param string $name Action name
 * @param string $url Url
 * @param array $configuration Additional configuration
 * @return TableList Table list object
 */    
    public function addUrlAction($name, $url, $configuration = array())
    {
        if ($this->currentTab == null) {
            throw new TableListException(__CLASS__.': select tab before add URL action');
        }
        $this->currentTab->addUrlAction($name, $url, $configuration);
        return $this;
    }

/**
 * Add ajax action button into action panel of tab
 * 
 * @param string $name Action name
 * @param string $sql SQL
 * @param array $configuration Additional configuration
 * @return TableList Table list object
 */    
    public function addAjaxAction($name, $sql, $configuration = array())
    {
        if ($this->currentTab == null) {
            throw new TableListException(__CLASS__.': select tab before add ajax action');
        }
        $this->currentTab->addAjaxAction($name, $sql, $configuration);
        return $this;
    }
    
/**
 * Bind request
 * 
 * @param Request $request Symfony2 request
 */    
    public function bindRequest(Request $request = null)
    {
        if ($request == null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        $this->ajaxMode = ($request->get('ajax') == 'true' ? true : false);
        $this->activeTab = $request->getSession()->get('tab_'.$request->get('_route').'_tab');
        if (($request->get('tab') != null) && isset($this->tabs[$request->get('tab')])) {
            $this->activeTab = $request->get('tab');
            $request->getSession()->set('tab_'.$request->get('_route').'_tab', $this->activeTab);
        }
        if (isset($this->tabs[$this->activeTab])) {
            $this->tabs[$this->activeTab]->bindRequest($request);
        }
    }

/**
 * Get view
 * 
 * @return array List view
 */    
    public function getView()
    {
        if (($this->activeTab == null) || (!isset($this->tabs[$this->activeTab]))) {
            $tabNames = array_keys($this->tabs);
            $this->activeTab = $tabNames[0];
        }
        if ($this->ajaxMode == true) {
            $view = $this->tabs[$this->activeTab]->getView();
            return array(
                'ajaxMode' => true,
                'activeTab' => $this->activeTab,
                'tab' => $view,
            );
        }
        $description = array();
        foreach ($this->tabs as $tabName=>$tab) {
            $description[$tabName] = $tab->getDescription();
        }
        $view = $this->tabs[$this->activeTab]->getView();
        return array(
            'activeTab' => $this->activeTab,
            'tabsDescription' => $description,
            'tab' => $view,
        );
    }

/**
 * Render list
 * 
 * @param string $template Template for default mode
 * @param string $ajaxTemplate Template for ajax mode
 * @param array $parameters Additional parameters for template
 * @return string Content
 */    
    public function renderView($template = 'SergsxmUIBundle:TableLists:TableList.html.twig', $ajaxTemplate = 'SergsxmUIBundle:TableLists:TableListAjax.html.twig', $parameters = array())
    {
        if ($this->ajaxMode == true) {
            return $this->container->get('templating')->render($ajaxTemplate, array_merge($parameters, $this->getView()));
        }
        return $this->container->get('templating')->render($template, array_merge($parameters, $this->getView()));
    }

/**
 * Render list
 * 
 * @param string $template Template for default mode
 * @param string $ajaxTemplate Template for ajax mode
 * @param array $parameters Additional parameters for template
 * @return Response Symfony2 Response
 */    
    public function render($template = 'SergsxmUIBundle:TableLists:TableList.html.twig', $ajaxTemplate = 'SergsxmUIBundle:TableLists:TableListAjax.html.twig', $parameters = array(), Response $response = null)
    {
        if ($this->ajaxMode == true) {
            return $this->container->get('templating')->renderResponse($ajaxTemplate, array_merge($parameters, $this->getView()));
        }
        return $this->container->get('templating')->renderResponse($template, array_merge($parameters, $this->getView()), $response);
    }
    
}
