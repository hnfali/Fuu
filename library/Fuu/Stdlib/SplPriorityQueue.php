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

use Serializable;
use SplPriorityQueue as NativeSplPriorityQueue;

/**
 * Serializable version of SplPriorityQueue
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 * 
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/SplPriorityQueue.php
 */
class SplPriorityQueue extends NativeSplPriorityQueue implements Serializable
{
    protected $serial = PHP_INT_MAX;

    /* ______________________________________________________________________ */
    
    public function insert($datum, $priority)
    {
        if ( ! is_array($priority)) {
            $priority = array($priority, $this->serial--);
        }
        parent::insert($datum, $priority);
    }

    /* ______________________________________________________________________ */
    
    public function toArray()
    {
        $this->setExtractFlags(self::EXTR_BOTH);
        $array = array();
        while ($this->valid()) {
            $array[] = $this->current();
            $this->next();
        }
        $this->setExtractFlags(self::EXTR_DATA);

        foreach ($array as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        $return = array();
        foreach ($array as $item) {
            $return[] = $item['data'];
        }

        return $return;
    }

    /* ______________________________________________________________________ */
    
    public function serialize()
    {
        $data = array();
        $this->setExtractFlags(self::EXTR_BOTH);
        while ($this->valid()) {
            $data[] = $this->current();
            $this->next();
        }
        $this->setExtractFlags(self::EXTR_DATA);

        foreach ($data as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        return serialize($data);
    }

    /* ______________________________________________________________________ */
    
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }
}