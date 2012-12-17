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

use Fuu\Mvc\Action\ControllerInterface;

interface FilterInterface extends TagInterface
{
    public function filter(ControllerInterface $controller);
}