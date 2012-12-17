<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_View
 */

namespace Fuu\View\Helper;

use Fuu\Storage\Session\Factory;
use Fuu\Storage\Strategy\Json;

class FlashMessenger
{
    protected $storage;
    protected $config = array();
    protected $data = array();

    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        if( ! isset($config['name'])) {
            $config['name'] = 'fuufm';
        }
        
        $defaults = array(
            'expire'   => '+7 days', 
            'path'     => '/', 
            'domain'   => '', 
            'secure'   => false, 
            'httponly' => false
        );
        $this->config = $config + $defaults;
        $this->storage = Factory::factory('Cookie', $this->config);
        $this->storage->addStrategy(new Json);
        
        // copy current flash message to `$this->data`, 
        // and then flush the cookie, thus it won't available on next request
        $this->data = $this->storage->read(null);
        $this->storage->destroy();
    }

    /* ______________________________________________________________________ */
    
    public function get($key = null)
    {
        if ( ! $key) {
            return $this->data;
        }

        if (is_array($key)) {
            switch (count($key)) {
                case 0:
                    return array();
                    break;

                case 1:
                    return $this->getMulti($key[0]);
                    break;

                case 2:
                    return $this->getMulti($key[0], $key[1]);
                    break;

                case 3:
                    return $this->getMulti($key[0], $key[1], $key[2]);
                    break;

                default:
                    return call_user_func_array(array($this, 'getMulti'), $key);
                    break;
            }
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        return null;
    }

    /* ______________________________________________________________________ */
    
    public function getMulti()
    {
        $keys = func_get_args();
        $data = array();
        
        foreach ($keys as $key) {
            if (isset($this->data[$key])) {
                $data[$key] = $this->data[$key];
            }
        }
        
        return $data;
    }

    /* ______________________________________________________________________ */
    
    public function set($key, $value = null)
    {
        return $this->storage->write($key, $value = null);
    }

    /* ______________________________________________________________________ */
    
    public function delete($key)
    {
        return $this->storage->delete($key);
    }

    /* ______________________________________________________________________ */
    
    public function flush()
    {
        return $this->storage->destroy();
    }
}