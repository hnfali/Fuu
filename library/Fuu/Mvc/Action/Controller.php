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

use DomainException;
use RuntimeException;
use Fuu\Http\Response;
use Fuu\Stdlib\Inflector;
use Fuu\Mvc\ResourceManager;
use Fuu\Mvc\Exception\ViewException;
use Fuu\Mvc\Exception\DispatchException;
use Fuu\Event\Event;
use Fuu\Event\Manager as EventManager;
use Fuu\View\Renderer\RendererInterface;
use Fuu\View\Renderer\Php as PhpRenderer;

class Controller implements ControllerInterface
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
     * View Renderer
     * @var \Fuu\View\Renderer\RendererInterface
     */
    protected $renderer;
    
    /**
     * Resource Manager
     * @var \Fuu\Mvc\ResourceManager
     */
    protected $resources;
    
    /**
     * Annotation Manager
     * @var \Fuu\Mvc\Action\Annotation\Manager
     */
    protected $annotation;
    
    /**
     * The Application
     * @var \Fuu\Mvc\ApplicationInterface
     */
    protected $application;

    /* ______________________________________________________________________ */
    
    public function __construct(ResourceManager $resources)
    {
        $this->resources = $resources;
        $this->events = new EventManager;

        $this->config = $resources->getConfig();
        $this->request = $resources->getRequest();
        $this->response = $resources->getResponse();
        $this->application = $resources->getApplication();
        $this->annotation = $resources->getAnnotationManager();

        $this->init();
    }

    /* ______________________________________________________________________ */
    
    protected function init() {}
    
    /* ______________________________________________________________________ */
    /**
     * To be executed before Response object is going to be send 
     * back to the Application::dispatch() method. 
     */
    public function __destruct()
    {
        $this->getEvents()->createTrigger(__FUNCTION__, $this);
    }
    
    /* ______________________________________________________________________ */
    /**
     * Get current controller
     * @return string 
     */
    public function __toString()
    {
        return (string) get_called_class();
    }
    
    /* ______________________________________________________________________ */
    /**
     * Get application config
     * @return \Fuu\Mvc\ApplicationConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /* ______________________________________________________________________ */
    /**
     * Get request object
     * @return \Fuu\Mvc\Action\RequestActionInterface 
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /* ______________________________________________________________________ */
    /**
     * Get response object
     * @return \Fuu\Http\ResponseServerInterface
     */
    public function getResponse()
    {
        return $this->response;
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
     * Validate if a method is an action method and call the method.
     * 
     * @param string $action
     * @return \Fuu\Http\ResponseServerInterface
     * @throws DispatchException 
     */
    public function invokeAction($action)
    {
        if ($this->annotation->validateAction($this, $action)) {
            $this->annotation->filterAction($this, $action);
            
            $this->getEvents()->attach(__FUNCTION__, function(Event $e) {
                $e->setResponse(
                    $e->getTarget()->{$e->getParam('action')}(
                        $e->getTarget()->getRequest(),
                        $e->getTarget()->getResponse()
                    )
                );
                return $e;
            });

            $responses = $this->getEvents()->createTrigger(__FUNCTION__, $this, compact('action'));
            $response = $responses->last()->getResponse();

            switch (true) {
                case ($response instanceof Response):
                    return $response;
                    break;

                case is_array($response):
                    return $this->response->appendBody(
                        $this->render($this->locateDefaultTemplate(), $response)
                    );
                    break;

                case is_string($response):
                    $this->response->appendBody($response);
                    return $this->response;
                    break;

                default:
                    // try to convert any type to string
                    try {
                        return $this->response->appendBody( (string) $response);
                    } catch (Exception $e) {
                        return $this->response;
                    }
                    break;
            }
        }
        throw new DispatchException('Invalid action: ' . $action);
    }
    
    /* ______________________________________________________________________ */
    /**
     * Set the view renderer
     * @param \Fuu\View\Renderer\RendererInterface $renderer 
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }
    
    /* ______________________________________________________________________ */
    /**
     * Get the view renderer
     * @return \Fuu\View\Renderer\RendererInterface
     * @throws DomainException
     * @throws ViewException 
     */
    public function getRenderer()
    {
        if ( ! $this->renderer) {
            $prefix = 'Fuu\\View\\Renderer\\';
            $class = $this->config['view_renderer'];
            
            if ( ! class_exists($class)) {
                // renderer not found?
                // probably its not a qualified class name
                $class = $prefix . Inflector::camelize($class, true);
            }
            
            if (class_exists($class)) {
                if (in_array($prefix . 'RendererInterface', class_implements($class))) {
                    $this->renderer = new $class($this->config);
                    return $this->renderer;
                } else {
                    throw new DomainException('View renderer must implements RendererInterface.');
                }
            }
            throw new ViewException('View renderer not found: ' . $class);
        }
        return $this->renderer;
    }
    
    /* ______________________________________________________________________ */
    /**
     * Renders a view
     * 
     * The first args can be any of path to the template file, template string, 
     * array, or object depends on the view renderer to use
     * 
     * @param string $template
     * @param array $data
     * @param string $type
     * @return string
     */
    public function render($template, array $data = array(), $type = 'file')
    {
        $this->getEvents()->attach(__FUNCTION__, function(Event $e) {
            $e->setResponse($e->getTarget()->getRenderer()->render(
                $e->getParam('template'), 
                $e->getParam('data'), 
                $e->getParam('type')
            ));
            return $e;
        });
        
        $params = compact('template', 'data', 'type');
        $responses = $this->getEvents()->createTrigger(__FUNCTION__, $this, $params);
        return (string) $responses->last()->getResponse();
    }
    
    /* ______________________________________________________________________ */
    /**
     * Redirects page to a given uri
     * 
     * If given $uri is just a path info, then the base url will be 
     * prepended to the $uri.
     * 
     * @param string $uri
     * @param int $code
     * @param bool $exit
     * @throws RuntimeException 
     */
    public function redirect($uri, $code = 302, $exit = true)
    {
        if ( ! $this->response->headerSent(false)) {
            if ( ! preg_match('/^https?:\/\//i', $uri)) {
                $uri = $this->request->info('base_url') . '/' . ltrim($uri, '/');
            }
            $this->response->setRedirect($uri, $code);
            $this->response->sendHeaders();
            if ($exit === true) {
                exit;
            }
        } else {
            throw new RuntimeException('Could not redirect page, headers already sent.');
        }
    }

    /* ______________________________________________________________________ */
    /**
     * Terminate the flow process and outputs an error message
     * 
     * @staticvar boolean $isError
     * @param int $code
     * @param array $data
     * @return type 
     */
    public function error($code, array $data = array())
    {
        static $isError = false;
        if ($isError == true) {
            return;
        }

        $isError = true;
        if ( ! $this->response->validateResponseCode($code)) {
            $code = 500; // internal server error
        }
        $title = $this->response->getStatusMessage($code);
        
        // try locating error template file
        $output = '';
        try {
            $renderer = new PhpRenderer;
            $path = $this->config['app_path'] . '/' . 
                $this->config['templates'] . '/errors/' . 
                $code . '.' . $renderer->getFileExtension();

            $output = $this->response->setBody($renderer->render($path, $data + compact('title')));
        } catch (Exception $e) {
            $output = '<h1>' . $code . ' - ' . $title . '</h1>';
        }
        
        echo $output;
        exit(0);
    }

    /* ______________________________________________________________________ */
    /**
     * Sends a header status
     * 
     * This is useful when we only need to send the header status, 
     * usually when the client requested via xmlhttprequest
     * 
     * @param type $code
     * @param type $exit 
     */
    public function headerStatus($code, $exit = true)
    {
        if ( ! $this->response->headerSent(false)) {
            $this->response->setResponseCode($code);
            $this->response->sendHeaders();
        }

        if ($exit) {
            exit(0);
        }
    }

    /* ______________________________________________________________________ */
    
    protected function locateDefaultTemplate()
    {
        $path = $this->config['app_path'] . '/' . 
                $this->config['module_dir'] . '/' . 
                $this->request->getModule() . '/' . 
                $this->config['view_dir'] . '/' . 
                $this->config['template_dir'] . '/' . 
                $this->config['module_dir'] . '/' . 
                $this->request->getController() . '/' . 
                $this->request->getAction() . '.';
        
        if ($media = strtolower($this->request->info('media'))) {
            $path .= $media . '.';
        }
        
        $path .= ltrim($this->getRenderer()->getFileExtension(), '.');
        return $path;
    }
}