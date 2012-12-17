<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Router;

use Exception;
use Fuu\Stdlib\PriorityQueue;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Mvc\Action\Request;

class Routes
{
    const DEFAULT_PRIORITY = 100;

    protected $queue;
    protected $routers = array();

    /* ______________________________________________________________________ */
    
    public function __construct($routers = array())
    {
        if ($routers instanceof ApplicationConfigInterface) {
            $routers = (array) $routers['routes'];
        }
        $this->routers = (array) $routers;
        $this->queue = new PriorityQueue;
    }

    /* ______________________________________________________________________ */
    
    public function add(RouterInterface $route, $priority = self::DEFAULT_PRIORITY)
    {
        if ( ! isset($this->routers[$priority])) {
            $this->routers[$priority] = $route;
        } else {
            do {
                $priority += 1;
            } while(isset($this->routers[$priority]));
            $this->routers[$priority] = $route;
        }
    }

    /* ______________________________________________________________________ */
    
    public function set(RouterInterface $route, $priority = self::DEFAULT_PRIORITY)
    {
        $this->routers[$priority] = $route;
    }

    /* ______________________________________________________________________ */
    
    public function setRequest(Request $request)
    {
        $this->routers[PHP_INT_MAX] = new DefaultRouter;
        foreach ($this->routers as $priority => $router) {
            if ($router instanceof RouterInterface) {
                $this->queue->insert($router, $priority);
            }
        }

        do {
            $result = $this->queue->extract()->setRequest($request);
        } while( ! $result);
    }
}