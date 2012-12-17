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

use ArrayAccess;
use Countable;
use Serializable;
use Traversable;

interface ParametersInterface extends ArrayAccess, Countable, Serializable, Traversable
{
    public function __construct(array $values = null);
    public function fromArray(array $values);
    public function fromString($string);
    public function toArray();
    public function toString();
    public function get($name, $default = null);
    public function set($name, $value);
}