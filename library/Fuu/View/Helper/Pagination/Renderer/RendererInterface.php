<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_View
 */

namespace Fuu\View\Helper\Pagination\Renderer;

use Fuu\View\Helper\Pagination\Pagination;

interface RendererInterface
{
    public function __construct(array $config = array());
    public function render(Pagination $pagination);
}