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

interface ManagerInterface 
{
    public function createTrigger($name, $target = null, $params = null, $callback = null);
    public function trigger(EventInterface $event, $callback = null);
    public function attach($event, $callback = null, $priority = 1);
    public function detach($listener);
    public function getEvents();
    public function getListeners($event);
    public function clearListeners($event);
}