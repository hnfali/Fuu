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

use PDO as PhpPdo;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Mvc\Exception\ConfigException;

/**
 * PDO Adapter 
 * @see http://php.net/manual/en/class.pdo.php
 */
class Pdo implements DbAdapterInterface
{
    const CONFIG_NAME = 'pdo';
    
    /**
     * PDO Persisten Object
     * @var \PDO
     */
    protected $pdo;
    
    /**
     * Configuration
     * @var array 
     */
    protected $config = array();

    /* ______________________________________________________________________ */
    /**
     * Initialize PDO Adapter
     * 
     * @param array|ApplicationConfigInterface $config
     * @throws ConfigException 
     */
    public function __construct($config = array())
    {
        $defaults = array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'user'      => 'root',
            'password'  => '',
            'port'      => null,
            'options'   => array(),
            'attribs'   => array(
                PhpPdo::ATTR_ERRMODE => PhpPdo::ERRMODE_EXCEPTION,
                PhpPdo::ATTR_DEFAULT_FETCH_MODE => PhpPdo::FETCH_ASSOC
            )
        );
        
        if ($config instanceof ApplicationConfigInterface) {
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
        if ( ! isset($this->config['dsn'])) {
            $cfg  = $this->config;
            $port = ( ! empty($cfg['port'])) ? (';port=' . $cfg['port']) : '';
            $dsn  = $cfg['driver'] . ':host=' . $cfg['host'] . $port . ';dbname=' . $cfg['dbname'];
            $this->config['dsn'] = $dsn;
        }
    }

    /* ______________________________________________________________________ */
    
    public function connect()
    {
        if ( ! $this->isConnected()) {
            $this->pdo = new PhpPdo(
                $this->config['dsn'], 
                $this->config['username'], 
                $this->config['password'], 
                $this->config['options'] 
            );
            
            // set PDO attribs
            foreach ( (array) $this->config['attribs'] as $key => $value) {
                $this->pdo->setAttribute($key, $value);
            }
        }
        return $this->isConnected();
    }

    /* ______________________________________________________________________ */
    
    public function isConnected()
    {
        return ($this->pdo instanceof PhpPdo);
    }

    /* ______________________________________________________________________ */
    
    public function disconnect()
    {
        unset($this->pdo);
        return true;
    }

    /* ______________________________________________________________________ */
    /**
     * Get PDO object
     * @return \PDO
     */
    public function getResource()
    {
        $this->connect();
        return $this->pdo;
    }
}