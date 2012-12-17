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

use RuntimeException;
use Fuu\Stdlib\ArrayPath;

class Cookie extends SessionAbstract implements AdapterInterface
{
    const DEFAULT_COOKIE_NAME = 'fukuki';
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'expire'   => '+7 days', 
            'path'     => '/', 
            'name'     => null,
            'domain'   => '', 
            'secure'   => false, 
            'httponly' => false
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        if ( ! $this->config['name']) {
            $this->config['name'] = self::DEFAULT_COOKIE_NAME;
        }
    }

    /* ______________________________________________________________________ */
    
    public function isStarted()
    {
        return true;
    }

    /* ______________________________________________________________________ */
    
    public function check($key)
    {
        return (isset($_COOKIE[$this->config['name']][$key]));
    }

    /* ______________________________________________________________________ */
    
    public function read($key = null, array $options = array())
    {
        $name = $this->config['name'];
        $data = isset($_COOKIE[$name]) ? (array) $_COOKIE[$name] : array();
        $data = $this->applyStrategies('read', $data, parent::READ_MODE);
        
        if ( ! $key) {
            return $data;
        }
        $default = isset($options['default']) ? $options['default'] : null;
        return isset($data[$key]) ? $data[$key] : $default;
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $value = null, array $options = array())
    {
        $data = (array) $this->read(null);
        if ($value === null) {
            unset($data[$key]);
        } else {
            $data[$key] = $value;
        }
        
        $data = $this->applyStrategies('write', $data, parent::WRITE_MODE);
        extract($this->config, EXTR_SKIP);
        
        $_COOKIE[$name] = $data;
        $result = setcookie($name, $data, strtotime($expire), $path, $domain, $secure, $httponly);
        if ( ! $result) {
            throw new RuntimeException(sprintf('There was an error setting `%s` cookie.', $name));
        }
        return $result;
    }

    /* ______________________________________________________________________ */
    
    public function delete($key, array $options = array())
    {
        return $this->write($key, null);
    }

    /* ______________________________________________________________________ */
    
    public function destroy(array $options = array())
    {
        extract($this->config, EXTR_SKIP);
        $_COOKIE[$name] = null;
        $result = setcookie($name, '', 1, $path, $domain, $secure, $httponly);
        if ( ! $result) {
            throw new RuntimeException(sprintf('There was an error setting `%s` cookie.', $name));
        }
        return $result;
    }
}