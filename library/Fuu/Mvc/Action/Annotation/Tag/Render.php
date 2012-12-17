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
use Fuu\Event\Event;
use Fuu\Stdlib\Inflector;
use Fuu\Mvc\Action\ControllerInterface;

/** @Annotation */
final class Render implements FilterInterface
{
    protected $args = array();

    /* ______________________________________________________________________ */
    
    public static function getTagName()
    {
        return 'render';
    }

    /* ______________________________________________________________________ */
    
    public function __construct(array $args)
    {
        $this->args    = $args + array(
            'file'     => null,
            'renderer' => 'Twig',
            'config'   => array()
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
        $class = Inflector::camelize($this->args['renderer']);
        if ( ! class_exists($class)) {
            $class = 'Fuu\\View\\Renderer\\' . $class;
            if ( ! class_exists($class)) {
                throw new RuntimeException(__METHOD__ . ': Invalid view renderer: ' . $class);
            }
        }
        
        $interface = 'Fuu\\View\\Renderer\\RendererInterface';
        if ( ! in_array($interface, class_implements($class))) {
            throw new DomainException(__METHOD__ . ': View renderer must implements ' . $interface);
        }
        
        $renderer = new $class( (array) $this->args['config']);
        $controller->setRenderer($renderer);
        
        // set template file to render via event
        if ($file = $this->args['file']) {
            $controller->getEvents()->attach('render', function (Event $e) use(&$controller, $file) {
                $config = $controller->getConfig();
                $prefix = substr($file, 0, 1);

                switch ($prefix) {
                    case '#':
                        $prefix = $config->app_path . $prefix;
                        break;

                    case '~':
                        $prefix = $config->app_path . '/' . $config->module_dir . '/' . 
                            $controller->getRequest()->getModule() . $prefix;
                        break;

                    default:
                        break;
                }
                $e->setParam('template', $file);
                return $e;
            });
        }
    }
}