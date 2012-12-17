<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Action;

use Fuu\Http\RequestServerInterface;

interface RequestActionInterface extends RequestServerInterface
{
    public function getParams();
    public function setParams(array $params);
    public function getParam($name, $default = null);
    public function setParam($name, $value = null);
    public function bindParam($index, $name, $filters = array());
    public function getSegments();
    public function setSegments(array $segments);
    public function getSegment($index, $flag = null);
    public function setSegment($index, $value = null, $flag = null);
    public function getModule();
    public function setModule($module);
    public function getController();
    public function setController($controller);
    public function getAction();
    public function setAction($action);
}