<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc;

use Fuu\Event\Event;
use Fuu\Event\Manager as EventManager;

require_once dirname(__DIR__) . '/Stdlib/ConfigInterface.php';
require_once dirname(__DIR__) . '/Stdlib/Config.php';
require_once 'ApplicationConfigInterface.php';
require_once 'ApplicationInterface.php';
require_once 'LoaderInterface.php';
require_once 'Config.php';
require_once 'Loader.php';

class Application implements ApplicationInterface
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
     * Default Class Loader
     * @var \Fuu\Mvc\LoaderInterface
     */
    protected $loader;
    
    /**
     * Resource Manager
     * @var \Fuu\Mvc\ResourceManager
     */
    protected $resources;
    
    /* ______________________________________________________________________ */
    
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        $this->autoloadLibrary();

        $this->events = new EventManager;
        $this->resources = new ResourceManager;
        
        // setup resources
        $this->resources->setApplication($this);
        $this->resources->setLoader($this->loader);
        $this->resources->setConfig($this->config);
        
        // bootstrapping
        if (isset($this->config['bootstrap'])) {
            $this->resources->getBootstrapper()->bootstrap($this->config['bootstrap']);
        }
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
     * Dispatch requests, and send back response
     * @return \Fuu\Http\ResponseServerInterface
     */
    public function dispatch()
    {
        return $this->resources->getDispatcher()->dispatch();
    }
    
    /* ______________________________________________________________________ */
    /**
     * Outputs the dispatched requests 
     */
    public function output()
    {
        echo (string) $this->dispatch();
    }
    
    /* ______________________________________________________________________ */
    
    protected function autoloadLibrary()
    {
        $this->loader = new Loader($this->config->autoload);
        
        $libpath = dirname(dirname(__DIR__));
        $this->loader->registerNamespace('Fuu', $libpath . '/Fuu');
        $this->loader->registerNamespace('Doctrine', $libpath . '/DoctrineORM/Doctrine');
        $this->loader->registerNamespace(
            $this->config->app_namespace, 
            $this->config->app_path
        );
        
        $this->loader->register();
    }
}