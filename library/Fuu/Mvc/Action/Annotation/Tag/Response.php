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

use Fuu\Mvc\Action\ControllerInterface;

/** @Annotation */
final class Response implements FilterInterface
{
    protected $args = array();

    /* ______________________________________________________________________ */
    
    public static function getTagName()
    {
        return 'response';
    }

    /* ______________________________________________________________________ */
    
    public function __construct(array $args)
    {
        $this->args    = $args + array(
            'type'     => 'text/html',
            'charset'  => 'utf8',
            'compress' => null // not implemented
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
        $controller->getResponse()->setContentType(
            $this->args['type'],
            $this->args['charset']
        );
    }
}