<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc\Action\Annotation\Tag;

interface TagInterface
{
    public static function getTagName();
    public function __construct(array $args);
    public function __isset($key);
    public function __get($key);
    public function __toString();
    public function toArray();
}