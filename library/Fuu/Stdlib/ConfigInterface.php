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
use ArrayAccess;
use Serializable;

interface ConfigInterface extends Countable, ArrayAccess, Serializable
{
    public function __get($key);
    public function __set($key, $value);
    public function __isset($key);
    public function __unset($key);
    public function toArray();
    public function setDefaults(array $data);
    public function markAsReadOnly($key);
}