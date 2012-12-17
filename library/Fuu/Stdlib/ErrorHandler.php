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

use LogicException;
use ErrorException;

/**
 * ErrorHandler that can be used to catch internal PHP errors
 * and convert to a ErrorException instance.
 * 
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/ErrorHandler.php
 */
abstract class ErrorHandler
{
    protected static $started = false;
    protected static $errorException = null;

    /* ______________________________________________________________________ */
    
    public static function started()
    {
        return static::$started;
    }

    /* ______________________________________________________________________ */
    
    public static function start($errorLevel = \E_WARNING)
    {
        if (static::started() === true) {
            throw new LogicException('ErrorHandler already started');
        }

        static::$started = true;
        static::$errorException = null;

        set_error_handler(array(get_called_class(), 'addError'), $errorLevel);
    }

    /* ______________________________________________________________________ */
    
    public static function stop($throw = false)
    {
        if (static::started() === false) {
            throw new LogicException('ErrorHandler not started');
        }

        $errorException = static::$errorException;

        static::$started = false;
        static::$errorException = null;
        restore_error_handler();

        if ($errorException && $throw) {
            throw $errorException;
        }

        return $errorException;
    }

    /* ______________________________________________________________________ */
    
    public static function addError($errno, $errstr = '', $errfile = '', $errline = 0)
    {
        static::$errorException = new ErrorException($errstr, 0, $errno, $errfile, $errline, static::$errorException);
    }
}