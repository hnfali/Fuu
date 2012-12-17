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

class Syslog implements AdapterInterface
{
    protected $id;
    protected $options;
    protected $facility;
    protected $isConnected = false;
    protected $levels = array(
        'emergency' => LOG_EMERG,
        'alert'     => LOG_ALERT,
        'critical'  => LOG_CRIT,
        'error'     => LOG_ERR,
        'warning'   => LOG_WARNING,
        'notice'    => LOG_NOTICE,
        'info'      => LOG_INFO,
        'debug'     => LOG_DEBUG
    );

    /* ______________________________________________________________________ */
    
    public function __construct($id = false, $options = LOG_ODELAY, $facility = LOG_USER)
    {
        $this->id = $id;
        $this->options = $options;
        $this->facility = $facility;
    }

    /* ______________________________________________________________________ */
    
    public function write($level, $message)
    {
        $level = strtolower($level);
        if ( ! isset($this->levels[$level])) {
            throw new InvalidArgumentException(__METHOD__ . ': Invalid log level: ' . $level);
        }
        if ( ! $this->isConnected) {
            closelog();
            openlog($this->id, $this->options, $this->facility);
            $this->isConnected = true;
        }
        
        return syslog($this->levels[$level], $message);
    }
}