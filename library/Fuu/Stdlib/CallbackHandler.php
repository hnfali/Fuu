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

use InvalidCallbackException;

/**
 * CallbackHandler
 *
 * A handler for a event, event, filterchain, etc. Abstracts PHP callbacks,
 * primarily to allow for lazy-loading and ensuring availability of default
 * arguments (currying).
 * 
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/CallbackHandler.php
 */
class CallbackHandler
{
    protected $callback;
    protected $metadata;

    /* ______________________________________________________________________ */
    
    public function __construct($callback, array $metadata = array())
    {
        $this->metadata  = $metadata;
        $this->registerCallback($callback);
    }

    /* ______________________________________________________________________ */
    
    public function getCallback()
    {
        return $this->callback;
    }

    /* ______________________________________________________________________ */
    
    public function call(array $args = array())
    {
        $callback = $this->getCallback();
        $argCount = count($args);
        switch ($argCount) {
            case 0:
                return call_user_func($callback);
            case 1:
                return call_user_func($callback, array_shift($args));
            case 2:
                $arg1 = array_shift($args);
                $arg2 = array_shift($args);
                return call_user_func($callback, $arg1, $arg2);
            case 3:
                $arg1 = array_shift($args);
                $arg2 = array_shift($args);
                $arg3 = array_shift($args);
                return call_user_func($callback, $arg1, $arg2, $arg3);
            default:
                return call_user_func_array($callback, $args);
        }
    }

    /* ______________________________________________________________________ */
    
    public function __invoke()
    {
        return $this->call(func_get_args());
    }

    /* ______________________________________________________________________ */
    
    public function getMetadata()
    {
        return $this->metadata;
    }

    /* ______________________________________________________________________ */
    
    public function getMetadatum($name)
    {
        if (array_key_exists($name, $this->metadata)) {
            return $this->metadata[$name];
        }
        return null;
    }

    /* ______________________________________________________________________ */
    
    protected function registerCallback($callback)
    {
        if ( ! is_callable($callback)) {
            throw new InvalidCallbackException(sprintf('%s: Invalid callback provided.', __METHOD__));
        }
        $this->callback = $callback;
    }
}