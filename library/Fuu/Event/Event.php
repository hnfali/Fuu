<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Event
 */

namespace Fuu\Event;

use ArrayAccess;
use InvalidArgumentException;

/**
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/EventManager/Event.php
 */
class Event implements EventInterface
{
    protected $name;
    protected $target;
    protected $params = array();
    protected $response;
    protected $stopPropagation = false;

    /* ______________________________________________________________________ */
    
    public function __construct($name = null, $target = null, $params = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $target) {
            $this->setTarget($target);
        }

        if (null !== $params) {
            $this->setParams($params);
        }
    }

    /* ______________________________________________________________________ */
    
    public function getName()
    {
        return $this->name;
    }

    /* ______________________________________________________________________ */
    
    public function getTarget()
    {
        return $this->target;
    }

    /* ______________________________________________________________________ */
    
    public function setParams($params)
    {
        if ( ! is_array($params) && ! is_object($params)) {
            throw new InvalidArgumentException(sprintf(
                'Event parameters must be an array or object, `%s` given.', gettype($params)
            ));
        }

        $this->params = $params;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function getResponse()
    {
        return $this->response;
    }

    /* ______________________________________________________________________ */
    
    public function getParams()
    {
        return $this->params;
    }

    /* ______________________________________________________________________ */
    
    public function getParam($name, $default = null)
    {
        if (is_array($this->params) OR $this->params instanceof ArrayAccess) {
            if ( ! isset($this->params[$name])) {
                return $default;
            }

            return $this->params[$name];
        }

        if ( ! isset($this->params->{$name})) {
            return $default;
        }
        return $this->params->{$name};
    }

    /* ______________________________________________________________________ */
    
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function setParam($name, $value)
    {
        if (is_array($this->params) OR $this->params instanceof ArrayAccess) {
            $this->params[$name] = $value;
        } else {
            $this->params->{$name} = $value;
        }
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function setResponse($value)
    {
        $this->response = $value;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function stopPropagation($flag = true)
    {
        $this->stopPropagation = (bool) $flag;
    }

    /* ______________________________________________________________________ */
    
    public function propagationIsStopped()
    {
        return $this->stopPropagation;
    }
}