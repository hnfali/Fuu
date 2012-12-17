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

use SplStack;

/**
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/EventManager/ResponseCollection.php
 */
class ResponseCollection extends SplStack
{
    protected $stopped = false;

    /* ______________________________________________________________________ */
    
    public function stopped()
    {
        return $this->stopped;
    }

    /* ______________________________________________________________________ */
    
    public function setStopped($flag)
    {
        $this->stopped = (bool) $flag;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function first()
    {
        return parent::bottom();
    }

    /* ______________________________________________________________________ */
    
    public function last()
    {
        if (count($this) === 0) {
            return null;
        }
        return parent::top();
    }

    /* ______________________________________________________________________ */
    
    public function contains($value)
    {
        foreach ($this as $response) {
            if ($response === $value) {
                return true;
            }
        }
        return false;
    }
}