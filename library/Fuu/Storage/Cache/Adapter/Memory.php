<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Cache\Adapter;

class Memory extends CacheAbstract implements AdapterInterface
{
    protected $cache = array();

    /* ______________________________________________________________________ */
    
    public function write($key, $data, $expiry = null)
    {
        $filtered = $this->applyStrategies('write', is_array($key) ? $key : $data, parent::WRITE_MODE);
        if (is_array($filtered)) {
            foreach ($filtered as $k => &$v) {
                $this->cache[$k] = $v;
            }
            return true;
        }
        return (boolean) ($this->cache[$key] = $filtered);
    }

    /* ______________________________________________________________________ */
    
    public function read($key)
    {
        if (is_array($key)) {
            $data = array();
            foreach ($key as $k) {
                if (isset($this->cache[$k])) {
                    $data[$k] = $this->cache[$k];
                }
            }
        } else {
            $data = isset($this->cache[$key]) ? $this->cache[$key] : null;
        }
        return $this->applyStrategies('read', $data, parent::READ_MODE);
    }

    /* ______________________________________________________________________ */
    
    public function delete($key)
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
            return true;
        } else {
            return false;
        }
    }

    /* ______________________________________________________________________ */
    
    public function decrement($key, $offset = 1)
    {
        if (isset($this->cache[$key]) && is_numeric($this->cache[$key])) {
            return $this->cache[$key] -= $offset;
        } else {
            return false;
        }
    }

    /* ______________________________________________________________________ */
    
    public function increment($key, $offset = 1)
    {
        if (isset($this->cache[$key]) && is_numeric($this->cache[$key])) {
            return $this->cache[$key] += $offset;
        } else {
            return false;
        }
    }

    /* ______________________________________________________________________ */
    
    public function flush()
    {
        $this->cache = array();
        return true;
    }

    /* ______________________________________________________________________ */
    
    public static function isEnabled()
    {
        return true;
    }
}