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

use ArrayObject;
use OutOfBoundsException;
use InvalidArgumentException;

class ArrayPath extends ArrayObject 
{
    protected $delimiter = '/';

    /* ______________________________________________________________________ */
    
    public function setDelimiter($delimiter)
    {
        if ( ! $delimiter) {
            throw new InvalidArgumentException(__METHOD__ . ': Invalid delimiter given.');
        }
        $this->delimiter = $delimiter;
    }

    /* ______________________________________________________________________ */
    
    public function toArray()
    {
        return (array) $this;
    }

    /* ______________________________________________________________________ */
    
    public function get($path, $default = null)
    {
        return $this->path($path, $default);
    }

    /* ______________________________________________________________________ */
    
    public function path($path, $default = null)
    {
        try {
            $pointer = $this->walk($path, false);
            return $pointer;
        } catch (OutOfBoundsException $e) {
            return $default;
        }
    }

    /* ______________________________________________________________________ */
    
    public function set($path, $value = null)
    {
        $pointer =& $this->walk($path, true);
        $pointer = $value;
    }

    /* ______________________________________________________________________ */
    
    public function add($path, $value = null)
    {
        $pointer =& $this->walk($path, true);
        $pointer[] = $value;
    }

    /* ______________________________________________________________________ */
    
    protected function &walk($path, $silent = true)
    {
        $parts = $this->pathToArray($path);
        $pointer =& $this;
        foreach ($parts as $part) {
            if (is_array($pointer) OR ($pointer instanceof ArrayPath)) {
                if ( ! isset($pointer[$part])) {
                    if ($silent === true) {
                        $pointer[$part] = array();
                    } else {
                        throw new OutOfBoundsException("Invalid path specified: {$path}");
                    }
                }
                $pointer =& $pointer[$part];
            } else {
                throw new OutOfBoundsException("Invalid path specified: {$path}");
            }
        }
        
        return $pointer;
    }

    /* ______________________________________________________________________ */
    
    protected function pathToArray($path)
    {
        return array_filter(explode($this->delimiter, $path), function($e) {
            return (trim($e) == '') ? false : true;
        });
    }
}