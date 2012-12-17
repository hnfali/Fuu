<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Session\Adapter;

use Fuu\Storage\Strategy\StrategyInterface;

interface AdapterInterface extends OperationInterface
{
    public function __construct(array $config = array());
    public function getConfig($key, $default = null);
    public function setConfig($key, $value = null);
    public function setStrategy($strategies);
    public function resetStrategy();
    public function addStrategy(StrategyInterface $strategy);
    public function addStrategies(array $strategies);
    public function isStarted();
}