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
use RuntimeException;

class Config extends ArrayObject implements ConfigInterface
{
    protected $readOnlyKeys = array();
    protected $data = array();

    /* ______________________________________________________________________ */
    
    public static function fromString($string)
    {
        $array = array();
        parse_str($string, $array);
        return new self($array);
    }

    /* ______________________________________________________________________ */
    
    public static function fromJson($json)
    {
        $array = json_decode($json);
        return new self(is_array($array) ? $array : array());
    }

    /* ______________________________________________________________________ */
    
    public static function fromIni($ini)
    {
        set_error_handler(function($error, $message = '', $file = '', $line = 0) {
            throw new RuntimeException(sprintf('Error reading INI string: %s', $message), $error);
        }, E_WARNING);
        $array = parse_ini_string($ini, true);
        restore_error_handler();
        return new self($array);
    }
    
    /* ______________________________________________________________________ */
    
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /* ______________________________________________________________________ */
    
    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /* ______________________________________________________________________ */
    
    public function __set($key, $value)
    {
        if (in_array($key, $this->readOnlyKeys)) {
            throw new RuntimeException(sprintf('`%s` is read only thus can not be modified.', $key));
        }
        return $this->data[$key] = $value;
    }

    /* ______________________________________________________________________ */
    
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /* ______________________________________________________________________ */
    
    public function __unset($key)
    {
        if (in_array($key, $this->readOnlyKeys)) {
            throw new RuntimeException(sprintf('`%s` is read only thus can not be modified.', $key));
        }
        unset($this->data[$key]);
    }

    /* ______________________________________________________________________ */
    
    public function toArray()
    {
        return $this->data;
    }

    /* ______________________________________________________________________ */
    
    public function setDefaults(array $data)
    {
        $this->data += $data;
    }

    /* ______________________________________________________________________ */
    
    public function markAsReadOnly($key)
    {
        $key = (array) $key;
        $this->readOnlyKeys = array_merge($this->readOnlyKeys, $key);
    }

    /* ______________________________________________________________________ */
    
    public function count()
    {
        return count($this->data);
    }

    /* ______________________________________________________________________ */
    
    public function serialize()
    {
        return @ serialize($this->data);
    }

    /* ______________________________________________________________________ */
    
    public function unserialize($data)
    {
        $this->data = (array) @ unserialize($data);
    }

    /* ______________________________________________________________________ */
    
    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /* ______________________________________________________________________ */
    
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->data[$key];
        }
    }

    /* ______________________________________________________________________ */
    
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /* ______________________________________________________________________ */
    
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}