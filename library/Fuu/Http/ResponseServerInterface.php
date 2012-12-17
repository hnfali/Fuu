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

interface ResponseServerInterface
{
    public function getVersion();
    public function setVersion($version);
    public function getProtocol();
    public function setProtocol($protocol);
    public function setHeader($name, $value, $replace = false);
    public function getHeaders();
    public function resetHeaders();
    public function unsetHeader($name);
    public function setResponseCode($code);
    public function getResponseCode();
    public function setRedirect($url, $code = 302);
    public function isRedirect();
    public function headerSent($exception = true);
    public function sendHeaders();
    public function setBody($content, $name = null);
    public function appendBody($content, $name = null);
    public function unsetBody($name = null);
    public function getBody($spec = false);
    public function outputBody();
    public function send();
    public function buffer();
    public function __toString();
}