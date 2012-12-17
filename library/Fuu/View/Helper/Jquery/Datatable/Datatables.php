<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_View
 */

namespace Fuu\View\Helper\Jquery\Datatable;

class Datatables
{
    protected $config;
    protected $renderer;

    /* ______________________________________________________________________ */
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /* ______________________________________________________________________ */
    
    public function getRenderer()
    {
        if ( ! ($this->renderer instanceof Renderer\RendererInterface)) {
            $this->renderer = new Renderer\DefaultRenderer($this->config);
        }
        return $this->renderer;
    }

    /* ______________________________________________________________________ */
    
    public function setRenderer(Renderer\RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /* ______________________________________________________________________ */
    
    public function render(Schema $schema)
    {
        $this->getRenderer()->render($schema);
    }
}