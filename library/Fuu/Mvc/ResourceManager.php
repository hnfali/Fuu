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

use Closure;
use DomainException;
use RuntimeException;
use InvalidArgumentException;
use Fuu\Mvc\ApplicationInterface;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Mvc\LoaderInterface;
use Fuu\Mvc\Bootstrapper;
use Fuu\Mvc\Action\Annotation\Manager;
use Fuu\Mvc\Action\DispatcherInterface;
use Fuu\Mvc\Action\ControllerInterface;
use Fuu\Mvc\Action\RequestActionInterface;
use Fuu\Mvc\Router\Routes;
use Fuu\Http\ResponseServerInterface;

class ResourceManager
{
    const THROW_NOT_FOUND_EXCEPTION = 1;

    protected $resources = array();
    protected $definitions = array();
    
    /* ______________________________________________________________________ */
    
    public function __construct()
    {
        $resources =& $this;
        $this->definitions = array(
            'application'       => array(
                'instance'      => array('Fuu\\Mvc\\Application'),
                'type'          => 'Fuu\\Mvc\\ApplicationInterface',
                'reinit'        => false,
                'params'        => array()
            ),
            'config'            => array(
                'instance'      => array('Fuu\\Mvc\\Config'),
                'type'          => 'Fuu\\Mvc\\ApplicationConfigInterface',
                'reinit'        => false,
                'params'        => array()
            ),
            'loader'            => array(
                'instance'      => array('Fuu\\Mvc\\DefaultLoader'),
                'type'          => 'Fuu\\Mvc\\LoaderInterface',
                'reinit'        => false,
                'params'        => array()
            ),
            'bootstrapper'      => array(
                'instance'      => array('Fuu\\Mvc\\Bootstrapper'),
                'type'          => 'Fuu\\Mvc\\Bootstrapper',
                'reinit'        => false,
                'params'        => array($this)
            ),
            'annotationmanager' => array(
                'instance'      => array('Fuu\\Mvc\\Action\\Annotation\\Manager'),
                'type'          => 'Fuu\\Mvc\\Action\\Annotation\\Manager',
                'reinit'        => false,
                'params'        => array()
            ),
            'dispatcher'        => array(
                'instance'      => array('Fuu\\Mvc\\Action\\Dispatcher'),
                'type'          => 'Fuu\\Mvc\\Action\\DispatcherInterface',
                'reinit'        => false,
                'params'        => array($this)
            ),
            'controller'        => array(
                'instance'      => null,
                'type'          => 'Fuu\\Mvc\\Action\\ControllerInterface',
                'reinit'        => false,
                'params'        => array($this)
            ),
            'request'           => array(
                'instance'      => array('Fuu\\Mvc\\Action\\Request'),
                'type'          => 'Fuu\\Mvc\\Action\\RequestActionInterface',
                'reinit'        => false,
                'params'        => array(
                                        function() use(&$resources) {
                                            return $resources->getConfig();
                                        }
                                    )
            ),
            'routes'            => array(
                'instance'      => array('Fuu\\Mvc\\Router\\Routes'),
                'type'          => 'Fuu\\Mvc\\Router\\Routes',
                'reinit'        => false,
                'params'        => array()
            ),
            'response'          => array(
                'instance'      => array('Fuu\\Http\\Response'),
                'type'          => 'Fuu\\Http\\ResponseServerInterface',
                'reinit'        => false,
                'params'        => array(
                                        function() use(&$resources) {
                                            return $resources->getRequest()->info('protocol');
                                        }
                                    )
            ),
        );
    }
    
    /* ______________________________________________________________________ */
    /**
     * Check if a specific resource is already set
     * @param string $id
     * @return bool 
     */
    public function check($id)
    {
        return isset($this->resources[$id]) ? true : false;
    }
    
    /* ______________________________________________________________________ */
    
    public function __call($method, $args)
    {
        $id = strtolower(substr($method, 3));
        $prefix = strtolower(substr($method, 0, 3));
        $config = isset($this->definitions[$id]) ? $this->definitions[$id] : null;
        
        switch (true) {
            case $prefix === 'get':
                if (isset($this->resources[$id])) {
                    return $this->resources[$id];
                }
                
                if ($config) {
                    // Usually when an instance is created via Factory
                    if (count($config['instance']) == 2) {
                        return $this->resources[$id] = call_user_func_array(
                            $config['instance'], 
                            $this->filterParams($config['params'])
                        );
                    // First element of the array will be instantiated
                    } else {
                        return $this->resources[$id] = $this->newInstance(
                            $config['instance'][0], 
                            $config['params']
                        );
                    }
                }
                break;

            case $prefix === 'set':
                // first argument is empty, U mad bro?
                if ( ! isset($args[0])) {
                    throw new InvalidArgumentException(sprintf(
                        '%s::%s: No arguments given.', __CLASS__, $method
                    ));
                }
                
                // If the resource is already set and `reinit` option is set to false
                if (isset($this->resources[$id]) && $config && ! $config['reinit']) {
                    throw new RuntimeException(sprintf(
                        '%s::%s: Resource is already initialized.', __CLASS__, $method
                    ));
                }
                
                // validate object type
                if ($this->validateInstance($id, $args[0])) {
                    $this->resources[$id] = $args[0];
                }
                break;

            default:
                break;
        }
    }

    /* ______________________________________________________________________ */
    
