<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Stdlib
 */

namespace Fuu\Stdlib;

use Countable;
use IteratorAggregate;
use Serializable;
use DomainException;
use SplPriorityQueue;

/**
 * Re-usable, serializable priority queue implementation
 *
 * SplPriorityQueue acts as a heap; on iteration, each item is removed from the
 * queue. If you wish to re-use such a queue, you need to clone it first. This
 * makes for some interesting issues if you wish to delete items from the queue,
 * or, as already stated, iterate over it multiple times.
 *
 * This class aggregates items for the queue itself, but also composes an
 * "inner" iterator in the form of an SplPriorityQueue object for performing
 * the actual iteration.
 * 
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/PriorityQueue.php
 */
class PriorityQueue implements Countable, IteratorAggregate, Serializable
{
    const EXTR_DATA     = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH     = 0x00000003;

    protected $queueClass = 'Fuu\Stdlib\SplPriorityQueue';
    protected $items = array();
    protected $queue;

    /* ______________________________________________________________________ */
    
    public function insert($data, $priority = 1)
    {
        $priority = (int) $priority;
        $this->items[] = array(
            'data'     => $data,
            'priority' => $priority,
        );
        $this->getQueue()->insert($data, $priority);
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function remove($datum)
    {
        $found = false;
        foreach ($this->items as $key => $item) {
            if ($item['data'] === $datum) {
                $found = true;
                break;
            }
        }
        if ($found) {
            unset($this->items[$key]);
            $this->queue = null;
            $queue = $this->getQueue();
            foreach ($this->items as $item) {
                $queue->insert($item['data'], $item['priority']);
            }
            return true;
        }
        return false;
    }

    /* ______________________________________________________________________ */
    
    public function isEmpty()
    {
        return (0 === $this->count());
    }

    /* ______________________________________________________________________ */
    
    public function count()
    {
        return count($this->items);
    }

    /* ______________________________________________________________________ */
    
    public function top()
    {
        return $this->getIterator()->top();
    }

    /* ______________________________________________________________________ */
    
    public function extract()
    {
        return $this->getQueue()->extract();
    }

    /* ______________________________________________________________________ */
    
    public function getIterator()
    {
        $queue = $this->getQueue();
        return clone $queue;
    }

    /* ______________________________________________________________________ */
    
    public function serialize()
    {
        return serialize($this->items);
    }

    /* ______________________________________________________________________ */
    
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }

    /* ______________________________________________________________________ */
    
    public function toArray($flag = self::EXTR_DATA)
    {
        switch ($flag) {
            case self::EXTR_BOTH:
                return $this->items;
                break;

            case self::EXTR_PRIORITY:
                return array_map(function($item) {
                    return $item['priority'];
                }, $this->items);

            case self::EXTR_DATA:
            default:
                return array_map(function($item) {
                    return $item['data'];
                }, $this->items);
        }
    }

    /* ______________________________________________________________________ */
    
    public function setInternalQueueClass($class)
    {
        $this->queueClass = (string) $class;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function contains($datum)
    {
        foreach ($this->items as $item) {
            if ($item['data'] === $datum) {
                return true;
            }
        }
        return false;
    }

    /* ______________________________________________________________________ */
    
    public function hasPriority($priority)
    {
        foreach ($this->items as $item) {
            if ($item['priority'] === $priority) {
                return true;
            }
        }
        return false;
    }

    /* ______________________________________________________________________ */
    
    protected function getQueue()
    {
        if (null === $this->queue) {
            $this->queue = new $this->queueClass();
            if ( ! $this->queue instanceof SplPriorityQueue) {
                throw new DomainException(sprintf(
                    'PriorityQueue expects an internal queue of type SplPriorityQueue, `%s` given.', 
                    get_class($this->queue)
                ));
            }
        }
        return $this->queue;
    }
}