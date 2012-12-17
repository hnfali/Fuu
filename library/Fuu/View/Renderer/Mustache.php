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
use Mustache_Engine;
use Mustache_Loader_ArrayLoader;
use Mustache_Loader_StringLoader;
use Mustache_Loader_FilesystemLoader;
use Fuu\Mvc\ApplicationConfigInterface;
use Fuu\Mvc\Exception\ViewException;

class Mustache implements RendererInterface
{
    const FILE_EXTENSION = '.mustache';
    const CONFIG_NAME = 'mustache_renderer';
    const DEV_MODE = 'development';
    
    protected $engine;
    protected $loaderArray;
    protected $loaderString;
    protected $loaderFilesystem;
    protected $config = array();

    /* ______________________________________________________________________ */
    
    public function __construct($config = array())
    {
        $defaults = array(
            'autoloader'            => null,
            'template_class_prefix' => '__Mustache_',
            'cache'                 => null,
            'loader'                => null,
            'partials_loader'       => null, 
            'helpers'               => array(),
            'escape'                => function($value) {
                                           return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                                       },
            'charset'               => 'ISO-8859-1',
        );
        
        if ($config instanceof ApplicationConfigInterface) {
            $this->config = (array) $config->{self::CONFIG_NAME} + $defaults;
        } else {
            $this->config = (array) $config + $defaults;
        }
        
        if ( ! class_exists('Mustache_Engine')) {
            if ($this->config['autoloader'] && file_exists($this->config['autoloader'])) {
                require_once $this->config['autoloader'];
                Mustache_Autoloader::register();
                return;
            }
            throw new RuntimeException('Mustache engine is not loaded.');
        }
    }
    
    /* ______________________________________________________________________ */
    
    public function getFileExtension()
    {
        return self::FILE_EXTENSION;
    }
    
    /* ______________________________________________________________________ */
    
    public function getEngine()
    {
        if ( ! ($this->engine instanceof Mustache_Engine)) {
            $this->engine = new Mustache_Engine($this->config);
        }
        return $this->engine;
    }
    
    /* ______________________________________________________________________ */
    
    public function renderString($string, array $data = array())
    {
        $this->getEngine()->setLoader($this->getLoaderString());
        $template = $this->getEngine()->loadTemplate($string);
        return $template->render($data);
    }
    
    /* ______________________________________________________________________ */
    
    public function renderArray($template, array $data = array())
    {
        $this->getEngine()->setLoader($this->getLoaderArray());
        $template = $this->getEngine()->loadTemplate($template);
        return $template->render($data);
    }
    
    /* ______________________________________________________________________ */
    
    public function renderFile($file, array $data = array())
    {
        $file = $this->addExtension($file);
        if (is_file($file)) {
            $dir = dirname($file);
            $file = basename($file);
            
            $this->getEngine()->setLoader($this->getLoaderFilesystem($dir));
            $template = $this->getEngine()->loadTemplate($file);
            return $template->render($data);
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
    
    public function getLoaderArray(array $templates = array())
    {
        if ( ! $this->loaderArray) {
            $this->loaderArray = new Mustache_Loader_ArrayLoader($templates);
        }
        return $this->loaderArray;
    }
    
    /* ______________________________________________________________________ */
    
    public function getLoaderFilesystem($basedir)
    {
        if ( ! $this->loaderFilesystem) {
            $options = array(
                'extension' => self::FILE_EXTENSION
            );
            $this->loaderFilesystem = new Mustache_Loader_FilesystemLoader($basedir, $options);
        }
        return $this->loaderFilesystem;
    }
    
    /* ______________________________________________________________________ */
    
    public function getLoaderString()
    {
        if ( ! $this->loaderString) {
            $this->loaderString = new Mustache_Loader_StringLoader;
        }
        return $this->loaderString;
    }
    
    /* ______________________________________________________________________ */
    
    protected function addExtension($file)
    {
        if ( ! substr($file, -strlen(self::FILE_EXTENSION)) == self::FILE_EXTENSION) {
            return $file . self::FILE_EXTENSION;
        }
        return $file;
    }
}