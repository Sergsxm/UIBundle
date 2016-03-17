<?php

/**
 * Table list tab class
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

class TableListTab
{
    
    private $container;
    private $repository;
    private $cols;
    private $name;
    private $description;
    private $orderColumn;
    private $orderDirection;
    private $itemsInPage;
    private $page;
    private $search;
    private $actions;
    private $actionErrors;

/**
 * Table list tab constructor
 * 
 * @param Container $container Symfony2 container
 * @param string $repository Doctrine main repository for list
 * @param string $name Tab name
 * @param string $description Tab description
 * @return TableListTab Tab object
 */    
    public function __construct(Container $container, $repository, $name, $description = null)
    {
        if (!preg_match('/^[A-Za-z0-9_\-]+$/ui', $name)) {
            throw new FormException(__CLASS__.': tab name must contain only letters and numbers');
        }
        $this->container = $container;
        $this->repository = $repository;
        $this->cols = array();
        $this->name = $name;
        $this->description = ($description != null ? $description : $name);
        $this->orderColumn = 0;
        $this->orderDirection = 0;
        $this->itemsInPage = 20;
        $this->page = 0;
        $this->search = '';
        $this->actionErrors = array();
        
        return $this;
    }

/**
 * Add column to table list
 * 
 * @param string $type Type of column
 * @param string $name Name of entity field (or sql)
 * @param array $configuration Additional configuration
 * @return TableListTab Tab object
 */    
    public function addColumn($type, $name, $configuration = array())
    {
        if (preg_match('/^[a-zA-Z]+$/ui', $name)) {
            $name = 'item.'.$name;
        }
        if ($type == 'text') {
            $configuration = array_merge(array (
                'url' => '',
                'join' => '',
                'description' => $name,
                'sort' => true,
                'search' => false,
                'hidden' => false,
                'pattern' => '{{text}}',
            ), $configuration);
        } elseif ($type == 'number') {
            $configuration = array_merge(array (
                'url' => '',
                'join' => '',
                'description' => $name,
                'sort' => true,
                'search' => false,
                'hidden' => false,
                'decimals' => 0,
                'thousandSeparator' => ' ',
                'deciamlPoint' => ',',
            ), $configuration);
        } elseif ($type == 'datetime') {
            $configuration = array_merge(array (
                'url' => '',
                'join' => '',
                'description' => $name,
                'sort' => true,
                'search' => false,
                'hidden' => false,
                'format' => 'Y-m-d H:i',
                'timeZone' => null,
            ), $configuration);
            if ($configuration['timeZone'] != null) {
                if (!$configuration['timeZone'] instanceof \DateTimeZone) {
                    $configuration['timeZone'] = new \DateTimeZone($configuration['timeZone']);
                }
            }
        } elseif ($type == 'image') {
            $type = 'text';
            $configuration = array_merge(array (
                'url' => '',
                'join' => '',
                'description' => $name,
                'sort' => true,
                'search' => false,
                'hidden' => false,
                'pattern' => '<img src="{{text}}" />',
            ), $configuration);
        } elseif ($type == 'boolean') {
            $type = 'case';
            $configuration = array_merge(array (
                'url' => '',
                'join' => '',
                'description' => $name,
                'sort' => true,
                'search' => false,
                'hidden' => false,
                'choices' => array('false' => '<i class="fa fa-times"></i>', 'true' => '<i class="fa fa-check"></i>'),
            ), $configuration);
        } elseif ($type == 'case') {
            $configuration = array_merge(array (
                'url' => '',
                'join' => '',
                'description' => $name,
                'sort' => true,
                'search' => false,
                'hidden' => false,
                'choices' => array(),
            ), $configuration);
        } elseif ($type == 'array') {
            $configuration = array_merge(array (
                'url' => '',
                'join' => '',
                'description' => $name,
                'sort' => false,
                'search' => false,
                'hidden' => false,
                'callback' => null,
            ), $configuration);
        } else {
            throw new TableListException(__CLASS__.': unknown col type "'.$type.'"');
        }
        
        $this->cols[] = array(
            'type'  => $type,
            'name' => $name,
            'configuration' => $configuration,
        );
        
        return $this;
    }

