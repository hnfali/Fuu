<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Http
 */

namespace Fuu\Http;

use Fuu\Http\Exception\HttpException;
use InvalidArgumentException;

/**
 * Http response handler
 * @todo decode an encoded message
 */
class Response implements ResponseServerInterface
{    
    protected $messages = array(

        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    );
    
    protected $body = array();
    protected $headers = array();
    protected $rawHeaders = array();
    protected $responseCode = 200;
    protected $isRedirect = false;
    protected $protocol;
    protected $version = '1.1';

    /* ______________________________________________________________________ */

    public function __construct($protocol = 'HTTP', $version = '1.1')
    {
        $this->protocol = strtoupper($protocol);
        $this->version = $version;
    }

    /* ______________________________________________________________________ */

    public function setHeader($name, $value, $replace = false)
    {
        $this->headerSent(true);
        $name  = $this->normalizeHeader($name);
        $value = (string) $value;

        if ($replace) {
            foreach ($this->headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->headers[$key]);
                }
            }
        }

        $this->headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        );
        return $this;
    }

    /* ______________________________________________________________________ */

    public function getVersion()
    {
        return $this->version;
    }

    /* ______________________________________________________________________ */

    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /* ______________________________________________________________________ */

    public function getProtocol()
    {
        return $this->protocol;
    }

    /* ______________________________________________________________________ */

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    /* ______________________________________________________________________ */

    public function setRawHeader($value)
    {
        $this->headerSent(true);
        foreach (explode("\r\n", (string) $value) as $line) {
            if ('Location' == substr($line, 0, 8)) {
                $this->isRedirect = true;
            }
            $this->rawHeaders[] = $line;
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function setRedirect($url, $code = 302)
    {
        $this->headerSent(true);
        $this->setHeader('Location', $url, true)->setResponseCode($code);
        return $this;
    }

    /* ______________________________________________________________________ */

    public function isRedirect()
    {
        return $this->isRedirect;
    }

    /* ______________________________________________________________________ */

    public function getHeaders()
    {
        return $this->headers;
    }

    /* ______________________________________________________________________ */

    public function getRawHeaders()
    {
        return $this->rawHeaders;
    }

    /* ______________________________________________________________________ */

    public function resetHeaders()
    {
        $this->headers = array();
        return $this;
    }

    /* ______________________________________________________________________ */

    public function resetRawHeaders()
    {
        $this->rawHeaders = array();
        return $this;
    }

    /* ______________________________________________________________________ */

    public function unsetHeader($name)
    {
        if ( ! count($this->headers)) {
            return $this;
        }
        foreach ($this->headers as $index => $header) {
            if ($name == $header['name']) {
                unset($this->headers[$index]);
            }
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function unsetRawHeader($value)
    {
        if ( ! count($this->rawHeaders)) {
            return $this;
        }
        $index = array_search($value, $this->rawHeaders);
        unset($this->rawHeaders[$index]);
        return $this;
    }

    /* ______________________________________________________________________ */

    public function validateResponseCode($code)
    {
        return isset($this->messages[$code]) ? true : false;
    }

    /* ______________________________________________________________________ */

    public function setResponseCode($code)
    {
        if ( ! $this->validateResponseCode($code)) {
            throw new HttpException("Invalid HTTP response code: `{$code}`");
        }

        if ((300 <= $code) && (307 >= $code)) {
            $this->isRedirect = true;
        } else {
            $this->isRedirect = false;
        }

        $this->responseCode = $code;
        return $this;
    }

    /* ______________________________________________________________________ */

    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /* ______________________________________________________________________ */

    public function getStatusMessage($code, $default = null)
    {
        if ($this->validateResponseCode($code)) {
            return $this->messages[$code];
        }
        return $default;
    }

    /* ______________________________________________________________________ */

    public function headerSent($exception = true)
    {
        $sent = headers_sent($file, $line);
        if ($sent && $exception) {
            throw new HttpException("Cannot send headers, headers already sent in {$file}, line {$line}");
        }
        return $sent;
    }

    /* ______________________________________________________________________ */

    public function sendHeaders()
    {
        $this->headerSent(true);
        
        // send status
        $message = $this->messages[$this->responseCode];
        $header = "{$this->protocol}/{$this->version} {$this->responseCode} {$message}";
        header($header);
        
        $headers = $this->headers + $this->rawHeaders;
        foreach ($headers as $header) {
            if (is_array($header)) {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            } else {
                header($header);
            }
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function setBody($content, $name = null)
    {
        if ((null === $name) OR ! is_string($name)) {
            $this->body = array('default' => (string) $content);
        } else {
            $this->body[$name] = (string) $content;
        }

        return $this;
    }

    /* ______________________________________________________________________ */

    public function appendBody($content, $name = null)
    {
        if ((null === $name) OR ! is_string($name)) {
            if (isset($this->body['default'])) {
                $this->body['default'] .= (string) $content;
            } else {
                return $this->append('default', $content);
            }
        } elseif (isset($this->body[$name])) {
            $this->body[$name] .= (string) $content;
        } else {
            return $this->append($name, $content);
        }

        return $this;
    }

    /* ______________________________________________________________________ */

    public function unsetBody($name = null)
    {
        if (null !== $name) {
            $name = (string) $name;
            if (isset($this->body[$name])) {
                unset($this->body[$name]);
                return true;
            }
            return false;
        }

        $this->body = array();
        return true;
    }

    /* ______________________________________________________________________ */

    public function getBody($spec = false)
    {
        if (false === $spec) {
            ob_start();
            $this->outputBody();
            return ob_get_clean();
        } elseif (true === $spec) {
            return $this->body;
        } elseif (is_string($spec) && isset($this->body[$spec])) {
            return $this->body[$spec];
        }

        return null;
    }

    /* ______________________________________________________________________ */

    public function append($name, $content)
    {
        if ( ! is_string($name)) {
            throw new InvalidArgumentException('Invalid body segment key (' . gettype($name) . ')');
        }
        
        if (isset($this->body[$name])) {
            unset($this->body[$name]);
        }
        
        $this->body[$name] = (string) $content;
        return $this;
    }

    /* ______________________________________________________________________ */

    public function prepend($name, $content)
    {
        if ( ! is_string($name)) {
            throw new InvalidArgumentException('Invalid body segment key (' . gettype($name) . ')');
        }

        if (isset($this->body[$name])) {
            unset($this->body[$name]);
        }

        $new = array($name => (string) $content);
        $this->body = $new + $this->body;
        return $this;
    }

    /* ______________________________________________________________________ */

    public function insert($name, $content, $parent = null, $before = false)
    {
        if ( ! is_string($name)) {
            throw new InvalidArgumentException('Invalid body segment key (' . gettype($name) . ')');
        }

        if ((null !== $parent) && ! is_string($parent)) {
            throw new InvalidArgumentException('Invalid body segment parent key (' . gettype($parent) . ')');
        }

        if (isset($this->body[$name])) {
            unset($this->body[$name]);
        }

        if ((null === $parent) OR !isset($this->body[$parent])) {
            return $this->append($name, $content);
        }

        $ins  = array($name => (string) $content);
        $keys = array_keys($this->body);
        $loc  = array_search($parent, $keys);
        if ( ! $before) {
            ++$loc;
        }

        if (0 === $loc) {
            $this->body = $ins + $this->body;
        } elseif ($loc >= (count($this->body))) {
            $this->body = $this->body + $ins;
        } else {
            $pre  = array_slice($this->body, 0, $loc);
            $post = array_slice($this->body, $loc);
            $this->body = $pre + $ins + $post;
        }

        return $this;
    }

    /* ______________________________________________________________________ */

    public function outputBody()
    {
        $body = implode('', $this->body);
        echo $body;
    }

    /* ______________________________________________________________________ */

    public function send()
    {
        $this->sendHeaders();
        $this->outputBody();
    }

    /* ______________________________________________________________________ */

    public function buffer()
    {
        ob_start();
        $this->send();
        return ob_get_clean();
    }

    /* ______________________________________________________________________ */

    public function setContentType($type = 'text/html', $charset = 'utf8')
    {
        $this->setRawHeader("Content-Type: {$type}; charset={$charset}");
    }

    /* ______________________________________________________________________ */

    public function setRefresh($url, $interval)
    {
        $url = urlencode($url);
        $interval = intval($interval);
        $this->setRawHeader("Refresh: {$interval}; url={$url}");
    }

    /* ______________________________________________________________________ */

    public function __toString()
    {
        return (string) $this->buffer();
    }

    /* ______________________________________________________________________ */

    protected function normalizeHeader($name) {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }
}