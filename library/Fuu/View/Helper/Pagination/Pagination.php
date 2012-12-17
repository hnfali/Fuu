<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_View
 */

namespace Fuu\View\Helper\Pagination;

use Fuu\Mvc\Action\RequestActionInterface;
use Fuu\Mvc\Exception\ConfigException;

class Pagination
{
    protected $renderer;
    protected $request;
    protected $config = array();

    /* ______________________________________________________________________ */
    
    public function __construct(RequestActionInterface $request, array $config = array())
    {
        $defaults = array(
            'title_format'  => 'Page {:page}',
            'first_label'   => 'First',
            'last_label'    => 'Last',
            'prev_label'    => 'Prev',
            'next_label'    => 'Next',
            'per_page'      => 10,
            'num_blocks'    => 5,
            'total_rows'    => 0,
            'param_name'    => null,
            'segment_index' => 4
        );
        $this->config = $config + $defaults;
        $this->request = $request;
        $this->init();
    }

    /* ______________________________________________________________________ */
    
    protected function init()
    {
        $keys = array('per_page', 'num_blocks', 'total_rows', 'segment_index');
        foreach ($keys as $key) {
            if ( ! is_int($this->config[$key])) {
                throw new ConfigException(__CLASS__ . ': `' . $key . '` config value expects to be integer.');
            }
            $this->config[$key] = abs($this->config[$key]);
        }
    }

    /* ______________________________________________________________________ */
    
    public function __toString()
    {
        return (string) $this->render();
    }

    /* ______________________________________________________________________ */
    
    public function setRenderer(renderer\RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /* ______________________________________________________________________ */
    
    public function getRenderer()
    {
        if ( ! ($this->renderer instanceof renderer\RendererInterface)) {
            $this->renderer = new renderer\DefaultRenderer;
        }
        return $this->renderer;
    }

    /* ______________________________________________________________________ */
    
    public function render()
    {
        return $this->getRenderer()->render($this);
    }

    /* ______________________________________________________________________ */
    
    public function hasFirstLink()
    {
        if ( ! $this->config['first_label']) {
            return false;
        }
        return ($this->currentPage() > ($this->config['num_blocks'] + 1));
    }

    /* ______________________________________________________________________ */
    
    public function hasLastLink()
    {
        if ( ! $this->config['last_label']) {
            return false;
        }
        return (($this->currentPage() + $this->config['num_blocks']) < $this->numPages());
    }

    /* ______________________________________________________________________ */
    
    public function hasPrevLink()
    {
        if ( ! $this->config['prev_label']) {
            return false;
        }
        return ($this->currentPage() != 1);
    }

    /* ______________________________________________________________________ */
    
    public function hasNextLink()
    {
        if ( ! $this->config['next_label']) {
            return false;
        }
        return ($this->currentPage() < $this->numPages());
    }

    /* ______________________________________________________________________ */
    
    public function getStartingBlock()
    {
        return (($this->currentPage() - $this->config['num_blocks']) > 0) ? 
            $this->currentPage() - ($this->config['num_blocks'] - 1) : 1;
    }

    /* ______________________________________________________________________ */
    
    public function getEndingBlock()
    {
        return (($this->currentPage() + $this->config['num_blocks']) < $this->numPages()) ? 
            $this->currentPage() + $this->config['num_blocks'] : $this->numPages();
    }

    /* ______________________________________________________________________ */
    
    public function numPages()
    {
        return (int) @ ceil($this->config['total_rows']/$this->config['per_page']);
    }

    /* ______________________________________________________________________ */
    
    public function currentPage()
    {
        if ( ! ($index = $this->config['param_name'])) {
            if ( ! ($index = $this->config['segment_index'])) {
                throw new ConfigException(__METHOD__ . ': Neither `param_name` or `segment_index` is set.');
            }
            $page = $this->request->getSegment($index);
        }
        
        if ( ! isset($page)) {
            $page = $this->request->getParam($index);
        }
        
        return $this->translateToPageIndex($page);
    }

    /* ______________________________________________________________________ */
    
    protected function translateToPageIndex($name)
    {
        switch (true) {
            case is_int($name):
                $page = ($name < 1) ? 1 : $name;
                if ((($page - 1) * $this->config['per_page']) > $this->config['total_rows']) {
                    $page = $this->numPages();
                }
                return $page;
                break;

            case strtolower($name) === 'first':
                return 1;
                break;

            case strtolower($name) === 'last':
                return $this->numPages();
                break;

            case strtolower($name) === 'half':
            case strtolower($name) === 'moddle':
                return floor($this->numPages()/2) + 1;
                break;

            default:
                return 1;
                break;
        }
    }
}