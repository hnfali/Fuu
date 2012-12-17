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

use InvalidArgumentException;

class Serializer extends StrategyAbstract
{
    /* ______________________________________________________________________ */
    
    public function write($data)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException(__METHOD__ . ': Could not serialize resources.');
        }
        return @serialize($data);
    }
    
    /* ______________________________________________________________________ */
    
    public function read($data)
    {
        return @unserialize($data);
    }
}