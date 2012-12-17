<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Cache\Adapter;

use SplDoublyLinkedList;
use Fuu\Storage\Strategy\StrategyInterface;

abstract class CacheAbstract
{
    const WRITE_MODE = SplDoublyLinkedList::IT_MODE_FIFO;
    const READ_MODE = SplDoublyLinkedList::IT_MODE_LIFO;

    protected $strategies;
    protected $config = array();

    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $this->config = $config;
        $this->init();
    }

    /* ______________________________________________________________________ */
    
    protected function init() {}

    /* ______________________________________________________________________ */
    
    public function getConfig($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    /* ______________________________________________________________________ */
    
    public function setConfig($key, $value = null)
    {
        $this->config[$key] = $value;
    }

    /* ______________________________________________________________________ */
    
    public function setStrategy($strategies)
    {
        $this->resetStrategy();
        if (is_array($strategies)) {
            foreach ($strategies as $strategy) {
                $this->addStrategy($strategy);
            }
        } else {
            $this->addStrategy($strategies);
        }
    }

    /* ______________________________________________________________________ */
    
    public function resetStrategy()
    {
        $this->strategies(true);
    }

    /* ______________________________________________________________________ */
    
    public function addStrategy(StrategyInterface $strategy)
    {
        $this->strategies()->push($strategy);
    }

    /* ______________________________________________________________________ */
    
    public function addStrategies(array $strategies)
    {
        foreach ($strategies as $$strategy) {
            $this->addStrategy($strategy);
        }
    }

    /* ______________________________________________________________________ */
    
    protected function strategies($reset = false)
    {
        if ( ! $this->strategies OR $reset) {
            $this->strategies = new SplDoublyLinkedList;
        }
        return $this->strategies;
    }

    /* ______________________________________________________________________ */
    
    protected function applyStrategies($method, $data, $mode = self::WRITE_MODE)
    {
        // Check if user haven't add any strategy. 
        // Don't need to instantiate strategies holder object
        if ( ! $this->strategies) {
            return $data;
        }
        
        if ( ! $this->strategies()->count()) {
            return $data;
        }
        
        $strategies = $this->strategies();
        $strategies->setIteratorMode($mode);
        
        foreach ($strategies as $strategy) {
            if (method_exists($strategy, $method)) {
                $data = $strategy->{$method}($data);
            }
        }
        return $data;
    }
}