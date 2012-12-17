<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc;

class Bootstrapper
{
    protected $classes = array();
    protected $resources;

    /* ______________________________________________________________________ */
    
    public function __construct(ResourceManager $resources)
    {
        $this->resources = $resources;
    }
    
    /* ______________________________________________________________________ */
    
    public function bootstrap($class)
    {
        $interface = 'Fuu\\Mvc\\BootstrapInterface';
        if ( ! in_array($class, $this->classes) && class_exists($class)) {
            if ( ! in_array($interface, class_implements($class))) {
                throw new DomainException(sprintf(
                    'Bootstrap Class `%s` must implements `%s`', $class, $interface
                ));
            }
            
            $obj = new $class($this->resources);
            $methods = get_class_methods($obj);
            foreach($methods as $method) {
                if(substr($method, 0, 4) === 'init') {
                    $this->{$method}();
                }
            }
        }
    }
}