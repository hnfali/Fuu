<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_View
 */

namespace Fuu\View\Renderer;

interface RendererInterface
{
    public function getFileExtension();
    public function render($template, array $data = array(), $type = 'file');
}