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

use RuntimeException;
use InvalidArgumentException;
use Fuu\Stdlib\String;

class Request implements RequestServerInterface
{
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_PATCH   = 'PATCH';
    
    protected $uri = null;
    protected $info = array();
    protected $files = array();
    protected $config = array();
    protected $detectors = array();

    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'strip_quotes'     => true,
            'sanitize_url'     => true,
            'normalize_string' => '.,-_/\\',
            'encoding'         => 'utf8' // not implemented
        );
        $this->config = $config + $defaults;
        $this->init();
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        $hostname = $this->env('HTTP_HOST');
        $protocol = ($this->env('HTTPS') == 'on') ? 'https' : 'http';
        
        if (empty($hostname)) {
            $server   = $this->env('SERVER_NAME');
            $port     = $this->env('SERVER_PORT');
            $hostname = (($protocol == 'http' && $port == 80) OR ($protocol == 'https' && $port == 443)) 
                ? $server : "{$server}:{$port}";
        }
        
        $prefix  = "{$protocol}://{$hostname}";
        $base_uri = $this->detectBaseUri();
        $base_url = rtrim("{$prefix}/{$base_uri}", '/');
        
        $this->info += compact('protocol', 'prefix', 'base_uri', 'base_url');
        $this->info += array(
            'uri'   => $this->getUri(),
            'url'   => $this->getUrl()
        );
        $this->info += parse_url($this->getUrl());
    }

    /* ______________________________________________________________________ */
    
    public function __toString()
    {
        return (string) $this->getUri();
    }

    /* ______________________________________________________________________ */
    
    public function setDetector($spec, $callable)
    {
        if ( ! is_callable($callable)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects args #2 to be callable.');
        }
        $this->detectors[strtolower(trim($spec, '-_'))] = $callable;
    }

    /* ______________________________________________________________________ */
    
    public function getUri()
    {
        if ($this->uri === null) {
            switch (true) {
                case (bool) $this->env('HTTPX_REWRITE_URL'):
                    $this->uri = $this->env('HTTPX_REWRITE_URL');
                    break;

                case (($this->env('IIS_WasUrlRewritten') == '1') 
                        && ($this->env('UNENCODED_URL') != '')):
                    $this->uri = $this->env('UNENCODED_URL');
                    break;

                case (bool) $this->env('REQUEST_URI'):
                    $this->uri = $this->env('REQUEST_URI');
                    break;

                case (bool) $this->env('PATH_INFO'):
                    $this->uri = $this->env('PATH_INFO');
                    break;

                case (bool) $this->env('ORIG_PATH_INFO'):
                    $this->uri = $this->env('ORIG_PATH_INFO');
                    if ($this->env('QUERY_STRING')) {
                        $this->uri .= '?' . $this->env('QUERY_STRING');
                    }
                    break;

                default:
                    $this->uri = '';
                    break;
            }
            
            // replaces backward slash with forward slash
            $this->uri = str_replace('\\', '/', $this->uri);
            
            if ($this->config['sanitize_url']) {
                $this->uri = filter_var($this->uri, FILTER_SANITIZE_URL);
            }
            
            if ($this->config['strip_quotes']) {
                $this->uri = str_replace(array('"', "'", '`'), '', $this->uri);
            }
            
            if ($str = $this->config['normalize_string']) {
                $this->uri = String::normalize($this->uri, is_array($str) ? implode('', $str) : $str);
            }
            
            $this->uri = rtrim($this->uri, '/');
        }
        return $this->uri;
    }

    /* ______________________________________________________________________ */
    
    public function getUrl()
    {
        return $this->info('prefix') . $this->getUri();
    }

    /* ______________________________________________________________________ */
    
    public function get($key = null, $default = null, array $filters = array())
    {
        return $this->filter($this->input($key, $default, $_GET), $filters);
    }

    /* ______________________________________________________________________ */
    
    public function post($key = null, $default = null, array $filters = array())
    {
        return $this->filter($this->input($key, $default, $_POST), $filters);
    }

    /* ______________________________________________________________________ */
    
    public function env($key = null, $default = null, array $filters = array())
    {
        return $this->filter($this->input($key, $default, $_SERVER + $_ENV), $filters);
    }

    /* ______________________________________________________________________ */
    
    public function cookie($key = null, $default = null, array $filters = array())
    {
        return $this->filter($this->input($key, $default, $_COOKIE), $filters);
    }

    /* ______________________________________________________________________ */
    
    public function session($key = null, $default = null, array $filters = array())
    {
        return $this->filter($this->input($key, $default, $_SESSION), $filters);
    }

    /* ______________________________________________________________________ */
    
    public function files($key = null, $default = null, array $filters = array())
    {
        if ($this->files === null) {
            $files = array();

            $mapFiles = function(&$array, $paramName, $index, $value) use (&$mapFiles) {
                if ( ! is_array($value)) {
                    $array[$index][$paramName] = $value;
                } else {
                    foreach ($value as $i => $v) {
                        $mapFiles($array[$index], $paramName, $i, $v);
                    }
                }
            };

            foreach ($_FILES as $fileName => $fileParams) {
                $files[$fileName] = array();
                foreach ($fileParams as $param => $data) {
                    if ( ! is_array($data)) {
                        $files[$fileName][$param] = $data;
                    } else {
                        foreach ($data as $i => $v) {
                            $mapFiles($files[$fileName], $param, $i, $v);
                        }
                    }
                }
            }
            
            $this->files = $files;
        }
        return $this->filter($this->input($key, $default, $this->files), $filters);
    }
    
    /* ______________________________________________________________________ */
    
    public function put($key = null, $default = null, array $filters = array())
    {
        throw new RuntimeException(__METHOD__ . ': Not implemented.');
    }

    /* ______________________________________________________________________ */
    
    public function delete($key = null, $default = null, array $filters = array())
    {
        throw new RuntimeException(__METHOD__ . ': Not implemented.');
    }

    /* ______________________________________________________________________ */
    
    public function trace($key = null, $default = null, array $filters = array())
    {
        throw new RuntimeException(__METHOD__ . ': Not implemented.');
    }

    /* ______________________________________________________________________ */
    
    public function connect($key = null, $default = null, array $filters = array())
    {
        throw new RuntimeException(__METHOD__ . ': Not implemented.');
    }

    /* ______________________________________________________________________ */
    
    public function patch($key = null, $default = null, array $filters = array())
    {
        throw new RuntimeException(__METHOD__ . ': Not implemented.');
    }

    /* ______________________________________________________________________ */
    
    public function options($key = null, $default = null, array $filters = array())
    {
        throw new RuntimeException(__METHOD__ . ': Not implemented.');
    }

    /* ______________________________________________________________________ */
    
    public function head($key = null, $default = null, array $filters = array())
    {
        throw new RuntimeException(__METHOD__ . ': Not implemented.');
    }

    /* ______________________________________________________________________ */
    
    public function info($name)
    {
        if (isset($this->info[$name])) {
            return $this->info[$name];
        }
        
        switch (strtolower(trim($name, '-_'))) {
            case 'method': 
            case 'requestmethod':
                return $this->env('REQUEST_METHOD');
                break;
            
            case 'ext': 
            case 'extension': 
            case 'filetype': 
            case 'mediatype': 
            case 'media': 
                return pathinfo($this->info('path'), PATHINFO_EXTENSION);
                break;
            
            case 'referer':
                return $this->env('HTTP_REFERER');
                break;
            
            case 'ua':
            case 'useragent':
            case 'client':
                return $this->env('HTTP_USER_AGENT');
                break;
            
            case 'ip': 
            case 'ipaddress':
            case 'remoteaddr': 
                return $this->env('REMOTE_ADDR');
                break;
            
            case 'proxyip': 
            case 'clientip': 
            case 'forwardedip': 
                if ($ip = $this->env('HTTP_CLIENT_IP')) {
                    return $ip;
                } elseif ($ip = $this->env('HTTP_X_FORWARDED_FOR')) {
                    return $ip;
                }
                return $this->info('ip');
                break;
            
            case 'routepath':
                $routepath = '/';
                $path = pathinfo($this->info('path'), PATHINFO_DIRNAME) . '/' . 
                        pathinfo($this->info('path'), PATHINFO_FILENAME);
                
                $base = $this->info('base_uri') ?: '/';
                if (strpos($path, $base) === 0 && $path !== $base) {
                    $routepath = substr($path, strlen($base));
                }
                return $routepath;
                break;
                
            default:
                return null;
                break;
        }
    }

    /* ______________________________________________________________________ */
    
    public function is($spec)
    {
        $spec = strtolower(trim($spec, '-_'));
        switch ($spec) {
            case 'ajax':
            case 'xhttprequest':
            case 'xmlhttprequest':
                return ($this->env('HTTP_X_REQUESTED_WITH') 
                    && strtolower($this->env('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
                break;
            
            case 'multipartformdata': 
            case 'multipart': 
                return (strpos($this->env('HTTP_ACCEPT'), 'multipart/form-data') 
                    OR strpos($this->env('HTTP_ACCEPT'), 'application/x-www-form-urlencoded'));
                break;

            case 'get': 
            case 'post': 
            case 'put': 
            case 'delete': 
            case 'header': 
                return ($this->env('REQUEST_METHOD') == strtoupper($spec));
                break;

            case 'secure': 
            case 'https': 
                return ($this->env('HTTPS') == 'on');
                break;

            case 'redirected':
                return (bool) $this->info('referer');
                break;

            default:
                if (isset($this->detectors[$spec])) {
                    return (bool) $this->detectors[$spec]($this);
                }
                return null;
                break;
        }
    }

    /* ______________________________________________________________________ */
    
    protected function filter($input, array $filters)
    {
        if( ! $filters) {
            return $input;
        }
        
        if (is_array($input)) {
            $filtered = array();
            foreach ($input as $key => $val) {
                $filtered[$key] = $this->filter($val, $filters);
            }
            return $filtered;
        } else {
            foreach ($filters as $filter) {
                if (is_callable($filter)) {
                    $input = call_user_func_array($filter, array($input));
                }
            }
            return $input;
        }
    }

    /* ______________________________________________________________________ */
    
    protected function input($key, $default, $input)
    {
        $input = (is_array($input)) ? $input : array();
        
        if (is_null($key)) {
            return $input;
        } elseif (isset($input[$key])) {
            return $input[$key];
        }
        
        return $default;
    }

    /* ______________________________________________________________________ */
    
    protected function detectBaseUri()
    {
        $file = basename($this->env('SCRIPT_FILENAME'));

        if (basename($this->env('SCRIPT_NAME')) === $file) {
            $base = $this->env('SCRIPT_NAME');
        } elseif (basename($this->env('PHP_SELF')) === $file) {
            $base = $this->env('PHP_SELF');
        } elseif (basename($this->env('ORIG_SCRIPT_NAME')) === $file) {
            $base = $this->env('ORIG_SCRIPT_NAME');
        } else {
            $path = $this->env('PHP_SELF');
            $file = $this->env('SCRIPT_FILENAME');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $last = count($segs);
            $base = '';
            $i    = 0;
            do {
                $seg    = $segs[$index];
                $base   = '/' . $seg . $base;
                ++$i;
            } while (($last > $i) && (false !== ($pos = strpos($path, $base))) && (0 != $pos));
        }

        // raw uri, remove prefix
        $raw = (strpos($this->getUri(), $this->info('prefix')) === 0) ? 
                substr($this->getUri(), strlen($this->info('prefix'))) : $this->getUri();

        // remove query string
        list($uri, ) = explode('?', $raw, 2) + array('', '');
        $basename = basename($base);

        $baseUri = '';
        switch (true) {
            case (0 === strpos($uri, $base)):
                $baseUri = rtrim($base, '/');
                break;

            case (0 === strpos($uri, dirname($base))):
                $baseUri = rtrim(dirname($base), '/');
                break;

            case (empty($basename) OR ! strpos($uri, $basename)):
                $baseUri = '';
                break;

            case ((strlen($uri) >= strlen($base)) 
                    && ((false !== ($pos = strpos($uri, $base))) 
                    && ($pos !== 0))):
                $baseUri = rtrim(substr($uri, 0, $pos + strlen($base)), '/');
                break;

            default:
                $baseUri = '';
                break;
        }
        return $baseUri;
    }
}