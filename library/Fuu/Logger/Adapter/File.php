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

use SplFileInfo;
use InvalidArgumentException;

class File implements AdapterInterface
{
    protected $path;
    protected $fileByLevel;
    protected $levels = array(
        'emergency', 'alert', 'critical',
        'error', 'warning', 'notice',
        'info', 'debug'
    );

    /* ______________________________________________________________________ */
    
    public function __construct($path, $fileByLevel = true)
    {
        $file = new SplFileInfo($path);
        if ( ! $file->isDir() OR ! $file->isWritable()) {
            throw new InvalidArgumentException(__METHOD__ . ': Invalid directory given or directory is not writable.');
        }
        $this->path = $path;
        $this->fileByLevel = (bool) $fileByLevel;
    }

    /* ______________________________________________________________________ */
    
    public function write($level, $message)
    {
        $level = strtolower($level);
        if ( ! in_array($level, $this->levels)) {
            throw new InvalidArgumentException(__METHOD__ . ': Invalid log level: ' . $level);
        }
        
        return file_put_contents(
            $this->generateFilename($level), 
            $this->formatMessage($level, $message), 
            FILE_APPEND
        );
    }

    /* ______________________________________________________________________ */
    
    protected function generateFilename($level)
    {
        $level = ($this->fileByLevel) ? $level . '-' : '';
        return $level . date('Y-m-d') . '.log';
    }

    /* ______________________________________________________________________ */
    
    protected function formatMessage($level, $message)
    {
        $output = '[' . date('D d M Y H:i:s e') . '] ';
        if ( ! $this->fileByLevel) {
            $output .= '[' . $level . '] ';
        }
        $output .= $message . "\n\n";
        return $output;
    }
}