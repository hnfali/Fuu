<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Plugin;

use Countable;
use RuntimeException;

class Benchmark implements Countable
{
    private $marker = array();
    private $data = array();
    private $index = array();
    private $start;
    private $stop;

    /* ______________________________________________________________________ */

    public function __construct($marker = 'init')
    {
        $this->start = $marker;
        $this->stop = $marker;
        $this->mark($marker);
    }

    /* ______________________________________________________________________ */

    public function mark()
    {
        static $index = 0;
        $time = microtime(true);
        $points = func_get_args();
        foreach ($points as $point) {
            if ( ! isset($this->marker[$point])) {
                $this->marker[$point] = $time;
                $this->stop = $point;
                $this->index[$index++] = $point;
            } else {
                throw new RuntimeException("Benchmark point `{$point}` is already registered.");
            }
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function count()
    {
        return count($this->marker);
    }

    /* ______________________________________________________________________ */

    public function data()
    {
        if ($this->data) {
            return $this->data;
        }
        
        $prev;
        $data = array();
        foreach ($this->marker as $key => $value) {
            if ( ! isset($prev)) {
                $prev = $value;
            }
            $data[$key] = number_format($value - $prev, 4);
            $prev = $value;
        }
        
        foreach ($this->index as $key => $value) {
            $this->data[$value] = $data[$value];
        }
        
        return $this->data;
    }

    /* ______________________________________________________________________ */

    public function dump()
    {
        var_dump($this->data());
    }

    /* ______________________________________________________________________ */

    public function from($marker)
    {
        if (isset($this->marker[$marker])) {
            $this->start = $marker;
        } else {
            throw new RuntimeException("Benchmark marker: `{$marker}` not found.");
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function to($marker)
    {
        if (isset($this->marker[$marker])) {
            $this->stop = $marker;
        } else {
            throw new RuntimeException("Benchmark marker: `{$marker}` not found.");
        }
        return $this;
    }

    /* ______________________________________________________________________ */

    public function calculate()
    {
        return number_format($this->marker[$this->stop] - $this->marker[$this->start], 4);
    }
}
