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

interface DispatcherInterface
{
    /* ______________________________________________________________________ */
    
    public function __construct(ResourceManager $resources);

    /* ______________________________________________________________________ */
    /**
     * Get event manager
     * @return \Fuu\Event\Manager
     */
    public function getEvents();

    /* ______________________________________________________________________ */
    /**
     * Dispatch requests
     * 
     * This method will invoke the router collection to iterate each routers 
     * in the collection, and set the request object to the matching router object
     * 
     * @return \Fuu\Http\ResponseServerInterface 
     */
    public function dispatch();
}