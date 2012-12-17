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

interface RequestServerInterface
{
    public function getUri();
    public function getUrl();
    public function env($key = null, $default = null, array $filters = array());
    public function cookie($key = null, $default = null, array $filters = array());
    public function session($key = null, $default = null, array $filters = array());
    public function files($key = null, $default = null, array $filters = array());
    public function get($key = null, $default = null, array $filters = array());
    public function options($key = null, $default = null, array $filters = array());
    public function head($key = null, $default = null, array $filters = array());
    public function post($key = null, $default = null, array $filters = array());
    public function put($key = null, $default = null, array $filters = array());
    public function delete($key = null, $default = null, array $filters = array());
    public function trace($key = null, $default = null, array $filters = array());
    public function connect($key = null, $default = null, array $filters = array());
    public function patch($key = null, $default = null, array $filters = array());
    public function info($name);
    public function is($spec);
    public function __toString();
    public function setDetector($spec, $callable);
}