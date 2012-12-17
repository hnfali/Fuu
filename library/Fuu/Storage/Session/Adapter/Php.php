<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Session\Adapter;

use Fuu\Mvc\Exception\ConfigException;

class Php extends Memory
{
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'session.cookie_lifetime' => '0', 
            'session.cookie_httponly' => true
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        if ( ! isset($this->config['session.name'])) {
            if ( ! isset($this->config['namespace'])) {
                throw new ConfigException(__METHOD__ . ': Session namespace is not set.');
            }
            $this->config['session.name'] = $this->config['namespace'];
        }
        
        foreach ($this->config as $key => $value) {
            if (strpos($key, 'session.') === false) {
                continue;
            }
            if (ini_set($key, $value) === false) {
                throw new ConfigException('Could not initialize the session.');
            }
        }
    }

    /* ______________________________________________________________________ */
    
    public function isStarted()
    {
        return (boolean) session_id();
    }

    /* ______________________________________________________________________ */
    
    protected function start() {
        if ( ! $this->isStarted()) {
            if ( ! isset($_SESSION)) {
                session_cache_limiter('nocache');
            }
            
            session_start();
            if ( ! isset($_SESSION[$this->config['namespace']])) {
                $_SESSION[$this->config['namespace']] = array();
            }
            $this->config['initial_data'] = $_SESSION[$this->config['namespace']];
        }
        return parent::start();
    }
    
    /* ______________________________________________________________________ */
    
    public function write($key, $value = null, array $options = array())
    {
        $return = parent::write($key, $value, $options);
        $_SESSION[$this->config['namespace']] = $this->storage->toArray();
        return $return;
    }

    /* ______________________________________________________________________ */
    
    public function delete($key, array $options = array())
    {
        $return = parent::delete($key, $options);
        $_SESSION[$this->config['namespace']] = $this->storage->toArray();
        return $return;
    }

    /* ______________________________________________________________________ */
    
    public function destroy(array $options = array())
    {
        unset($_SESSION[$this->config['namespace']]);
        $options['destroy_internal'] = false;
        return parent::destroy($options);
    }

    /* ______________________________________________________________________ */
    
    protected function prefix($key)
    {
        return $key;
    }
}