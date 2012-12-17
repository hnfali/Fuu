<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Mvc
 */

namespace Fuu\Mvc;

use Fuu\Stdlib\Config as StdlibConfig;

class Config extends StdlibConfig implements ApplicationConfigInterface
{
    protected $required = array(
        'app_path', 'app_namespace', 
        'public_path', 'environment'
    );
    
    /* ______________________________________________________________________ */

    public function __construct(array $data = array())
    {
        $this->validate($data);

        $defaults = array(
            'autoload'            => array(),
            'routes'              => array(),
            'bootstrap'           => null,
            'view_renderer'       => 'Twig',
            'fuu_path'            => dirname(__DIR__),
            'library_path'        => dirname(dirname(__DIR__)),
            'template_dir'        => 'Templates',
            'layout_dir'          => 'Layouts',
            'module_dir'          => 'Modules',
            'view_dir'            => 'Views',
            'cache_dir'           => 'var/cache',
            'log_dir'             => 'var/logs',
            'tmp_dir'             => 'tmp',
            'annotation'          => array(),
        );
        parent::__construct($data + $defaults);
    }

    /* ______________________________________________________________________ */
    
    protected function validate(array $data) {
        if (count(array_intersect(array_keys($data), $this->required)) !== 4) {
            $message = 'The following config keys are required: ' . implode(', ', $this->required);
            throw new Exception\ConfigException($message);
        }
    }
}