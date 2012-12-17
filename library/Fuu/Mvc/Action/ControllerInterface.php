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

use Fuu\Mvc\ResourceManager;
use Fuu\View\Renderer\RendererInterface;

interface ControllerInterface
{
    /* ______________________________________________________________________ */
    
    public function __construct(ResourceManager $resources);

    /* ______________________________________________________________________ */
    /**
     * Get application config
     * @return \Fuu\Mvc\ApplicationConfigInterface
     */
    public function getConfig();

    /* ______________________________________________________________________ */
    /**
     * Get event manager
     * @return \Fuu\Event\Manager
     */
    public function getEvents();

    /* ______________________________________________________________________ */
    /**
     * Get request object
     * @return \Fuu\Mvc\Action\RequestActionInterface
     */
    public function getRequest();

    /* ______________________________________________________________________ */
    /**
     * Get response object
     * @return \Fuu\Http\ResponseServerInterface
     */
    public function getResponse();

    /* ______________________________________________________________________ */
    /**
     * Validate if a method is an action method and call the method.
     * 
     * @param string $action
     * @return \Fuu\Http\ResponseServerInterface
     * @throws DispatchException 
     */
    public function invokeAction($action);
    
    /* ______________________________________________________________________ */
    /**
     * Set the view renderer
     * @param \Fuu\View\Renderer\RendererInterface $renderer 
     */
    public function setRenderer(RendererInterface $renderer);
    
    /* ______________________________________________________________________ */
    /**
     * Get the view renderer
     * @return \Fuu\View\Renderer\RendererInterface
     * @throws DomainException
     * @throws ViewException 
     */
    public function getRenderer();
    
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
    public function render($template, array $data = array(), $type = 'file');
    
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
    public function redirect($url, $code = 302, $exit = true);

    /* ______________________________________________________________________ */
    /**
     * Terminate the flow process and outputs an error message
     * 
     * @staticvar boolean $isError
     * @param int $code
     * @param array $data
     * @return type 
     */
    public function error($code, array $data = array());

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
    public function headerStatus($code, $exit = true);
}