<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Event
 */

namespace Fuu\Event;

interface EventInterface 
{
    public function getName();
    public function getTarget();
    public function getParams();
    public function getParam($name, $default = null);
    public function getResponse();
    public function setName($name);
    public function setTarget($target);
    public function setParams($params);
    public function setParam($name, $value);
    public function setResponse($value);
    public function stopPropagation($flag = true);
    public function propagationIsStopped();
}
