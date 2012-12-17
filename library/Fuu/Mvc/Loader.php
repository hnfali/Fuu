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

use Traversable;
use InvalidArgumentException;

/**
 * A default class loader based on Zend\Loader\StandardAutoloader
 * @package Fuu_Mvc
 */
class Loader implements LoaderInterface
{
    const NS_SEPARATOR     = '\\';
    const PREFIX_SEPARATOR = '_';
    const LOAD_NS          = 'namespaces';
    const LOAD_PREFIX      = 'prefixes';
    const ACT_AS_FALLBACK  = 'fallback_autoloader';

    protected $namespaces = array();
    protected $prefixes = array();
    protected $fallbackAutoloaderFlag = false;

    /* ______________________________________________________________________ */
    
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /* ______________________________________________________________________ */
    
    public function setOptions($options)
    {
        if ( ! is_array($options) && ! ($options instanceof Traversable)) {
            throw new InvalidArgumentException(__METHOD__ . ': Options must be either an array or Traversable');
        }

        foreach ($options as $type => $pairs) {
            switch ($type) {
                case self::LOAD_NS:
                    if (is_array($pairs) OR $pairs instanceof Traversable) {
                        $this->registerNamespaces($pairs);
                    }
                    break;
                case self::LOAD_PREFIX:
                    if (is_array($pairs) OR $pairs instanceof Traversable) {
                        $this->registerPrefixes($pairs);
                    }
                    break;
                case self::ACT_AS_FALLBACK:
                    $this->setFallbackAutoloader($pairs);
                    break;
                default:
                    // ignore
            }
        }
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function setFallbackAutoloader($flag)
    {
        $this->fallbackAutoloaderFlag = (bool) $flag;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function isFallbackAutoloader()
    {
        return $this->fallbackAutoloaderFlag;
    }

    /* ______________________________________________________________________ */
    
    public function registerNamespace($namespace, $directory)
    {
        $namespace = rtrim($namespace, self::NS_SEPARATOR) . self::NS_SEPARATOR;
        $this->namespaces[$namespace] = $this->normalizeDirectory($directory);
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function registerNamespaces($namespaces)
    {
        if ( ! is_array($namespaces) && ! ($namespaces instanceof Traversable)) {
            throw new InvalidArgumentException(
                __METHOD__ . ': Namespace pairs must be either an array or Traversable'
            );
        }

        foreach ($namespaces as $namespace => $directory) {
            $this->registerNamespace($namespace, $directory);
        }
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function registerPrefix($prefix, $directory)
    {
        $prefix = rtrim($prefix, self::PREFIX_SEPARATOR). self::PREFIX_SEPARATOR;
        $this->prefixes[$prefix] = $this->normalizeDirectory($directory);
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function registerPrefixes($prefixes)
    {
        if ( ! is_array($prefixes) && ! ($prefixes instanceof Traversable)) {
            throw new InvalidArgumentException(
                __METHOD__ . ': Prefix pairs must be either an array or Traversable'
            );
        }

        foreach ($prefixes as $prefix => $directory) {
            $this->registerPrefix($prefix, $directory);
        }
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function autoload($class)
    {
        $isFallback = $this->isFallbackAutoloader();
        if (false !== strpos($class, self::NS_SEPARATOR)) {
            if ($this->loadClass($class, self::LOAD_NS)) {
                return $class;
            } elseif ($isFallback) {
                return $this->loadClass($class, self::ACT_AS_FALLBACK);
            }
            return false;
        }
        if (false !== strpos($class, self::PREFIX_SEPARATOR)) {
            if ($this->loadClass($class, self::LOAD_PREFIX)) {
                return $class;
            } elseif ($isFallback) {
                return $this->loadClass($class, self::ACT_AS_FALLBACK);
            }
            return false;
        }
        if ($isFallback) {
            return $this->loadClass($class, self::ACT_AS_FALLBACK);
        }
        return false;
    }

    /* ______________________________________________________________________ */
    
    public function register()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /* ______________________________________________________________________ */
    
    protected function transformClassNameToFilename($class, $directory)
    {
        $matches = array();
        preg_match('/(?P<namespace>.+\\\)?(?P<class>[^\\\]+$)/', $class, $matches);

        $class     = (isset($matches['class'])) ? $matches['class'] : '';
        $namespace = (isset($matches['namespace'])) ? $matches['namespace'] : '';

        return $directory
             . str_replace(self::NS_SEPARATOR, '/', $namespace)
             . str_replace(self::PREFIX_SEPARATOR, '/', $class)
             . '.php';
    }

    /* ______________________________________________________________________ */
    
    protected function loadClass($class, $type)
    {
        if ( ! in_array($type, array(self::LOAD_NS, self::LOAD_PREFIX, self::ACT_AS_FALLBACK))) {
            throw new InvalidArgumentException();
        }

        if ($type === self::ACT_AS_FALLBACK) {
            $filename     = $this->transformClassNameToFilename($class, '');
            $resolvedName = stream_resolve_include_path($filename);
            if ($resolvedName !== false) {
                return include $resolvedName;
            }
            return false;
        }

        foreach ($this->$type as $leader => $path) {
            if (0 === strpos($class, $leader)) {
                $trimmedClass = substr($class, strlen($leader));

                $filename = $this->transformClassNameToFilename($trimmedClass, $path);
                if (file_exists($filename)) {
                    return include $filename;
                }
                return false;
            }
        }
        return false;
    }

    /* ______________________________________________________________________ */
    
    protected function normalizeDirectory($directory)
    {
        $last = $directory[strlen($directory) - 1];
        if (in_array($last, array('/', '\\'))) {
            $directory[strlen($directory) - 1] = DIRECTORY_SEPARATOR;
            return $directory;
        }
        $directory .= DIRECTORY_SEPARATOR;
        return $directory;
    }
}