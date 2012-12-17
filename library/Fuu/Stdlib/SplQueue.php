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
use SplQueue as NativeSplQueue;

/**
 * Serializable version of SplQueue
 * 
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/SplQueue.php
 */
class SplQueue extends NativeSplQueue implements Serializable
{
    /* ______________________________________________________________________ */
    
    public function toArray()
    {
        $array = array();
        foreach ($this as $item) {
            $array[] = $item;
        }
        return $array;
    }

    /* ______________________________________________________________________ */
    
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /* ______________________________________________________________________ */
    
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->push($item);
        }
    }
}