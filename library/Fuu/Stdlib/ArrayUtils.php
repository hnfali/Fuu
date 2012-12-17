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

use Traversable;
use InvalidArgumentException;

/**
 * Utility class for testing and manipulation of PHP arrays.
 * Declared abstract, as we have no need for instantiation.
 * 
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/ArrayUtils.php
 */
abstract class ArrayUtils
{
    /* ______________________________________________________________________ */
    
    public static function hasStringKeys($value, $allowEmpty = false)
    {
        if ( ! is_array($value)) {
            return false;
        }

        if ( ! $value) {
            return $allowEmpty;
        }

        return count(array_filter(array_keys($value), 'is_string')) > 0;
    }

    /* ______________________________________________________________________ */
    
    public static function hasIntegerKeys($value, $allowEmpty = false)
    {
        if ( ! is_array($value)) {
            return false;
        }

        if ( ! $value) {
            return $allowEmpty;
        }

        return count(array_filter(array_keys($value), 'is_int')) > 0;
    }

    /* ______________________________________________________________________ */
    
    public static function hasNumericKeys($value, $allowEmpty = false)
    {
        if ( ! is_array($value)) {
            return false;
        }

        if ( ! $value) {
            return $allowEmpty;
        }

        return count(array_filter(array_keys($value), 'is_numeric')) > 0;
    }

    /* ______________________________________________________________________ */
    
    public static function isList($value, $allowEmpty = false)
    {
        if ( ! is_array($value)) {
            return false;
        }

        if ( ! $value) {
            return $allowEmpty;
        }

        return (array_values($value) === $value);
    }

    /* ______________________________________________________________________ */
    
    public static function isHashTable($value, $allowEmpty = false)
    {
        if ( ! is_array($value)) {
            return false;
        }

        if ( ! $value) {
            return $allowEmpty;
        }

        return (array_values($value) !== $value);
    }

    /* ______________________________________________________________________ */
    
    public static function iteratorToArray($iterator, $recursive = true)
    {
        if ( ! is_array($iterator) && ! ($iterator instanceof Traversable)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects an array or Traversable object');
        }

        if ( ! $recursive) {
            if (is_array($iterator)) {
                return $iterator;
            }

            return iterator_to_array($iterator);
        }

        if (method_exists($iterator, 'toArray')) {
            return $iterator->toArray();
        }

        $array = array();
        foreach ($iterator as $key => $value) {
            if (is_scalar($value)) {
                $array[$key] = $value;
                continue;
            }

            if ($value instanceof Traversable) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            if (is_array($value)) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            $array[$key] = $value;
        }

        return $array;
    }

    /* ______________________________________________________________________ */
    
    public static function merge(array $a, array $b)
    {
        foreach ($b as $key => $value) {
            if (array_key_exists($key, $a)) {
                if (is_int($key)) {
                    $a[] = $value;
                } elseif (is_array($value) && is_array($a[$key])) {
                    $a[$key] = self::merge($a[$key], $value);
                } else {
                    $a[$key] = $value;
                }
            } else {
                $a[$key] = $value;
            }
        }

        return $a;
    }
}