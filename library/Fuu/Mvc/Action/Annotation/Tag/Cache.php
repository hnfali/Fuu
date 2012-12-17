<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Action\Annotation\Tag;

use DomainException;
use RuntimeException;
use Fuu\Stdlib\Inflector;
use Fuu\Storage\Cache\Factory;
use Fuu\Storage\Strategy\StrategyInterface;
use Fuu\Mvc\Action\ControllerInterface;

/** @Annotation */
final class Cache implements FilterInterface
{
    protected $args = array();

    /* ______________________________________________________________________ */
    
    public static function getTagName()
    {
        return 'cache';
    }

    /* ______________________________________________________________________ */
    
    public function __construct(array $args)
    {
        $this->args   = $args + array(
            'key'        => null,
            'adapter'    => 'PhpFile',
            'strategies' => array(),
            'config'     => array()
        );
    }

    /* ______________________________________________________________________ */
    
    public function __isset($key)
    {
        return isset($this->args[$key]);
    }

    /* ______________________________________________________________________ */
    
    public function __get($key)
    {
        if (isset($this->args[$key])) {
            return $this->args[$key];
        }
    }

    /* ______________________________________________________________________ */
    
    public function __toString()
    {
        return http_build_query($this->args);
    }

    /* ______________________________________________________________________ */
    
    public function toArray()
    {
        return $this->args;
    }

    /* ______________________________________________________________________ */
    
    public function filter(ControllerInterface $controller)
    {
        if ( ! $this->args['key']) {
            throw new RuntimeException(__METHOD__ . ': Cache key is not set.');
        }
        
        $adapter = Factory::factory($this->args['adapter'], (array) $this->args['config']);
        $key = Inflector::slug(get_class($controller), '_') . '_' . $this->args['key'];
        
        if ($this->args['strategies']) {
            foreach ( (array) $this->args['strategies'] as $$strategy) {
                $adapter->addStrategy($this->getStrategy($strategy));
            }
        }
        
        if ($cache = $adapter->read($key)) {
            $controller->getEvents()->attach('invokeAction', function($e) use($cache) {
                $e->setResponse($cache);
                $e->stopPropagation();
                return $e;
            });
        } else {
            $controller->getEvents()->attach('invokeAction', function($e) use($key, $adapter) {
                $response = $e->getResponse();
                $adapter->write($key, $response);
                return $e;
            }, -1);
        }
    }

    /* ______________________________________________________________________ */
    
    protected function &getStrategy($name)
    {
        $prefix = 'Fuu\\Storage\\Strategy\\';
        $class = $prefix . $name;
        if ( ! class_exists($class)) {
            $class = $name;
            if ( ! class_exists($class)) {
                throw new RuntimeException('Strategy adapter does not exists: ' . $name);
            }
        }
        
        $strategy = new $class;
        if ( ! ($strategy instanceof StrategyInterface)) {
            throw new DomainException('Cache strategy must implements StrategyInterface.');
        }
        
        return $strategy;
    }
}