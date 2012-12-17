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

class Apc extends CacheAbstract implements AdapterInterface
{
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'prefix' => '',
            'expiry' => '+1 hour'
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $data, $expiry = null)
    {
        $expiry = ($expiry) ?: $this->config['expiry'];
        $cachetime = (is_int($expiry) ? $expiry : strtotime($expiry)) - time();

        $data = $this->applyStrategies('write', $data, parent::WRITE_MODE);
        return apc_store($key, $data, $cachetime);
    }

    /* ______________________________________________________________________ */
    
    public function read($key)
    {
        $data = apc_fetch($key);
        return $this->applyStrategies('read', $data, parent::READ_MODE);
    }

    /* ______________________________________________________________________ */
    
    public function delete($key)
    {
        return apc_delete($key);
    }

    /* ______________________________________________________________________ */
    
    public function decrement($key, $offset = 1)
    {
        return apc_dec($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function increment($key, $offset = 1)
    {
        return apc_inc($key, $offset);
    }

    /* ______________________________________________________________________ */
    
    public function flush()
    {
        return apc_clear_cache('user');
    }

    /* ______________________________________________________________________ */
    
    public static function isEnabled()
    {
        $loaded = extension_loaded('apc');
        $isCli = (php_sapi_name() === 'cli');
        $enabled = ( ! $isCli && ini_get('apc.enabled')) OR ($isCli && ini_get('apc.enable_cli'));
        return ($loaded && $enabled);
    }
}