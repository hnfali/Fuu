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

interface OperationInterface
{
    public function write($key, $data, $expiry = null);
    public function read($key);
    public function delete($key);
    public function decrement($key, $offset = 1);
    public function increment($key, $offset = 1);
    public function flush();
}