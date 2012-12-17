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

use ArrayObject;
use InvalidArgumentException;
use Fuu\Stdlib\PriorityQueue;
use Fuu\Stdlib\CallbackHandler;

/**
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/EventManager/EventManager.php
 */
class Manager implements ManagerInterface
{
    protected $events = array();

    /* ______________________________________________________________________ */

    public function createTrigger($name, $target = null, $params = null, $callback = null)
    {
        if ($callback && ! is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback provided');
        }
        
        $event = new Event($name, $target, $params);
        return $this->triggerListeners($event->getName(), $event, $callback);
    }

    /* ______________________________________________________________________ */

    public function trigger(EventInterface $event, $callback = null)
    {
        if ($callback && ! is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback provided');
        }

        return $this->triggerListeners($event->getName(), $event, $callback);
    }

    /* ______________________________________________________________________ */

    public function attach($event, $callback = null, $priority = 1)
    {
        if ( ! is_callable($callback)) {
            throw new InvalidArgumentException(sprintf('%s: expects args #2 to be callable.', __METHOD__));
        }

        if (is_array($event)) {
            $listeners = array();
            foreach ($event as $name) {
                $listeners[] = $this->attach($name, $callback, $priority);
            }
            return $listeners;
        }

        if (empty($this->events[$event])) {
            $this->events[$event] = new PriorityQueue;
        }

        $listener = new CallbackHandler($callback, compact('event', 'priority'));
        $this->events[$event]->insert($listener, $priority);
        return $listener;
    }

    /* ______________________________________________________________________ */

    public function detach($listener)
    {
        if ( ! $listener instanceof CallbackHandler) {
            $type = (is_object($listener) ? get_class($listener) : gettype($listener));
            throw new InvalidArgumentException(sprintf(
                '%s: expected a CallbackHandler, `%s` given.', __METHOD__, $type
            ));
        }

        $event = $listener->getMetadatum('event');
        if ( ! $event OR empty($this->events[$event])) {
            return false;
        }
        $return = $this->events[$event]->remove($listener);
        if ( ! $return) {
            return false;
        }
        if ( ! count($this->events[$event])) {
            unset($this->events[$event]);
        }
        return true;
    }

    /* ______________________________________________________________________ */

    public function getEvents()
    {
        return array_keys($this->events);
    }

    /* ______________________________________________________________________ */

    public function getListeners($event)
    {
        if ( ! array_key_exists($event, $this->events)) {
            return new PriorityQueue;
        }
        return $this->events[$event];
    }

    /* ______________________________________________________________________ */

    public function clearListeners($event)
    {
        if ( ! empty($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    /* ______________________________________________________________________ */

    public function prepareArgs(array $args)
    {
        return new ArrayObject($args);
    }

    /* ______________________________________________________________________ */

    protected function triggerListeners($event, EventInterface $e, $callback = null)
    {
        $responses = new ResponseCollection;
        $listeners = $this->getListeners($event);

        if ($listeners->isEmpty()) {
            return $responses;
        }

        foreach ($listeners as $listener) {
            $responses->push(call_user_func($listener->getCallback(), $e));

            if ($e->propagationIsStopped()) {
                $responses->setStopped(true);
                break;
            }

            if ($callback && call_user_func($callback, $responses->last())) {
                $responses->setStopped(true);
                break;
            }
        }

        return $responses;
    }
}