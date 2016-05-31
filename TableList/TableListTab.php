<?php

/**
 * Table list tab class
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\TableList;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sergsxm\UIBundle\TableList\TableListException;
use Sergsxm\UIBundle\TableList\TableListQuery;

class TableListTab
{
    
    private $container;
    private $columns = array();
    private $name;
    private $configuration;
    private $orderColumn = null;
    private $orderDirection = 0;
    private $defaultOrderColumn = 0;
    private $defaultOrderDirection = 0;
    private $itemsInPage = 20;
    private $page = 0;
    private $search = '';
    private $actions = array();
    private $actionErrors = array();
    private $itemsInPageValues = array(10, 20, 50, 100);
    private $query;

/**
 * Table list tab constructor
 * 
 * @param ContainerInterface $container Symfony2 container
 * @param string $repository Doctrine main repository for list
 * @param string $name Tab name
 * @param array|string $configuration Tab configuration (if string - description)
 * @return TableListTab Tab object
 */    
    public function __construct(ContainerInterface $container, $repository, $name, $configuration = null)
    {
        if (!preg_match('/^[A-Za-z0-9_\-]+$/ui', $name)) {
            throw new TableListException(__CLASS__.': tab name must contain only letters and numbers');
        }
        $this->container = $container;
        $this->name = $name;
        $this->setDefaults();
        if (is_string($configuration)) {
            $this->configuration['description'] = $configuration;
        } elseif (is_array($configuration)) {
            $this->configuration = array_merge($this->configuration, $configuration);
        }
        $this->query = new TableListQuery($this->container->get('doctrine')->getManager(), $repository, $this->configuration['whereType']);
        
        return $this;
    }

/**
 * Set configuration to default values
 */    
    private function setDefaults()
    {
        $this->configuration = array(
            'description' => $this->name,
            'whereType' => TableListQuery::WT_OR,
            'countEnabled' => false,
        );
    }
    
/**
 * Add where condition
 * 
 * @param string $dql Name of entity field (or sql)
 * @param string $condition Condition (like "!= :test")
 * @param mixed $parameter Condition parameter
 * @return \Sergsxm\UIBundle\TableList\TableListTab
 */    
    public function addWhereCondition($dql, $condition, $parameter = null)
    {
        $i = $this->query->addColumn($dql);
        $this->query->where($i, $condition, $parameter);
        return $this;
    }

/**
 * Open where group
 * 
 * @param int $whereType Where type
 * @return \Sergsxm\UIBundle\TableList\TableListTab
 */    
    public function openWhereGroup($whereType)
    {
        $this->query->openWhereGroup($whereType);
        return $this;
    }

/**
 * Close where group
 * 
 * @return \Sergsxm\UIBundle\TableList\TableListTab
 */    
    public function closeWhereGroup()
    {
        $this->query->closeWhereGroup();
        return $this;
    }

/**
 * Set group by statment
 * 
 * @param string $dql Name of entity field
 */
    public function groupBy($dql)
    {
        $i = $this->query->addColumn($dql);
        $this->query->group($i);
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
        if (!class_exists($type) || !is_subclass_of($type, '\Sergsxm\UIBundle\TableList\TableListColumn')) {
            $types = $this->container->get('sergsxm.ui')->getTableListColumns();
            if (isset($types[$type])) {
                $type = $types[$type]; 
            } else {
                throw new TableListException(__CLASS__.': column type "'.$type.'" is not exist');
            }
        }
        $this->columns[] = new $type($this->container, $this->query, $name, $configuration);
        if (isset($configuration['defaultOrderDirection'])) {
            $this->defaultOrderColumn = count($this->columns) - 1;
            $this->defaultOrderDirection = $configuration['defaultOrderDirection'];
        }
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
            throw new TableListException(__CLASS__.': action name must contain only letters and numbers');
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
            throw new TableListException(__CLASS__.': action name must contain only letters and numbers');
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
        if (($request->get('action') != null) && ($request->get('csrf_token') == $this->getCsrfToken())) {
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
        $itemsCount = null;
        if ($this->configuration['countEnabled'] == true) {
            $subquery = clone $this->query;
            $itemsCount = $subquery->getCount();
        }
        return array(
            'name' => $this->name,
            'description' => $this->configuration['description'],
            'configuration' => $this->configuration,
            'itemsCount' => $itemsCount,
        );
    }
    
/**
 * Get view of tab
 * 
 * @return array View array
 */    
    public function getView()
    {
        if (($this->orderColumn === null) || !isset($this->columns[$this->orderColumn])) {
            $this->orderColumn = $this->defaultOrderColumn;
            $this->orderDirection = $this->defaultOrderDirection;
        }
        foreach ($this->columns as $columnKey=>$column) {
            $column->modifyQuery(($this->orderColumn == $columnKey ? $this->orderDirection : null), ($this->search != '' ? '%'.$this->search.'%' : ''));
        }
        
        if (!in_array($this->itemsInPage, $this->itemsInPageValues)) {
            $this->itemsInPage = 20;
        }
        $itemsCount = $this->query->getCount();
        
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

        $items = $this->query->getResult($this->page * $this->itemsInPage, $this->itemsInPage);
        foreach ($this->columns as $column) {
            $column->postQuery($items);
        }
        
        $result = array();
        foreach ($items as $item) {
            $resultItem = array(
                'id' => $item['id']
            );
            foreach ($this->columns as $columnKey=>$column) {
                $resultItem['col'.$columnKey] = $column->convertValue($item);
            }
            $result[] = $resultItem;
        }
        
        $columns = array();
        $searchEnabled = false;
        foreach ($this->columns as $column) {
            $columnView = $column->getView();
            if ($columnView['searchEnabled'] == true) {
                $searchEnabled = true;
            }
            $columns[] = $columnView;
        }
        
        
        return array(
            'name' => $this->name,
            'description' => $this->configuration['description'],
            'configuration' => $this->configuration,
            'columns' => $columns,
            'orderColumn' => $this->orderColumn,
            'orderDirection' => $this->orderDirection,
            'itemsInPage' => $this->itemsInPage,
            'page' => $this->page,
            'pageCount' => $pageCount,
            'itemsCount' => $itemsCount,
            'items' => $result,
            'searchEnabled' => $searchEnabled,
            'search' => $this->search,
            'actions' => $this->actions,
            'actionErrors' => $this->actionErrors,
            'itemsInPageChoices' => $this->itemsInPageValues,
            'csrfToken' => $this->generateCsrfToken(),
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

/**
 * Get CSFR token for validation
 * 
 * @return string Token
 */    
    private function getCsrfToken()
    {
        return $this->container->get('request_stack')->getMasterRequest()->getSession()->get('sergsxm_table_csrf');
    }

/**
 * Generate CSFR token
 * 
 * @return string Token
 */    
    private function generateCsrfToken()
    {
        if ($this->container->get('request_stack')->getMasterRequest()->getSession()->has('sergsxm_table_csrf')) {
            return $this->getCsrfToken();
        }
        $randomValue = random_bytes(32);
        $token = rtrim(strtr(base64_encode($randomValue), '/+', '-_'), '=');
        $this->container->get('request_stack')->getMasterRequest()->getSession()->set('sergsxm_table_csrf', $token);
        return $token;
    }
    
}
