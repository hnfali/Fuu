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

class Json extends StrategyAbstract
{
    /* ______________________________________________________________________ */
    
    public function write($data)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException(__METHOD__ . ': Could not serialize resources.');
        }
        return json_encode($data);
    }
    
    /* ______________________________________________________________________ */
    /**
     * @todo Filter $data to be UTF-8 compliant (`json_decode` function 
     *       only works with UTF-8 encoded data)
     */
    public function read($data)
    {
        if ($data === null) {
            return;
        }
        return json_decode($data);
    }
}