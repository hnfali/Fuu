<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Action;

use RuntimeException;
use Fuu\Http\Response;
use Fuu\Event\Event;
use Fuu\Event\Manager as EventManager;
use Fuu\Mvc\Exception\DispatchException;
use Fuu\Mvc\ResourceManager;
use Fuu\Stdlib\Inflector;

class Dispatcher implements DispatcherInterface
{
    /**
     * Application Configuration
     * @var \Fuu\Mvc\ApplicationConfigInterface
     */
    protected $config;
    
    /**
     * Event Manager
     * @var \Fuu\Event\Manager
     */
    protected $events;
    
    /**
     * Router Collection
     * @var \Fuu\Mvc\Router\Routes 
     */
    protected $routes;
    
    /**
     * Request Object
     * @var \Fuu\Mvc\Action\RequestActionInterface
     */
    protected $request;
    
    /**
     * Response Object
     * @var \Fuu\Http\ResponseServerInterface
     */
    protected $response;
    
    /**
     * Resource Manager
     * @var \Fuu\Mvc\ResourceManager
     */
    protected $resources;
    
    /**
     * The Controller
     * @var \Fuu\Mvc\Action\ControllerInterface 
     */
    protected $controller;
    
    /**
     * Application Object
     * @var \Fuu\Mvc\ApplicationInterface
     */
    protected $application;

    /* ______________________________________________________________________ */
    
    public function __construct(ResourceManager $resources)
    {
        $this->events = new EventManager;
        $this->resources = $resources;

        $this->config = $resources->getConfig();
        $this->request = $resources->getRequest();
        $this->response = $resources->getResponse();
        $this->application = $resources->getApplication();

        $this->response->setProtocol($this->request->info('protocol'));
    }

    /* ______________________________________________________________________ */
    /**
     * Get event manager
     * @return \Fuu\Event\Manager
     */
    public function getEvents()
    {
        return $this->events;
    }

    /* ______________________________________________________________________ */
    /**
     * Dispatch requests
     * 
     * This method will invoke the router collection to iterate each routers 
     * in the collection, and set the request object to the matching router object
     * 
     * @return \Fuu\Http\ResponseServerInterface 
     */
    public function dispatch()
    {
        $this->resources->getRoutes()->setRequest($this->request);
        $response = $this->run();
        
        // destroy controller object, thus the `__desctruct()` method is called.
        unset($this->controller);
        return $response;
    }
    
    /* ______________________________________________________________________ */
    
    protected function run()
    {
        $prefix = $this->config['app_namespace'] . '\\' . $this->config['module_dir'];
        $module = $prefix . '\\' . $this->request->getModule();
        
        // looking for bootstrap class, and bootstrapping if exists.
        $bootstrap = $module . '\\Config\\Bootstrap';
        $this->resources->getBootstrapper()->bootstrap($bootstrap);

        $class = $module . '\\' . $this->request->getController();
        if (class_exists($class)) {
            $this->resources->setController(new $class($this->resources));
            return $this->resources->getController()->invokeAction($this->request->getAction());
        }

        throw new DispatchException('Controller not found: ' . $class);
    }
}