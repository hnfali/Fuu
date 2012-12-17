<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Logger
 */

namespace Fuu\Logger\Adapter;

use InvalidArgumentException;
use Fuu\Storage\Cache\Factory;

class Cache implements AdapterInterface
{
    protected $cacheAdapter;
    protected $levels = array(
        'emergency', 'alert', 'critical',
        'error', 'warning', 'notice',
        'info', 'debug'
    );

    /* ______________________________________________________________________ */
    
    public function __construct($adapter, array $config = array())
    {
        $this->cacheAdapter = Factory::factory($adapter, $config);
    }

    /* ______________________________________________________________________ */
    
    public function write($level, $message)
    {
        $level = strtolower($level);
        if ( ! in_array($level, $this->levels)) {
            throw new InvalidArgumentException(__METHOD__ . ': Invalid log level: ' . $level);
        }
        return $this->cacheAdapter->write($level, $message);
    }
}