/**
 * Add url button into action panel of tab 
 * 
 * @param string $name Action name
 * @param string $url Url
 * @param array $configuration Additional configuration
 * @return TableListTab Tab object
 */    
    public function addUrlAction($name, $url, $configuration = array())
    {
        if (!preg_match('/^[A-Za-z0-9_\-]+$/ui', $name)) {
            throw new FormException(__CLASS__.': action name must contain only letters and numbers');
        }
        if (strpos($url, '/') === false) {
            $url = $this->container->get('router')->generate($url);
        }
        $configuration = array_merge(array (
                'type' => 'url',
                'name' => $name,
                'description' => $name,
                'permission' => true,
                'url' => $url,
                'sendIds' => false,
                'multiply' => true,
                'confirmed' => false,
                'confirmedMessage' => 'Please confirm this operation',
                'confirmedTitle' => 'Warning',
                'confirmedOk' => 'OK',
                'confirmedCancel' => 'Cancel',
            ), $configuration);
        $this->actions[$name] = $configuration;
        return $this;
    }

/**
 * Add ajax action button into action panel of tab
 * 
 * @param string $name Action name
 * @param string $sql SQL
 * @param array $configuration Additional configuration
 * @return TableListTab Tab object
 */    
    public function addAjaxAction($name, $sql, $configuration = array())
    {
        if (!preg_match('/^[A-Za-z0-9_\-]+$/ui', $name)) {
            throw new FormException(__CLASS__.': action name must contain only letters and numbers');
        }
        $configuration = array_merge(array (
                'type' => 'ajax',
                'name' => $name,
                'description' => $name,
                'permission' => true,
                'multiply' => true,
                'confirmed' => false,
                'confirmedMessage' => 'Please confirm this operation',
                'confirmedTitle' => 'Warning',
                'confirmedOk' => 'OK',
                'confirmedCancel' => 'Cancel',
                'sql' => $sql,
                'callback' => null
            ), $configuration);
        $this->actions[$name] = $configuration;
        return $this;
    }

