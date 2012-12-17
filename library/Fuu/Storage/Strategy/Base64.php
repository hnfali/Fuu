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

class Base64 extends StrategyAbstract
{
    /* ______________________________________________________________________ */
    
    public function write($data)
    {
        return base64_encode($data);
    }
    
    /* ______________________________________________________________________ */
    
    public function read($data)
    {
        if ($data === null) {
            return;
        }
        return base64_decode($data);
    }
}