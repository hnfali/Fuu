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

class PhpFile extends File
{
    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'extension' => 'cache.php',
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $data, $expiry = null)
    {
        if ($expiry) {
            throw new InvalidArgumentException(
                'Expiry time must be set either from constructor or `setConfig` methos.'
            );
        }
        $data = $this->applyStrategies('write', $data, parent::WRITE_MODE);
        return file_put_contents($this->path($key), '<' . '?php return ' . var_export($data, true) . ';');
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

            $data = include $this->path($key);
            return $this->applyStrategies('read', $data, parent::READ_MODE);
        }
    }
}