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

use RuntimeException;
use Fuu\Mvc\Exception\ConfigException;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Stdlib\Inflector;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;

/**
 * Doctrine ORM Adapter
 * 
 * Enables Doctrine ORM as the default model handler on  
 * fuu framework application 
 * 
 * @see https://github.com/doctrine/doctrine2/
 */
class DoctrineOrm implements DbAdapterInterface
{
    const CONFIG_NAME = 'doctrine_orm';
    const ENV_DEVELOPMENT = 'development';
    const ENV_PRODUCTION = 'production';

    /**
     * Doctrine Entity Manager
     * @var Doctrine\ORM\EntityManager 
     */
    protected $em;
    
    /**
     * Configuration
     * @var array 
     */
    protected $config = array();

    /* ______________________________________________________________________ */
    /**
     * Initialize DoctrineOrm Adapter
     * 
     * Accepts either array or ApplicationConfigInterface as its first arguments.
     * The following config keys are required:
     * 
     * > driver          : The database driver to be used. e.g. pdo_mysql, pdo_sqlite3
     * > entity_path     : Path to the directory where the entities exists
     * > proxy_path      : Path for Doctrine to generate proxies
     * > proxy_namespace : The proxy class namespace
     * 
     * Other optional options:
     * 
     * > environment     : Default is set to `production`
     * > cache_driver    : Default is set to `Array`
     * > autoload_type   : Default is set to `dir`
     * > base_path       : Default is empty (automaticly set to 
     *                     Doctrine path which is shipped with 
     *                     this framework)
     * 
     * @param array|ApplicationConfigInterface $config
     * @throws ConfigException 
     */
    public function __construct($config = array())
    {
        $defaults = array(
            'environment'   => self::ENV_PRODUCTION,
            'cache_driver'  => 'Doctrine\\Common\\Cache\\Array',
            'autoload_type' => 'dir',
            'base_path'     => ''
        );
        
        if ($config instanceof ApplicationConfigInterface) {
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
        // validate required config keys
        foreach (array('entity_path', 'proxy_path', 'proxy_namespace', 'driver') as $key) {
            if ( ! isset($this->config[$key])) {
                throw new ConfigException("Doctrine adapter requires `{$val}` to be set in configuration.");
            }
        }
        
        // setup autoloader
        // there are 3 different ways of loading Doctrine depends on how we get it, 
        // loaded as PEAR extension, cloned from git repo, and download from its website.
        switch (true) {
            case (strtolower($this->config['autoload_type']) == 'pear'):
                require_once 'Doctrine/ORM/Tools/Setup.php';
                Setup::registerAutoloadPEAR();
                break;

            case (strtolower($this->config['autoload_type']) == 'git'):
                $path = rtrim($this->config['base_path'], DIRECTORY_SEPARATOR);
                $setup = $path . '/lib/vendor/doctrine-common/lib/Doctrine/ORM/Tools/Setup.php';
                if ( ! is_file($setup)) {
                    throw new ConfigException('DoctrineOrm Adapter: Invalid `base_path` config provided.');
                }
                require_once $setup;
                Setup::registerAutoloadGit($path);
                break;

            default:
                if ( ! $this->config['base_path']) {
                    $libpath = dirname(dirname(dirname(__DIR__)));
                    $this->config['base_path'] = $libpath . '/DoctrineORM';
                }
                $path = rtrim($this->config['base_path'], DIRECTORY_SEPARATOR);
                $setup = $path . '/Doctrine/ORM/Tools/Setup.php';
                if ( ! is_file($setup)) {
                    throw new ConfigException('DoctrineOrm Adapter: Invalid `base_path` config provided.');
                }
                require_once $setup;
                Setup::registerAutoloadDirectory($path);
                break;
        }
    }

    /* ______________________________________________________________________ */
    
    public function connect()
    {
        if ( ! $this->isConnected()) {
            $cache =& $this->getCacheDriver();
            $cfg = new Configuration;
            $cfg->setMetadataCacheImpl($cache);

            $driverImpl = $cfg->newDefaultAnnotationDriver($this->config['entity_path']);
            $cfg->setMetadataDriverImpl($driverImpl);
            $cfg->setQueryCacheImpl($cache);
            $cfg->setProxyDir($this->config['proxy_path']);
            $cfg->setProxyNamespace($this->config['proxy_namespace']);

            $dev = ($this->config['environment'] == self::ENV_DEVELOPMENT) ? true : false;
            $cfg->setAutoGenerateProxyClasses($dev);

            if (isset($this->config['namespace_aliases'])) {
                foreach ( (array) $this->config['namespace_aliases'] as $alias => $namespace) {
                    $cfg->setEntityNamespaces(array($alias => $namespace));
                }
            }
            $this->em = EntityManager::create($this->config, $cfg);
        }
        return $this->isConnected();
    }

    /* ______________________________________________________________________ */
    
    public function isConnected()
    {
        return ($this->em instanceof EntityManager);
    }

    /* ______________________________________________________________________ */
    
    public function disconnect()
    {
        unset($this->em);
        return true;
    }

    /* ______________________________________________________________________ */
    /**
     * Get entity manager
     * @return \Doctrine\ORM\EntityManager
     */
    public function getResource()
    {
        $this->connect();
        return $this->em;
    }

    /* ______________________________________________________________________ */
    
    protected function &getCacheDriver()
    {
        $class = 'Doctrine\\Common\\Cache\\' . Inflector::camelize($this->config['cache_driver'], true);
        if ( ! class_exists($class)) {
            $class = Inflector::camelize($this->config['cache_driver'], true);
            if ( ! class_exists($class)) {
                throw new RuntimeException('DoctrineOrm Adapter: Cache driver doesn\'t exists: ' . $class);
            }
        }
        
        $driver = new $class();
        return $driver;
    }
}