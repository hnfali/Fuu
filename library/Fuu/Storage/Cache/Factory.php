<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Cache;

use DomainException;
use RuntimeException;
use InvalidArgumentException;
use Fuu\Stdlib\Inflector;

class Factory
{
    /* ______________________________________________________________________ */
    /**
     * Create an instance of cache adapter
     * 
     * @param string|\Fuu\Storage\Cache\AdapterInterface $adapter
     * @param array $config
     * @return \Fuu\Storage\Cache\AdapterInterface
     * @throws RuntimeException
     * @throws DomainException
     * @throws InvalidArgumentException 
     */
    public static function factory($adapter, array $config = array())
    {
        $prefix = 'Fuu\\Storage\\Cache\\Adapter\\';
        $interface = $prefix . 'AdapterInterface';
        
        if (is_string($adapter) && ($adapter = Inflector::camelize($adapter, true))) {
            $adapter = ($adapter == 'Memcached') ? 'Memcache' : $adapter;
            $class = $prefix . $adapter;
            
            if ( ! class_exists($class)) {
                $class = $adapter;
                if ( ! class_exists($class)) {
                    throw new RuntimeException('Cache adapter not found: ' . $adapter);
                }
            }
            
            if ( ! in_array($interface, class_implements($class))) {
                throw new DomainException(sprintf('Cache adapter must implements `%s`.', $interface));
            }
            
            // check if the adapter is supported in current environment
            if ( ! $class::isEnabled()) {
                throw new RuntimeException(
                    sprintf('%s: `%s` is not supported in your current environment.', __METHOD__, $adapter)
                );
            }
            
            $obj = new $class($config);
            return $obj;
        } elseif ($adapter instanceof Adapter\AdapterInterface) {
            return $adapter;
        }
        
        $type = is_object($adapter) ? get_class($adapter) : gettype($adapter);
        throw new InvalidArgumentException(
            sprintf('%s expects args #1 to be string or `%s`, %s given.', __METHOD__, $interface, $type)
        );
    }
}