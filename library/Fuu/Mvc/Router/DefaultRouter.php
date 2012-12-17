<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Router;

use Fuu\Stdlib\Inflector;
use Fuu\Mvc\Action\Request;

class DefaultRouter implements RouterInterface
{
    /* ______________________________________________________________________ */
    
    public function setRequest(Request $request)
    {
        $params   = array();
        $module   = $controller = $action = 'index';
        $segments = $request->getSegments();
        
        switch (count($segments)) {
            case 0:
                break;
            
            case 1:
                $module = array_shift($segments);
                break;
            
            case 2:
                $module = array_shift($segments);
                $controller = array_shift($segments);
                break;
            
            case 3: 
                $module = array_shift($segments);
                $controller = array_shift($segments);
                $action = array_shift($segments);
                break;
            
            default:
                $module = array_shift($segments);
                $controller = array_shift($segments);
                $action = array_shift($segments);
                $params = $segments;
                break;
        }
        
        // apply naming rules
        // TODO: preserve directory separator for module name
        $module = Inflector::camelize($module, true);
        $controller = Inflector::camelize($controller, true);
        $action = Inflector::camelize($action, false);
        
        // pass vars to request object
        $request->setModule($module);
        $request->setController($controller);
        $request->setAction($action);
        $request->setParams($params);
        
        return true;
    }
}