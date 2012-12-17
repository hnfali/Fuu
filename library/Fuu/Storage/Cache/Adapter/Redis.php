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

use Redis as RedisCore;

class Redis extends CacheAbstract implements AdapterInterface
{
    protected $connection;
    
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'host' => '127.0.0.1:6379',
            'expiry' => '+1 hour',
            'persistent' => false
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        if ( ! $this->connection) {
            $this->connection = new RedisCore();
        }
        list($ip, $port) = explode(':', $this->config['host']);
        $method = $this->config['persistent'] ? 'pconnect' : 'connect';
        $this->connection->{$method}($ip, $port);
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $data, $expiry = null)
    {
        $expiry = ($expiry) ?: $this->config['expiry'];
        $data = $this->applyStrategies('write', $data, parent::WRITE_MODE);
        
        if ($result = $this->connection->set($key, $data)) {
            if ($expiry) {
                return $this->ttl($key, $expiry);
            }
            return $result;
        }
    }

    /* ______________________________________________________________________ */
    
    public function read($key)
    {
        if (is_array($key)) {
            $data = $this->connection->getMultiple($key);
        } else {
            $data = $this->connection->get($key);
        }
        return $this->applyStrategies('read', $data, parent::READ_MODE);
    }

    /* ______________________________________________________________________ */
    
    public function delete($key)
    {
        return (boolean) $this->connection->delete($key);
    }

    /* ______________________________________________________________________ */
    
    public function decrement($key, $offset = 1)
    {
        return $this->connection->decr($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function increment($key, $offset = 1)
    {
        return $this->connection->incr($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function flush()
    {
        return $this->connection->flushdb();
    }

    /* ______________________________________________________________________ */
    
    public static function isEnabled()
    {
        return extension_loaded('redis');
    }
    
    /* ______________________________________________________________________ */
    
    protected function ttl($key, $expiry)
    {
        return $this->connection->expireAt($key, is_int($expiry) ? $expiry : strtotime($expiry));
    }
}