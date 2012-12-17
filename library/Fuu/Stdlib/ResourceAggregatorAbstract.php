<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Stdlib
 */

namespace Fuu\Stdlib;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use OutOfBoundsException;

abstract class ResourceAggregatorAbstract implements Countable, IteratorAggregate
{
    protected $adapters = array();
    
    /* ______________________________________________________________________ */
    
    abstract public function add($id, $adapter, array $config = array());
    
    /* ______________________________________________________________________ */
    
    public function get($id, $exception = true)
    {
        if (isset($this->adapters[$id])) {
            return $this->adapters[$id];
        }
        
        if ($exception) {
            throw new OutOfBoundsException(__METHOD__ . ': ID not found: ' . $id);
        }
        
        return null;
    }
    
    /* ______________________________________________________________________ */
    
    public function remove($id)
    {
        if (isset($this->adapters[$id])) {
            unset($this->adapters[$id]);
            return true;
        }
        return false;
    }
    
    /* ______________________________________________________________________ */
    
    public function reset()
    {
        $this->adapters = array();
    }
    
    /* ______________________________________________________________________ */
    
    public function flush()
    {
        foreach ($this->adapters as $key => $value) {
            $this->remove($key);
        }
    }
    
    /* ______________________________________________________________________ */
    
    public function count()
    {
        return count($this->adapters);
    }
    
    /* ______________________________________________________________________ */
    
    public function getIterator()
    {
        return new ArrayIterator($this->adapters);
    }
}