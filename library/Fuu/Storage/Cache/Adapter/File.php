<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Cache\Adapter;

use SplFileInfo;
use RuntimeException;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Fuu\Mvc\Exception\ConfigException;

class File extends CacheAbstract implements AdapterInterface
{
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'path' => null,
            'expiry' => '+1 hour',
            'extension' => 'cache',
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        $file = new SplFileInfo($this->config['path']);
        if ( ! $file->isDir() OR ! $file->isWritable()) {
            throw new ConfigException('Cache Adapter: Invalid path given or directory is not writable.');
        }
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $data, $expiry = null)
    {
        if ($expiry) {
            throw new InvalidArgumentException(
                'Expiry time must be set either from constructor or `setConfig` method.'
            );
        }
        $data = $this->applyStrategies('write', $data, parent::WRITE_MODE);
        return file_put_contents($this->path($key), $data);
    }

    /* ______________________________________________________________________ */
    
    public function read($key)
    {
        $file = new SplFileInfo($this->path($key));
        if ($file->isFile() && $file->isReadable()) {
            $cachetime = (is_int($this->config['expiry']) ? 
                $this->config['expiry'] : strtotime($this->config['expiry'])) - time();

            if ((time() - $file->getMTime()) > $cachetime) {
                unlink($this->path($key));
                return;
            }

            $data = file_get_contents($this->path($key));
            return $this->applyStrategies('read', $data, parent::READ_MODE);
        }
    }

    /* ______________________________________________________________________ */
    
    public function delete($key)
    {
        $file = new SplFileInfo($this->path($key));
        if ($file->isFile()) {
            unlink($this->path($key));
        }
    }

    /* ______________________________________________________________________ */
    
    public function decrement($key, $offset = 1)
    {
        return false; // not supported
    }

    /* ______________________________________________________________________ */
    
    public function increment($key, $offset = 1)
    {
        return false; // not supported
    }

    /* ______________________________________________________________________ */
    
    public function flush()
    {
        $base = new RecursiveDirectoryIterator($this->config['path']);
        $iterator = new RecursiveIteratorIterator($base);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getPathName());
            }
        }
        return true;
    }

    /* ______________________________________________________________________ */
    
    public static function isEnabled()
    {
        return true;
    }

    /* ______________________________________________________________________ */
    
    protected function path($file)
    {
        $ext = ($this->config['extension']) ? '.' . ltrim($this->config['extension'], '.') : '';
        return rtrim($this->config['path'], '/\\') . DIRECTORY_SEPARATOR . $file . $ext;
    }
}