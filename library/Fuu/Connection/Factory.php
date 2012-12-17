<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Connection
 */

namespace Fuu\Connection;

use DomainException;
use RuntimeException;
use InvalidArgumentException;
use Fuu\Stdlib\Inflector;

class Factory
{
    /* ______________________________________________________________________ */
    /**
     * Create an instance of connection adapter
     * 
     * @param string|\Fuu\Connection\Adapter\AdapterInterface $adapter
     * @param array $config
     * @return \Fuu\Connection\AdapterInterface
     * @throws RuntimeException
     * @throws DomainException
     * @throws InvalidArgumentException 
     */
    public static function factory($adapter, array $config = array())
    {
        $prefix = 'Fuu\\Connection\\Adapter\\';
        $interface = $prefix . 'AdapterInterface';
        
        if (is_string($adapter) && ($adapter = Inflector::camelize($adapter, true))) {
            $class = $prefix . $adapter;
            
            if ( ! class_exists($class)) {
                $class = $adapter;
                if ( ! class_exists($class)) {
                    throw new RuntimeException('Connection adapter not found: ' . $adapter);
                }
            }
            
            if ( ! in_array($interface, class_implements($class))) {
                throw new DomainException(sprintf('Connection adapter must implements `%s`.', $interface));
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