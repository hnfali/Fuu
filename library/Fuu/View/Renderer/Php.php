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

use Fuu\Stdlib\String;
use InvalidArgumentException;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Mvc\Exception\ViewException;

class Php implements RendererInterface
{
    const FILE_EXTENSION = '.php';
    const CONFIG_NAME = 'php_renderer';
    const DEV_MODE = 'development';
    
    protected $config = array();

    /* ______________________________________________________________________ */
    
    public function __construct($config = array())
    {
        $defaults = array(
            'debug'      => false, // not implemented
            'filters'    => array(), // filters final output
            'autoescape' => true // not implemented.
        );
        
        if ($config instanceof ApplicationConfigInterface) {
            $this->config = (array) $config->{self::CONFIG_NAME} + $defaults;
            if (isset($config->environment) && $config->environment == self::DEV_MODE) {
                $this->config['debug'] = true;
            }
        } else {
            $this->config = (array) $config + $defaults;
        }
    }
    
    /* ______________________________________________________________________ */
    
    public function getFileExtension()
    {
        return self::FILE_EXTENSION;
    }
    
    /* ______________________________________________________________________ */
    
    public function renderFile($template, array $data = array())
    {
        if (is_file($template)) {
            ob_start();
            extract($data, EXTR_SKIP);
            include $template;
            return $this->applyFilters(ob_get_clean());
        }
        
        throw new ViewException('Template not found: ' . pathinfo($template, PATHINFO_BASENAME));
    }
    
    /* ______________________________________________________________________ */
    
    public function renderString($string, array $data = array())
    {
        return String::insert($string, $data);
    }
    
    /* ______________________________________________________________________ */
    
    public function render($template, array $data = array(), $type = 'file')
    {
        switch ($type) {
            case 'file':
                return $this->renderFile($template, $data);
                break;

            case 'string':
                return $this->renderString($template, $data);
                break;
        }
        
        throw new InvalidArgumentException('Unsupported type to render: ' . $type);
    }

    /* ______________________________________________________________________ */
    
    protected function applyFilters($output) {
        foreach ( (array) $this->config['filters'] as $filter) {
            if (is_callable($filter)) {
                $output = $filter($output);
            }
        }
        return $output;
    }
}