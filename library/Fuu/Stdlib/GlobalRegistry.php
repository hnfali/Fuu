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

abstract class GlobalRegistry
{
    protected static $data = array();

    /* ______________________________________________________________________ */

    private function __construct() {}

    /* ______________________________________________________________________ */

    public static function get($key, $default = null)
    {
        if (isset(static::$data[$key])) {
            return static::$data[$key];
        }
        return $default;
    }

    /* ______________________________________________________________________ */

    public static function set($key, $value = null, $overwrite = true)
    {
        if (( ! isset(static::$data[$key]) && ! $overwrite) OR $overwrite) {
            static::$data[$key] = $value;
        }
    }

    /* ______________________________________________________________________ */

    public static function delete($key)
    {
        unset(static::$data[$key]);
    }

    /* ______________________________________________________________________ */

    public static function reset()
    {
        static::$data = array();
    }

    /* ______________________________________________________________________ */

    public static function getData()
    {
        return static::$data;
    }
}