/**
 * Bind request
 * 
 * @param Request $request Symfony2 request
 * @return boolean True if the request has been processed ajax 
 */    
    public function bindRequest(Request $request = null)
    {
        if ($request == null) {
            $request = $this->container->get('request_stack')->getMasterRequest();
        }
        $this->orderColumn = $request->getSession()->get('tab_'.$request->get('_route').'_'.$this->name.'_orderColumn');
        $this->orderDirection = $request->getSession()->get('tab_'.$request->get('_route').'_'.$this->name.'_orderDirection');
        $this->page = $request->getSession()->get('tab_'.$request->get('_route').'_'.$this->name.'_page');
        $this->search = $request->getSession()->get('tab_'.$request->get('_route').'_'.$this->name.'_search');
        $this->itemsInPage = $request->getSession()->get('tab_'.$request->get('_route').'_'.$this->name.'_itemsInPage');
        if ($request->get('ordercolumn') !== null) {
            $this->orderColumn = intval($request->get('ordercolumn'));
            $this->orderDirection = ($request->get('orderdirection') == 'desc' ? 1 : 0);
            $request->getSession()->set('tab_'.$request->get('_route').'_'.$this->name.'_orderColumn', $this->orderColumn);
            $request->getSession()->set('tab_'.$request->get('_route').'_'.$this->name.'_orderDirection', $this->orderDirection);
        }
        if ($request->get('page') !== null) {
            $this->page = intval($request->get('page'));
            $request->getSession()->set('tab_'.$request->get('_route').'_'.$this->name.'_page', $this->page);
        }
        if ($request->get('search') !== null) {
            $this->search = $request->get('search');
            $request->getSession()->set('tab_'.$request->get('_route').'_'.$this->name.'_search', $this->search);
        }
        if ($request->get('itemsinpage') !== null) {
            $this->itemsInPage = intval($request->get('itemsinpage'));
            $request->getSession()->set('tab_'.$request->get('_route').'_'.$this->name.'_itemsInPage', $this->itemsInPage);
        }
        if ($request->get('action') != null) {
            if (isset($this->actions[$request->get('action')]) && ($this->actions[$request->get('action')]['type'] == 'ajax')) {
                $action = $this->actions[$request->get('action')];
                if ($action['permission'] == true) {
                    $sqls = explode(';', $this->actions[$request->get('action')]['sql']);
                    $ids = $request->get('id');
                    if (is_array($ids) && (count($ids) > 0)) {
                        if ($action['multiply'] == false) {
                            array_splice($ids, 1);
                        }
                        foreach ($sqls as $sql) {
                            if (strpos($sql, ':ids') !== false) {
                                $this->container->get('doctrine')->getManager()->createQuery($sql)->setParameter('ids', $ids)->execute();
                            } elseif (strpos($sql, ':id') !== false) {
                                foreach ($ids as $id) {
                                    $this->container->get('doctrine')->getManager()->createQuery($sql)->setParameter('id', $id)->execute();
                                }
                            }
                        }
                        if ($action['callback'] != null) {
                            $this->actionErrors = call_user_func($action['callback'], $ids);
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }

/**
 * Get tab description
 * 
 * @return array Tab description
 */    
    public function getDescription()
    {
        return array(
            'name' => $this->name,
            'description' => $this->description,
        );
    }
    
/**
 * Get view of tab
 * 
 * @return array View array
 */    
    public function getView()
    {
        $searchEnabled = false;
        $selectSql = 'item.id as id';
        $fromSql = $this->repository.' item';
        $whereSql = '';
        $orderSql = '';
        $parametersSql = array();
        foreach ($this->cols as $colkey => $colitem) {
            if (($colitem['configuration']['join'] != '') && (strpos($fromSql, $colitem['configuration']['join']) === false)) {
                $fromSql .= ' '.$colitem['configuration']['join'];
            }
            if (strpos(strtolower($colitem['name']), 'select ') !== false) {
                $colitem['name'] = '('.$colitem['name'].')';
            }
            $selectSql .= ','.$colitem['name'].' AS col'.$colkey;
            if (($colitem['configuration']['sort'] == true) && ($this->orderColumn == $colkey)) {
                if (strpos(strtolower($colitem['name']), 'select ') !== false) {
                    $orderSql = 'col'.$colkey.' '.($this->orderDirection == 0 ? 'ASC' : 'DESC');
                } else {
                    $orderSql = $colitem['name'].' '.($this->orderDirection == 0 ? 'ASC' : 'DESC');
                }
            }
            if ($colitem['configuration']['search'] == true) {
                $searchEnabled = true;
                if ($this->search != '') {
                    $parametersSql['search'] = '%'.$this->search.'%';
                    if ($whereSql != '') {
                        $whereSql .= ' OR ';
                    }
                    if (strpos(strtolower($colitem['name']), 'select ') !== false) {
                        $whereSql = 'col'.$colkey.' LIKE :search';
                    } else {
                        $whereSql = $colitem['name'].' LIKE :search';
                    }
                }
            }
        }
        if ($orderSql == '') {
            foreach ($this->cols as $colkey => $colitem) {
                if ($colitem['configuration']['sort'] == true) {
                    if (strpos(strtolower($colitem['name']), 'select ') !== false) {
                        $orderSql = 'col'.$colkey.' ASC';
                    } else {
                        $orderSql = $colitem['name'].' ASC';
                    }
                    $this->orderDirection = 0;
                    $this->orderColumn = $colkey;
                    break;
                }
            }
        }
        
        if (!in_array($this->itemsInPage, array(10, 20, 50, 100))) {
            $this->itemsInPage = 20;
        }

        $itemsCount = $this->container->get('doctrine')->getManager()->createQuery('SELECT count(item.id) FROM '.$fromSql.($whereSql != '' ? ' WHERE '.$whereSql : ' '))->setParameters($parametersSql)->getSingleScalarResult();

        $pageCount = ceil($itemsCount / $this->itemsInPage);
        if ($pageCount < 1) {
            $pageCount = 1;
        }
        if ($this->page < 0) {
            $this->page = 0;
        }
        if ($this->page >= $pageCount) {
            $this->page = $pageCount - 1;
        }
        $items = $this->container->get('doctrine')->getManager()->createQuery('SELECT '.$selectSql.' FROM '.$fromSql.($whereSql != '' ? ' WHERE '.$whereSql : ' ').' ORDER BY '.$orderSql)->setParameters($parametersSql)->setFirstResult($this->page * $this->itemsInPage)->setMaxResults($this->itemsInPage)->getResult();
        if (!is_array($items)) {
            $items = array();
        }
        foreach ($items as &$item) {
            foreach ($this->cols as $colkey => $colitem) {
                if (!isset($item['col'.$colkey])) {
                    $item['col'.$colkey] = '';
                }
                
                if ($colitem['type'] == 'text') {
                    if ($item['col'.$colkey] instanceof \Sergsxm\UIBundle\Classes\FileInterface) {
                        $item['col'.$colkey] = $item['col'.$colkey]->getContentFile();
                    }
                    $item['col'.$colkey] = str_replace('{{text}}', htmlentities($item['col'.$colkey]), $colitem['configuration']['pattern']);
                } elseif ($colitem['type'] == 'number') {
                    $item['col'.$colkey] = number_format($item['col'.$colkey], $colitem['configuration']['decimals'], $colitem['configuration']['deciamlPoint'], $colitem['configuration']['thousandSeparator']);
                } elseif ($colitem['type'] == 'datetime') {
                    if (($item['col'.$colkey] instanceof \DateTime) && ($colitem['configuration']['timeZone'] != null)) {
                        $item['col'.$colkey]->setTimezone($colitem['configuration']['timeZone']);
                    }
                    $item['col'.$colkey] = ($item['col'.$colkey] instanceof \DateTime ? $item['col'.$colkey]->format($colitem['configuration']['format']) : '');
                } elseif ($colitem['type'] == 'case') {
                    if ($item['col'.$colkey] === false) {
                        $item['col'.$colkey] = 'false';
                    }
                    if ($item['col'.$colkey] === true) {
                        $item['col'.$colkey] = 'true';
                    }
                    $item['col'.$colkey] = ((($item['col'.$colkey] !== null) && isset($colitem['configuration']['choices'][$item['col'.$colkey]])) ? $colitem['configuration']['choices'][$item['col'.$colkey]] : '');
                } elseif ($colitem['type'] == 'array') {
                    if ($colitem['configuration']['callback'] === null) {
                        $item['col'.$colkey] = (is_array($item['col'.$colkey]) ? count($item['col'.$colkey]) : '');
                    } else {
                        if (is_array($item['col'.$colkey])) {
                            $item['col'.$colkey] = call_user_func($colitem['configuration']['callback'], $item['col'.$colkey]);
                        } else {
                            $item['col'.$colkey] = '';
                        }
                    }
                }
                
                if ($colitem['configuration']['url'] != '') {
                    if (strpos($colitem['configuration']['url'], '/') === false) {
                        $actionUrl = $this->container->get('router')->generate($colitem['configuration']['url'], array('id' => $item['id']));
                    } else {
                        $actionUrl = str_replace('{{id}}', $item['id'], $colitem['configuration']['url']);
                    }
                    $item['col'.$colkey] = '<a href="'.$actionUrl.'">'.$item['col'.$colkey].'</a>';
                }
            }
        }
        unset($item);
        
        return array(
            'name' => $this->name,
            'description' => $this->description,
            'columns' => $this->cols,
            'orderColumn' => $this->orderColumn,
            'orderDirection' => $this->orderDirection,
            'itemsInPage' => $this->itemsInPage,
            'page' => $this->page,
            'pageCount' => $pageCount,
            'itemsCount' => $itemsCount,
            'items' => $items,
            'searchEnabled' => $searchEnabled,
            'search' => $this->search,
            'actions' => $this->actions,
            'actionErrors' => $this->actionErrors,
            'itemsInPageChoices' => array(10, 20, 50, 100),
        );
    }

/**
 * Render tab
 * 
 * @param string $template Template
 * @param array $parameters Additional parameters for template
 * @return string Content
 */    
    public function renderView($template, $parameters = array())
    {
        return $this->container->get('templating')->render($template, array_merge($parameters, $this->getView()));
    }

/**
 * Render tab
 * 
 * @param string $template Template
 * @param array $parameters Additional parameters for template
 * @return Response Symfony2 response
 */    
    public function render($template, $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($template, array_merge($parameters, $this->getView()), $response);
    }
    
}
