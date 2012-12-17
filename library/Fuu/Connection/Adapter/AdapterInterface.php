<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Connection
 */

namespace Fuu\Connection\Adapter;

interface AdapterInterface
{
    /* ______________________________________________________________________ */
    /**
     * Connect to a specific resource
     * return bool
     */
    public function connect();

    /* ______________________________________________________________________ */
    /**
     * Check if an adapter is connected to a specific resource
     * @return bool
     */
    public function isConnected();

    /* ______________________________________________________________________ */
    /**
     * Disconnect a connection
     * @return bool
     */
    public function disconnect();

    /* ______________________________________________________________________ */
    /**
     * @return object|resource 
     */
    public function getResource();
}