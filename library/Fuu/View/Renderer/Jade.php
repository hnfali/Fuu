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
use Everzet\Jade\Jade as JadeCore;
use Everzet\Jade\Parser;
use Everzet\Jade\Dumper\PHPDumper;
use Everzet\Jade\Dumper\DumperInterface;
use Everzet\Jade\Lexer\Lexer;
use Everzet\Jade\Lexer\LexerInterface;
use Everzet\Jade\Filter\JavaScriptFilter;
use Everzet\Jade\Filter\CDATAFilter;
use Everzet\Jade\Filter\PHPFilter;
use Everzet\Jade\Filter\CSSFilter;
use Everzet\Jade\Visitor\AutotagsVisitor;
use Fuu\Stdlib\String;

class Jade implements RendererInterface
{
    const FILE_EXTENSION = '.jade';
    const CONFIG_NAME = 'jade_renderer';
    const DEV_MODE = 'development';
    
    protected $jade;
    protected $dumper;
    protected $lexer;
    protected $config = array();

    /* ______________________________________________________________________ */
    
    public function __construct()
    {
        if ( ! class_exists('Everzet\\Jade\\Jade')) {
            throw new RuntimeException('Jade is not loaded. Please register Jade in your autoloader.');
        }
    }
    
    /* ______________________________________________________________________ */
    
    public function getFileExtension()
    {
        return self::FILE_EXTENSION;
    }
    
    /* ______________________________________________________________________ */
    
    public function render($template, array $data = array(), $type = 'file')
    {
        $output = $this->getJade()->render($template);
        return String::insert($output, $data);
    }

    /* ______________________________________________________________________ */
    
    public function getJade()
    {
        if ( ! ($this->jade instanceof JadeCore)) {
            $this->jade = new JadeCore(new Parser($this->getLexer()), $this->getDumper());
        }
        return $this->jade;
    }

    /* ______________________________________________________________________ */
    
    public function getDumper() {
        if ( ! ($this->dumper instanceof DumperInterface)) {
            $this->dumper = new PHPDumper;
            $this->dumper->registerVisitor('tag', new AutotagsVisitor);
            $this->dumper->registerFilter('javascript', new JavaScriptFilter);
            $this->dumper->registerFilter('cdata', new CDATAFilter);
            $this->dumper->registerFilter('php', new PHPFilter);
            $this->dumper->registerFilter('style', new CSSFilter);
        }
        return $this->dumper;
    }

    /* ______________________________________________________________________ */
    
    public function setDumper(DumperInterface $dumper) {
        $this->dumper = $dumper;
    }

    /* ______________________________________________________________________ */
    
    public function getLexer() {
        if ( ! ($this->lexer instanceof LexerInterface)) {
            $this->lexer = new Lexer;
        }
        return $this->lexer;
    }

    /* ______________________________________________________________________ */
    
    public function setLexer(LexerInterface $lexer) {
        $this->lexer = $lexer;
    }
}