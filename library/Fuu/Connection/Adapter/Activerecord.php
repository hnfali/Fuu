<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Connection
 */

namespace Fuu\Connection\Adapter;

use Activerecord;
use Fuu\Stdlib\ArrayUtils;
use Fuu\Mvc\Exception\ConfigException;
use Fuu\Mvc\ApplicationConfigInterface;

/**
 * Activerecord Adapter
 * @see https://github.com/kla/php-activerecord/
 */
class Activerecord implements DbAdapterInterface
{
    const CONFIG_NAME = 'activerecord';
    const ENV_DEVELOPMENT = 'development';
    const ENV_PRODUCTION = 'production';

    protected $cfg;
    protected $config = array();

    /* ______________________________________________________________________ */
    
    public function __construct($config = array())
    {
        $defaults = array(
            'autoloader'  => null,
            'environment' => self::ENV_PRODUCTION,
            'model_path'  => null,
            'connections' => array()
        );
        
        if ($config instanceof ApplicationConfigInterface) {
            $defaults['model_path'] = $config->app_path . '/' . $config->model_dir;
            $defaults['environment'] = $config->environment;
            $config = (array) $config->{self::CONFIG_NAME};
        }
        
        if ( ! is_array($config)) {
            throw new ConfigException(__METHOD__ . ' expects arg #1 to be an array.');
        }
        
        $this->config = $config + $defaults;
        $this->init();
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        if ( ! $this->config['autoloader']) {
            throw new ConfigException('Activerecord autoloader is not set.');
        }
        
        if ( ! $this->config['model_path']) {
            throw new ConfigException('Activerecord model path is not set.');
        }
        
        if ( ! ArrayUtils::isHashTable( (array) $this->config['connections'])) {
            $this->config['connections'] = array(
                $this->config['environment'] => (array) $this->config['connections']
            );
        }
        
        // load autoloader
        require_once $this->config['autoloader'];
    }

    /* ______________________________________________________________________ */
    
    public function connect()
    {
        if ( ! $this->isConnected()) {
            $this->cfg = ActiveRecord\Config::instance();
            $this->cfg->set_model_directory($this->config['model_path']);
            $this->cfg->set_connections($this->config['connections']);
            $this->cfg->set_default_connection($this->config['environment']);
        }
        return $this->isConnected();
    }

    /* ______________________________________________________________________ */
    
    public function isConnected()
    {
        return ($this->cfg instanceof Activerecord\Config);
    }

    /* ______________________________________________________________________ */
    
    public function disconnect()
    {
        unset($this->cfg);
        return true;
    }

    /* ______________________________________________________________________ */
    /**
     * Get instance of Activerecord\Config
     * @return \Activerecord\Config 
     */
    public function getResource()
    {
        $this->connect();
        return $this->cfg;
    }
}