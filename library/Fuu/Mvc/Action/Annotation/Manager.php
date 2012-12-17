<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Action\Annotation;

use ReflectionMethod;
use RuntimeException;
use Fuu\Storage\Cache\Factory;
use Fuu\Storage\Cache\Adapter\AdapterInterface;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Mvc\Action\Controller;
use Fuu\Mvc\Action\Annotation\Tag\Action;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\Annotation\Target;

class Manager
{
    const IS_ACTION_KEY = 'is_action?';
    const CONFIG_NAME = 'annotation';
    const CACHE_KEY = 'annotations';
    
    /**
     * Configuration
     * @var array
     */
    protected $config;
    
    /**
     * Cache adapter
     * @var \Fuu\Storage\Cache\Adapter\AdapterInterface
     */
    protected $cache;
    
    /**
     * Parsed annotations (after calling `getAnnotations` method)
     * @var array 
     */
    protected $annotations = array();
    
    /**
     * Registered annotations
     * @var array 
     */
    protected $tags = array();
    
    /**
     * Annotatins to be ignored
     * @var array 
     */
    protected $ignores = array(
        'access'=> true, 'author'=> true, 'copyright'=> true, 'deprecated'=> true,
        'example'=> true, 'ignore'=> true, 'internal'=> true, 'link'=> true, 'see'=> true,
        'since'=> true, 'tutorial'=> true, 'version'=> true, 'package'=> true,
        'subpackage'=> true, 'name'=> true, 'global'=> true, 'param'=> true,
        'return'=> true, 'staticvar'=> true, 'category'=> true, 'staticVar'=> true,
        'static'=> true, 'var'=> true, 'throws'=> true, 'inheritdoc'=> true,
        'inheritDoc'=> true, 'license'=> true, 'todo'=> true, 'deprecated'=> true,
        'deprec'=> true, 'author'=> true, 'property' => true, 'method' => true,
        'abstract'=> true, 'exception'=> true, 'magic' => true, 'api' => true,
        'final'=> true, 'filesource'=> true, 'throw' => true, 'uses' => true,
        'usedby'=> true, 'private' => true, 'Annotation' => true, 'override' => true,
        'codeCoverageIgnore' => true, 'codeCoverageIgnoreStart' => true, 'codeCoverageIgnoreEnd' => true,
        'Required' => true, 'Attribute' => true, 'Attributes' => true,
        'Target' => true, 'SuppressWarnings' => true
    );

    /* ______________________________________________________________________ */
    
    public function __construct($config = array())
    {
        if ($config instanceof ApplicationConfigInterface) {
            $this->config = (array) $config->{self::CONFIG_NAME};
        } else {
            $this->config = (array) $config;
        }
        
        // default cache adapter
        $cache_adapter = 'Memory';
        $this->config += compact('cache_adapter');
    }

    /* ______________________________________________________________________ */
    
    public function setCacheAdapter($adapter, array $config = array())
    {
        $this->cache = Factory::factory($adapter, $config);
    }

    /* ______________________________________________________________________ */
    
    public function getCacheAdapter()
    {
        if ( ! ($this->cache instanceof AdapterInterface)) {
            $this->cache = Factory::factory($this->config['cache_adapter'], $this->config);
        }
        return $this->cache;
    }

    /* ______________________________________________________________________ */
    
    public function registerTag($class)
    {
        $interface = 'Fuu\\Mvc\\Action\\Annotation\\Tag\\TagInterface';
        if (class_exists($class) && in_array($interface, class_implements($class))) {
            return ($this->tags[$class::getTagName()] = $class);
        }
        
        throw new RuntimeException('Invalid tag class: ' . $class);
    }

    /* ______________________________________________________________________ */
    
    public function unregisterTag($name)
    {
        unset($this->tags[$name]);
    }

    /* ______________________________________________________________________ */
    
    public function validateAction(Controller $controller, $action)
    {
        if ( ! method_exists($controller, $action)) {
            return false;
        }
        
        // register Action annotation
        $this->registerTag('Fuu\\Mvc\\Action\\Annotation\\Tag\\Action');
        
        $annotations = $this->getAnnotations($controller, $action);
        if ( ! isset($annotations[self::IS_ACTION_KEY]) 
                OR ! $annotations[self::IS_ACTION_KEY]) {
            return false;
        }
        
        return true;
    }

    /* ______________________________________________________________________ */
    
    public function filterAction(Controller $controller, $action)
    {
        if ( ! method_exists($controller, $action)) {
            return;
        }
        
        $annotations = $this->getAnnotations($controller, $action);
        foreach ($annotations as $key => $tag) {
            if ($tag instanceof Tag\FilterInterface) {
                $tag->filter($controller);
            }
        }
    }

    /* ______________________________________________________________________ */
    
    public function getAnnotations(Controller $controller, $action)
    {
        $key = $this->getKey($controller, $action);
        
        // `no` means it is the first time this method is called
        if ( ! $this->annotations) {
            $cache = (array) $this->getCacheAdapter()->read(self::CACHE_KEY);
            
            if ( ! isset($cache[$key])) {
                $parser = new DocParser;
                $parser->setTarget(Target::TARGET_METHOD);
                $parser->setImports($this->tags);
                $parser->setIgnoredAnnotationNames($this->ignores);

                $method = new ReflectionMethod($controller, $action);
                
                $tags = array();
                foreach ($parser->parse($method->getDocComment()) as $tag) {
                    if ($tag instanceof Tag\TagInterface) {
                        $tags[$tag::getTagName()] = $tag;
                    }
                }
                
                $actionTag = Action::getTagName();
                $cache[$key] = $tags + array(
                    self::IS_ACTION_KEY => isset($tags[$actionTag])
                );
                
                $this->getCacheAdapter()->write(self::CACHE_KEY, $cache);
            }
            $this->annotations = $cache;
        }
        
        return isset($this->annotations[$key]) ? $this->annotations[$key] : null;
    }

    /* ______________________________________________________________________ */
    
    protected function getKey(Controller $controller, $action)
    {
        return get_class($controller) . '.' . $action;
    }
}