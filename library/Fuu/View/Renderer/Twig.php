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

use RuntimeException;
use InvalidArgumentException;
use Twig_Autoloader;
use Twig_Loader_Array;
use Twig_Loader_String;
use Twig_Loader_Filesystem;
use Twig_Extension_Debug;
use Twig_Environment;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Mvc\Exception\ConfigException;
use Fuu\Mvc\Exception\ViewException;

class Twig implements RendererInterface 
{
    const FILE_EXTENSION = '.twig';
    const CONFIG_NAME = 'twig_renderer';
    const DEV_MODE = 'development';
    
    protected $config = array();
    protected $twigEnvironment;
    protected $twigLoaderArray;
    protected $twigLoaderString;
    protected $twigLoaderFilesystem;
    protected $searchPaths = array();

    /* ______________________________________________________________________ */
    
    public function __construct($config = array())
    {
        $defaults = array(
            'autoloader'            => null,
            'debug'                 => false,
            'auto_search_templates' => true,
            'preserve_extension'    => true,
            'charset'               => 'utf-8',
            'base_template_class'   => 'Twig_Template',
            'strict_variables'      => false,
            'autoescape'            => true,
            'cache'                 => false,
            'auto_reload'           => null,
            'optimizations'         => -1
        );
        
        if ($config instanceof ApplicationConfigInterface) {
            $this->config = (array) $config->{self::CONFIG_NAME} + $defaults;
            if (isset($config->environment) && $config->environment == self::DEV_MODE) {
                $this->config['debug'] = true;
            }
            
            // auto search template paths
            if ($this->config['auto_search_templates']) {
                $this->addSearchTemplatePaths($config);
            }
        } else {
            $this->config = (array) $config + $defaults;
        }
        
        if ( ! class_exists('Twig_Environment')) {
            if ($this->config['autoloader'] && file_exists($this->config['autoloader'])) {
                require_once $this->config['autoloader'];
                Twig_Autoloader::register();
                return;
            }
            throw new RuntimeException('Twig engine is not loaded.');
        }
    }
    
    /* ______________________________________________________________________ */
    
    public function getFileExtension()
    {
        return self::FILE_EXTENSION;
    }
    
    /* ______________________________________________________________________ */
    
    public function renderString($string, array $data = array())
    {
        $this->getEngine()->setLoader($this->getLoaderString());
        return $this->getEngine()->render($string, $data);
    }
    
    /* ______________________________________________________________________ */
    
    public function renderArray($template, array $data = array())
    {
        $this->getEngine()->setLoader($this->getLoaderArray());
        return $this->getEngine()->render($template, $data);
    }
    
    /* ______________________________________________________________________ */
    
    public function renderFile($file, array $data = array())
    {
        if ($this->config['preserve_extension']) {
            $file = $this->preserveExtension($file);
        }
        
        if (is_file($file)) {
            $dir = dirname($file);
            $file = basename($file);
            
            if ($this->searchPaths) {
                $this->getLoaderFilesystem()->setPaths($this->searchPaths);
            }
            
            $this->getLoaderFilesystem()->addPath($dir);
            $this->getEngine()->setLoader($this->getLoaderFilesystem());
            $template = $this->getEngine()->render($file, $data);
            $this->getLoaderFilesystem()->setPaths(array());
            return $template;
        }
        
        throw new ViewException('Template not found: ' . pathinfo($file, PATHINFO_BASENAME));
    }
    
    /* ______________________________________________________________________ */
    
    public function render($template, array $data = array(), $type = 'file')
    {
        switch (strtolower($type)) {
            case 'file':
                return $this->renderFile($template, $data);
                break;

            case 'str':
            case 'string':
                return $this->renderString($template, $data);
                break;

            case 'array':
                return $this->renderArray($template, $data);
                break;

            default:
                throw new InvalidArgumentException('Unsupported type to render: ' . $type);
                break;
        }
    }
    
    /* ______________________________________________________________________ */
    
    public function getLoaderArray()
    {
        if ( ! $this->twigLoaderArray) {
            $this->twigLoaderArray = new Twig_Loader_Array(array());
        }
        return $this->twigLoaderArray;
    }
    
    /* ______________________________________________________________________ */
    
    public function getLoaderFilesystem()
    {
        if ( ! $this->twigLoaderFilesystem) {
            $this->twigLoaderFilesystem = new Twig_Loader_Filesystem(array());
        }
        return $this->twigLoaderFilesystem;
    }
    
    /* ______________________________________________________________________ */
    
    public function getLoaderString()
    {
        if ( ! $this->twigLoaderString) {
            $this->twigLoaderString = new Twig_Loader_String;
        }
        return $this->twigLoaderString;
    }
    
    /* ______________________________________________________________________ */
    
    protected function getEngine()
    {
        if ( ! ($this->twigEnvironment instanceof Twig_Environment)) {
            $this->twigEnvironment = new Twig_Environment(null, $this->config);
            if ($this->config['debug']) {
                $this->twigEnvironment->addExtension(new Twig_Extension_Debug());
            }
        }
        return $this->twigEnvironment;
    }
    
    /* ______________________________________________________________________ */
    
    protected function addSearchTemplatePaths(ApplicationConfigInterface $config)
    {
        $paths = array(
            $config->app_path . '/' . $config->template_dir,
            $config->app_path . '/' . $config->template_dir . '/' . $config->layout_dir,
            $config->app_path . '/' . $config->view_dir . '/' . $config->template_dir,
            $config->app_path . '/' . $config->view_dir . '/' . $config->template_dir . '/' . $config->layout_dir
        );

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $this->searchPaths[] = $path;
            }
        }
    }
    
    /* ______________________________________________________________________ */
    
    protected function preserveExtension($file)
    {
        if ( ! substr($file, -strlen(self::FILE_EXTENSION)) == self::FILE_EXTENSION) {
            return $file . self::FILE_EXTENSION;
        }
        return $file;
    }
}