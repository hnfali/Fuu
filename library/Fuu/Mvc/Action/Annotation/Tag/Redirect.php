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
final class Redirect implements FilterInterface
{
    const IF_IFNOT = 'if-ifnot';
    const IFNOT_IF = 'ifnot-if';
    
    protected $args = array();

    /* ______________________________________________________________________ */
    
    public static function getTagName()
    {
        return 'redirect';
    }

    /* ______________________________________________________________________ */
    
    public function __construct(array $args)
    {
        $this->args = $args + array(
            'uri'   => null,
            'code'  => 302,
            'exit'  => true,
            'if'    => array(),
            'ifnot' => array(),
            'order' => self::IF_IFNOT,
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
        $args = $this->args;
        $if = function() use(&$controller, $args) {
            foreach ( (array) $args['if'] as $cond) {
                if ($controller->getRequest()->is($cond)) {
                    $controller->redirect($args['uri'], $args['code'], $args['exit']);
                }
            }
        };
        $ifnot = function() use(&$controller, $args) {
            foreach ( (array) $args['ifnot'] as $cond) {
                if ( ! $controller->getRequest()->is($cond)) {
                    $controller->redirect($args['uri'], $args['code'], $args['exit']);
                }
            }
        };
        
        if ($args['order'] == self::IFNOT_IF) {
            $ifnot(); $if();
        } else {
            $if(); $ifnot();
        }
    }
}