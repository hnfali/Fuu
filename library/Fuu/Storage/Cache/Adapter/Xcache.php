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

class Xcache extends CacheAbstract implements AdapterInterface
{
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array('prefix' => '', 'expiry' => '+1 hour');
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $data, $expiry = null)
    {
        $expiry = ($expiry) ?: $this->config['expiry'];
        $data = $this->applyStrategies('write', $data, parent::WRITE_MODE);
        return xcache_set($key, $data, strtotime($expiry) - time());
    }

    /* ______________________________________________________________________ */
    
    public function read($key)
    {
        $data = xcache_get($key);
        return $this->applyStrategies('read', $data, parent::READ_MODE);
    }

    /* ______________________________________________________________________ */
    
    public function delete($key)
    {
        return xcache_unset($key);
    }

    /* ______________________________________________________________________ */
    
    public function decrement($key, $offset = 1)
    {
        return xcache_dec($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function increment($key, $offset = 1)
    {
        return xcache_inc($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function flush()
    {
        $admin = (ini_get('xcache.admin.enable_auth') === 'On');
        if ($admin && ( ! isset($this->config['username']) OR ! isset($this->config['password']))) {
            return false;
        }

        $credentials = array();
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $credentials['username'] = $_SERVER['PHP_AUTH_USER'];
            $_SERVER['PHP_AUTH_USER'] = $this->config['username'];
        }
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $credentials['password'] = $_SERVER['PHP_AUTH_PW'];
            $_SERVER['PHP_AUTH_PW'] = $this->config['password'];
        }

        for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++) {
            if (xcache_clear_cache(XC_TYPE_VAR, $i) === false) {
                return false;
            }
        }

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $_SERVER['PHP_AUTH_USER'] = ($credentials['username'] !== null) ? $credentials['username'] : null;
        }
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $_SERVER['PHP_AUTH_PW'] = ($credentials['password'] !== null) ? $credentials['password'] : null;
        }
        return true;
    }

    /* ______________________________________________________________________ */
    
    public static function isEnabled()
    {
        return extension_loaded('xcache');
    }
}