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

use Memcached;

class Memcache extends CacheAbstract implements AdapterInterface
{
    const CONN_DEFAULT_PORT = 11211;
    
    protected $connection;
    
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'host' => '127.0.0.1',
            'expiry' => '+1 hour'
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        $this->connection = $this->connection ?: new Memcached();
        $servers = array();

        if (isset($this->config['servers'])) {
            $this->connection->addServers($this->config['servers']);
            return;
        }
        $this->connection->addServers($this->formatHost($this->config['host']));
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $data, $expiry = null)
    {
        $expiry = ($expiry) ?: $this->config['expiry'];
        $expires = is_int($expiry) ? $expiry : strtotime($expiry);
        $data = $this->applyStrategies('write', $data, parent::WRITE_MODE);
        return $this->connection->set($key, $data, $expires);
    }

    /* ______________________________________________________________________ */
    
    public function read($key)
    {
        $data = null;
        if (is_array($key)) {
            $data = $this->connection->getMulti($key);
        } else {
            if (($data = $this->connection->get($key)) === false) {
                if ($this->connection->getResultCode() === Memcached::RES_NOTFOUND) {
                    $data = null;
                }
            }
        }
        return $this->applyStrategies('read', $data, parent::READ_MODE);
    }

    /* ______________________________________________________________________ */
    
    public function delete($key)
    {
        return $this->connection->delete($key);
    }

    /* ______________________________________________________________________ */
    
    public function decrement($key, $offset = 1)
    {
        return $this->connection->decrement($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function increment($key, $offset = 1)
    {
        return $this->connection->increment($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function flush()
    {
        return $this->connection->flush();
    }

    /* ______________________________________________________________________ */
    
    public static function isEnabled()
    {
        return extension_loaded('memcached');
    }
    
    /* ______________________________________________________________________ */
    
    protected function formatHost($host)
    {
        $fromString = function($host) {
            if (strpos($host, ':')) {
                list($host, $port) = explode(':', $host);
                return array($host, intval($port));
            }
            return array($host, Memcache::CONN_DEFAULT_PORT);
        };

        if (is_string($host)) {
            return array($fromString($host));
        }
        
        $servers = array();
        while (list($server, $weight) = each($host)) {
            if (is_string($weight)) {
                $servers[] = $fromString($weight);
                continue;
            }
            $server = $fromString($server);
            $server[] = $weight;
            $servers[] = $server;
        }
        return $servers;
    }
}