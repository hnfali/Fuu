<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Action;

use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Http\Request as HttpRequest;

class Request extends HttpRequest implements RequestActionInterface
{
    const SEGMENTS_INDEX_FROM_0 = 1;
    const SEGMENTS_INDEX_FROM_1 = 0;
    
    protected $module = 'index';
    protected $controller = 'index';
    protected $action = 'index';
    protected $params = array();
    protected $segments = array();

    /* ______________________________________________________________________ */
    
    public function __construct($config = array())
    {
        if ($config instanceof ApplicationConfigInterface) {
            $config = (array) $config['request'];
        }
        
        parent::__construct( (array) $config);
        $this->segments = array_filter(explode('/', $this->info('routepath')), function($e) {
            return (trim($e) === '') ? false : true;
        });
    }
    
    /* ______________________________________________________________________ */
    
    public function __get($param)
    {
        return isset($this->params[$param]) ? $this->params[$param] : null;
    }
    
    /* ______________________________________________________________________ */
    
    public function getParams()
    {
        return $this->params;
    }
    
    /* ______________________________________________________________________ */
    
    public function setParams(array $params)
    {
        $this->params = $params;
    }
    
    /* ______________________________________________________________________ */
    
    public function getParam($name, $default = null)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return $default;
    }
    
    /* ______________________________________________________________________ */
    
    public function setParam($name, $value = null)
    {
        $this->params[$name] = $value;
    }
    
    /* ______________________________________________________________________ */
    
    public function bindParam($index, $name, $filters = array())
    {
        if (isset($this->params[$index])) {
            $this->params[$name] = $this->params[$index];
            
            if ( ! is_array($filters)) {
                $filters = array($filters);
            }
            
            foreach ($filters as $$filter) {
                if (is_callable($filter)) {
                    $this->params[$name] = $filter($this->params[$name]);
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /* ______________________________________________________________________ */
    
    public function getSegments()
    {
        return $this->segments;
    }
    
    /* ______________________________________________________________________ */
    
    public function setSegments(array $segments)
    {
        $this->segments = $segments;
    }
    
    /* ______________________________________________________________________ */
    
    public function getSegment($index, $flag = null)
    {
        if ($flag == self::SEGMENTS_INDEX_FROM_1) {
            $index += 1;
        }
        
        if (isset($this->segments[$index])) {
            return $this->segments[$index];
        }
        return null;
    }
    
    /* ______________________________________________________________________ */
    
    public function setSegment($index, $value = null, $flag = null)
    {
        if ($flag == self::SEGMENTS_INDEX_FROM_1) {
            $index += 1;
        }
        
        $this->segments[$index] = $value;
    }
    
    /* ______________________________________________________________________ */
    
    public function getModule()
    {
        return $this->module;
    }

    /* ______________________________________________________________________ */
    
    public function setModule($module)
    {
        $this->module = $module;
    }

    /* ______________________________________________________________________ */
    
    public function getController()
    {
        return $this->controller;
    }

    /* ______________________________________________________________________ */
    
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /* ______________________________________________________________________ */
    
    public function getAction()
    {
        return $this->action;
    }

    /* ______________________________________________________________________ */
    
    public function setAction($action)
    {
        $this->action = $action;
    }
}