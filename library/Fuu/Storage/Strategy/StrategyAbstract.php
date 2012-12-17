<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Strategy;

abstract class StrategyAbstract implements StrategyInterface
{
    protected $config = array();
    
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $this->config = $config;
        $this->init();
    }
    
    /* ______________________________________________________________________ */
    
    protected function init()
    {
        // do something
    }
}