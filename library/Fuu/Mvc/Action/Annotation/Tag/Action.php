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
final class Action implements FilterInterface
{
    protected $args = array();

    /* ______________________________________________________________________ */
    
    public static function getTagName()
    {
        return 'action';
    }

    /* ______________________________________________________________________ */
    
    public function __construct(array $args)
    {
        $this->args   = $args + array(
            'accepts' => null,
            'is'      => null
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
        if ($this->args['accepts'] && ! in_array(
                $controller->getRequest()->info('media'), 
                (array) $this->args['accepts'])) {
            $controller->error(404);
        }
        
        foreach ( (array) $this->args['is'] as $is) {
            if ( ! $controller->getRequest()->is($is)) {
                $controller->error(404);
            }
        }
    }
}