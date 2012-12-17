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

interface OperationInterface
{
    public function check($key);
    public function read($key = null, array $options = array());
    public function write($key, $value = null, array $options = array());
    public function delete($key, array $options = array());
    public function destroy(array $options = array());
}