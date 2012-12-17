<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Logger
 */

namespace Fuu\Logger;

use ReflectionClass;
use DomainException;
use RuntimeException;
use InvalidArgumentException;
use Fuu\Stdlib\Inflector;

class Factory
{
    /* ______________________________________________________________________ */
    /**
     * Create an instance of logger adapter
     * 
     * @param string|\Fuu\Logger\Adapter\AdapterInterface $adapter
     * @param array $params
     * @return \Fuu\Logger\Adapter\AdapterInterface
     * @throws RuntimeException
     * @throws DomainException
     * @throws InvalidArgumentException 
     */
    public static function factory($adapter, array $params = array())
    {
        $prefix = 'Fuu\\Logger\\Adapter\\';
        $interface = $prefix . 'AdapterInterface';
        
        if (is_string($adapter) && ($adapter = Inflector::camelize($adapter, true))) {
            $class = $prefix . $adapter;
            
            if ( ! class_exists($class)) {
                $class = $adapter;
                if ( ! class_exists($class)) {
                    throw new RuntimeException('Logger adapter not found: ' . $adapter);
                }
            }
            
            if ( ! in_array($interface, class_implements($class))) {
                throw new DomainException(sprintf('Logger adapter must implements `%s`.', $interface));
            }
            
            return $this->newInstance($class, $params);
        } elseif ($adapter instanceof Adapter\AdapterInterface) {
            return $adapter;
        }
        
        $type = is_object($adapter) ? get_class($adapter) : gettype($adapter);
        throw new InvalidArgumentException(
            sprintf('%s expects args #1 to be string or `%s`, %s given.', __METHOD__, $interface, $type)
        );
    }

    /* ______________________________________________________________________ */
    
    protected function newInstance($class, array $params)
    {
        switch (count($params)) {
            case 0:
                $obj = new $class;
                break;

            case 1:
                $obj = new $class($params[0]);
                break;

            case 2:
                $obj = new $class($params[0], $params[1]);
                break;

            case 3:
                $obj = new $class($params[0], $params[1], $params[2]);
                break;

            default:
                $r = new ReflectionClass($class);
                $obj = $r->newInstanceArgs($params);
                break;
        }
        return $obj;
    }
}