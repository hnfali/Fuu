<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Stdlib
 */

namespace Fuu\Stdlib;

use ArrayObject;

/**
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/Parameters.php
 */
class Parameters extends ArrayObject implements ParametersInterface
{
    /* ______________________________________________________________________ */
    
    public function __construct(array $values = null)
    {
        if (null === $values) {
            $values = array();
        }
        parent::__construct($values, ArrayObject::ARRAY_AS_PROPS);
    }

    /* ______________________________________________________________________ */
    
    public function fromArray(array $values)
    {
        $this->exchangeArray($values);
    }

    /* ______________________________________________________________________ */
    
    public function fromString($string)
    {
        $array = array();
        parse_str($string, $array);
        $this->fromArray($array);
    }

    /* ______________________________________________________________________ */
    
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /* ______________________________________________________________________ */
    
    public function toString()
    {
        return http_build_query($this);
    }

    /* ______________________________________________________________________ */
    
    public function offsetGet($name)
    {
        if (isset($this[$name])) {
            return parent::offsetGet($name);
        }
        return null;
    }

    /* ______________________________________________________________________ */
    
    public function get($name, $default = null)
    {
        if (isset($this[$name])) {
            return parent::offsetGet($name);
        }
        return $default;
    }

    /* ______________________________________________________________________ */
    
    public function set($name, $value)
    {
        $this[$name] = $value;
        return $this;
    }
}