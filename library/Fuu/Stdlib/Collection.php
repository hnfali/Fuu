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

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Serializable;
use ArrayIterator;
use InvalidArgumentException;

class Collection implements ArrayAccess, Countable, IteratorAggregate, Serializable
{
    protected $data = array();

    /* ______________________________________________________________________ */

    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /* ______________________________________________________________________ */

    public function __toString()
    {
        return $this->dump();
    }

    /* ______________________________________________________________________ */

    public function toArray()
    {
        return $this->data;
    }

    /* ______________________________________________________________________ */

    public function clear()
    {
        $this->data = array();
    }

    /* ______________________________________________________________________ */

    public function contains($element)
    {
        if (in_array($element, $this->data) OR array_search($element, $this->data)) {
            return true;
        }
        return false;
    }

    /* ______________________________________________________________________ */

    public function count()
    {
        return count($this->data);
    }

    /* ______________________________________________________________________ */

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /* ______________________________________________________________________ */

    public function serialize()
    {
        return serialize($this->data);
    }

    /* ______________________________________________________________________ */

    public function unserialize($string)
    {
        $this->data = unserialize($string);
    }

    /* ______________________________________________________________________ */

    public function dump()
    {
        return var_dump($this->data);
    }

    /* ______________________________________________________________________ */

    public function chunk($size, $collect = true)
    {
        $data = array_chunk($this->data, $size);
        if ($collect) {
            $class = get_class($this);
            $chunks = new $class(array_chunk($this->data, $size));
            return $chunks->map(function($chunk) {
                $data = $chunks($chunk);
            });
        }
        return $data;
    }

    /* ______________________________________________________________________ */

    public function each($callback)
    {
        if( ! is_callable($callback)) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires arg #1 to be callable.');
        }
        foreach($this->data as $key => $val) {
            $callback($val, $key);
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function end() {
        return end($this->data);
    }

    /* ______________________________________________________________________ */

    public function filter($filter) {
        if ( ! is_callable($filter)) {
            throw new InvalidArgumentException(__METHOD__ . ' requires arg #1 to be callable.');
        }
        $this->data = array_filter($this->data, $filter);
        return $this;
    }

    /* ______________________________________________________________________ */

    public function find($filter, $collect = true)
    {
        $data = array_filter($this->data, $filter);
        if ($collect) {
            $class = get_class($this);
            $data = new $class($data);
        }
        return $data;
    }

    /* ______________________________________________________________________ */

    public function first($filter = null)
    {
        if ( ! $filter) {
            return $this->rewind();
        }

        foreach ($this as $item) {
            if ($filter($item)) {
                return $item;
            }
        }
    }

    /* ______________________________________________________________________ */

    public function get($index)
    {
        return $this->offsetGet($index);
    }

    /* ______________________________________________________________________ */

    public function intersect(array $data)
    {
        $this->data = array_intersect($this->data, $data);
        return $this;
    }

    /* ______________________________________________________________________ */

    public function join($glue = null)
    {
        return implode($glue, $this->data);
    }

    /* ______________________________________________________________________ */

    public function map($filter, $collect = true)
    {
        $data = array_map($filter, $this->data);
        if ($collect) {
            $class = get_class($this);
            return new $class($data);
        }
        return $data;
    }

    /* ______________________________________________________________________ */

    public function merge($data)
    {
        return $this->data = array_merge($this->data, $data);
    }

    /* ______________________________________________________________________ */

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /* ______________________________________________________________________ */

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /* ______________________________________________________________________ */

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            return $this->data[] = $value;
        }
        return $this->data[$offset] = $value;
    }

    /* ______________________________________________________________________ */

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /* ______________________________________________________________________ */

    public function pop()
    {
        return ($this->count()) ? array_pop($this->data) : null;
    }

    /* ______________________________________________________________________ */

    public function push($element)
    {
        $this->data[] = $element;
        return $this;
    }

    /* ______________________________________________________________________ */

    public function reduce($filter, $init = null)
    {
        return array_reduce($this->data, $filter, $init);
    }

    /* ______________________________________________________________________ */

    public function reverse()
    {
        return $this->data = array_reverse($this->data);
    }

    /* ______________________________________________________________________ */

    public function set($index, $element)
    {
        return $this->offsetSet($index, $element);
    }

    /* ______________________________________________________________________ */

    public function shift()
    {
        return ($this->count()) ? array_shift($this->data) : null;
    }

    /* ______________________________________________________________________ */

    public function shuffle()
    {
        return shuffle($this->data);
    }

    /* ______________________________________________________________________ */

    public function slice($offset, $length = null)
    {
        $this->data = array_slice($this->data, $offset, $length, true);
        return $this;
    }

    /* ______________________________________________________________________ */

    public function sort($filter = 'asort', $flag = null)
    {
        if ($filter && is_callable($filter)) {
            $flag = ($flag) ?: SORT_REGULAR;
            $filter($this->data, $flag);
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function unique()
    {
        return $this->data = array_unique($this->data);
    }

    /* ______________________________________________________________________ */

    public function unshift($element)
    {
        return array_unshift($this->data, $element);
    }
}