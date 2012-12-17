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

interface ApplicationInterface
{
    /* ______________________________________________________________________ */
    
    public function __construct(array $config);
    /* ______________________________________________________________________ */
    /**
     * Get event manager
     * @return \Fuu\Event\Manager
     */
    public function getEvents();

    /* ______________________________________________________________________ */
    /**
     * Dispatch requests, and send back response
     * @return \Fuu\Http\ResponseServerInterface
     */
    public function dispatch();
    
    /* ______________________________________________________________________ */
    /**
     * Outputs the dispatched requests 
     */
    public function output();
}