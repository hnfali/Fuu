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
use Fuu\Http\ResponseServerInterface;

class FirePhp implements AdapterInterface
{
    protected $headers = array(
        'X-Wf-Protocol-1' => 'http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1' => 'http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1' => 'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1'
    );
    protected $levels = array(
        'emergency' => 'ERROR',
        'alert'     => 'ERROR',
        'critical'  => 'ERROR',
        'error'     => 'ERROR',
        'warning'   => 'WARN',
        'notice'    => 'INFO',
        'info'      => 'INFO',
        'debug'     => 'LOG'
    );
    protected $counter = 1;
    protected $response;
    protected $headersSet = false;

    /* ______________________________________________________________________ */
    
    public function __construct(ResponseServerInterface $reponse)
    {
        $this->response = $reponse;
    }

    /* ______________________________________________________________________ */
    
    public function write($level, $message)
    {
        $level = strtolower($level);
        if ( ! isset($this->levels[$level])) {
            throw new InvalidArgumentException(__METHOD__ . ': Invalid log level: ' . $level);
        }
        $this->setHeaders();
        $message = $this->formatMessage($level, $message);
        return $this->response->setHeader($message['key'], $message['content']);
    }

    /* ______________________________________________________________________ */
    
    protected function setHeaders()
    {
        if ( ! $this->headersSet) {
            foreach($this->headers as $key => $val) {
                $this->response->setHeader($key, $val);
            }
            $this->headersSet = true;
        }
    }

    /* ______________________________________________________________________ */
    
    protected function formatMessage($level, $message)
    {
        $key = 'X-Wf-1-1-1-' . $this->counter++;
        $content = array(array('Type' => $this->levels[$level]), $message);
        $content = json_encode($content);
        $content = strlen($content) . '|' . $content . '|';
        return compact('key', 'content');
    }
}