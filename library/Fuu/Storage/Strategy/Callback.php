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

class Callback extends StrategyAbstract
{
    /* ______________________________________________________________________ */
    
    protected function init()
    {
        $defaults = array(
            'write_callback' => null,
            'read_callback'  => null
        );
        $this->config += $defaults;
    }

    /* ______________________________________________________________________ */
    
    public function write($data)
    {
        $func = $this->config['write_callback'];
        return is_callable($func) ? $func($data) : $data;
    }

    /* ______________________________________________________________________ */
    
    public function read($data)
    {
        $func = $this->config['read_callback'];
        return is_callable($func) ? $func($data) : $data;
    }
}