    protected function validateInstance($id, $obj)
    {
        if (isset($this->definitions[$id])) {
            $def = $this->definitions[$id];
            if ( ! is_a($obj, $def['type'])) {
                throw new DomainException(sprintf(
                    'Resource `%s` expects an instance of `%s`', $id, $defs['type']
                ));
            }
        }
        return true;
    }
    
    /* ______________________________________________________________________ */
    
    protected function newInstance($class, array $params)
    {
        if ( ! class_exists($class)) {
            throw new RuntimeException('Resource class not found: ' . $class);
        }
        
        // execute closure object inside params
        $params = $this->filterParams($params);
        
        switch (count($params)) {
            case 0:
                $obj = new $class;
                break;

            case 1:
                $obj = new $class($params[0]);
                break;

            case 2:
                $obj = new $class($params[0], $params[1]);
                break;

            case 3:
                $obj = new $class($params[0], $params[1], $params[2]);
                break;

            default:
                $r = new ReflectionClass($class);
                $obj = $r->newInstanceArgs($params);
                break;
        }
        return $obj;
    }
    
    /* ______________________________________________________________________ */
    
    protected function filterParams(array $params)
    {
        return array_map(function($e) { return ($e instanceof Closure) ? $e() : $e; }, $params);
    }

    /* ______________________________________________________________________ */
    /**
     * Get the application instance
     * @return \Fuu\Mvc\ApplicationInterface 
     * @throws RuntimeException
     */
    public function getApplication() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set an application instance
     * @param $app \Fuu\Mvc\ApplicationInterface 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setApplication(ApplicationInterface $app) { return $this->__call(__FUNCTION__, array($app)); }

    /* ______________________________________________________________________ */
    /**
     * Get the application config
     * @return \Fuu\Mvc\ApplicationConfigInterface 
     * @throws RuntimeException
     */
    public function getConfig() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set application config
     * @param $config \Fuu\Mvc\ApplicationConfigInterface 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setConfig(ApplicationConfigInterface $config) { return $this->__call(__FUNCTION__, array($config)); }

    /* ______________________________________________________________________ */
    /**
     * Get loader
     * @return \Fuu\Mvc\LoaderInterface 
     * @throws RuntimeException
     */
    public function getLoader() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set loader
     * @param $loader \Fuu\Mvc\LoaderInterface 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setLoader(LoaderInterface $loader) { return $this->__call(__FUNCTION__, array($loader)); }

    /* ______________________________________________________________________ */
    /**
     * Get bootstrapper
     * @return \Fuu\Mvc\Bootstrapper 
     * @throws RuntimeException
     */
    public function getBootstrapper() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set bootstrapper
     * @param $bootstrapper \Fuu\Mvc\Bootstrapper 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setBootstrapper(Bootstrapper $bootstrapper) { return $this->__call(__FUNCTION__, array($bootstrapper)); }

    /* ______________________________________________________________________ */
    /**
     * Get annotation manager
     * @return \Fuu\Mvc\Action\Annotation\Manager 
     * @throws RuntimeException
     */
    public function getAnnotationManager() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set annotation manager
     * @param $manager \Fuu\Mvc\Action\Annotation\Manager 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setAnnotationManager(Manager $manager) { return $this->__call(__FUNCTION__, array($manager)); }

    /* ______________________________________________________________________ */
    /**
     * Get dispatcher
     * @return \Fuu\Mvc\Action\DispatcherInterface 
     * @throws RuntimeException
     */
    public function getDispatcher() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set dispatcher
     * @param $dispatcher \Fuu\Mvc\Action\DispatcherInterface 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setDispatcher(DispatcherInterface $dispatcher) { return $this->__call(__FUNCTION__, array($dispatcher)); }

    /* ______________________________________________________________________ */
    /**
     * Get controller
     * @return \Fuu\Mvc\Action\ControllerInterface 
     * @throws RuntimeException
     */
    public function getController() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set controller
     * @param $controller \Fuu\Mvc\Action\ControllerInterface 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setController(ControllerInterface $controller) { return $this->__call(__FUNCTION__, array($controller)); }

    /* ______________________________________________________________________ */
    /**
     * Get request object
     * @return \Fuu\Mvc\Action\RequestActionInterface 
     * @throws RuntimeException
     */
    public function getRequest() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set request object
     * @param $request \Fuu\Mvc\Action\RequestActionInterface 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setRequest(RequestActionInterface $request) { return $this->__call(__FUNCTION__, array($request)); }

    /* ______________________________________________________________________ */
    /**
     * Get router collection
     * @return \Fuu\Mvc\Router\Routes 
     * @throws RuntimeException
     */
    public function getRoutes() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set router collection
     * @param $routes \Fuu\Mvc\Router\Routes 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setRoutes(Routes $routes) { return $this->__call(__FUNCTION__, array($routes)); }

    /* ______________________________________________________________________ */
    /**
     * Get response object
     * @return \Fuu\Http\ResponseServerInterface 
     * @throws RuntimeException
     */
    public function getResponse() { return $this->__call(__FUNCTION__, array()); }

    /* ______________________________________________________________________ */
    /**
     * Set response object
     * @param $response \Fuu\Http\ResponseServerInterface 
     * @throws DomainException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function setResponse(ResponseServerInterface $response) { return $this->__call(__FUNCTION__, array($response)); }
}