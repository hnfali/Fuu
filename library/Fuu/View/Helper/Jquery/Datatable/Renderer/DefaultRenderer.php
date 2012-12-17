<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_View
 */

namespace Fuu\View\Helper\Jquery\Datatable\Renderer;

use Fuu\Mvc\Exception\ConfigException;
use Fuu\View\Helper\Jquery\Datatable\Config;
use Fuu\View\Helper\Jquery\Datatable\Schema;

class DefaultRenderer implements RendererInterface
{
    protected $config;

    /* ______________________________________________________________________ */
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /* ______________________________________________________________________ */
    
    public function render(Schema $schema)
    {
        // validate if `sAjaxSource` is set.
        if ($this->config['bServerSide'] && ! $this->config['sAjaxSource']) {
            throw new ConfigException(__METHOD__ . ': `sAjaxSource` is not set.');
        }
        
        // if `bServerSide` is set to false and `aaData` is not set? 
        // set the initial value of `aaData`
        if ( ! $this->config['bServerSide'] && ! $this->config['aaData']) {
            $this->config['aaData'] = array();
        }
        
        $tableHeader = '';
        $tableFooter = '';
        foreach ($schema as $key => $config) {
            if ( ! isset($this->config['aoColumns'])) {
                $this->config['aoColumns'][] = array(
                    'sName'     => $key,
                    'sTitle'    => $config['label'],
                    'sWidth'    => $config['width'] ? intval($config['width']) . 'px' : '',
                    'bSortable' => (bool) $config['sortable'],
                    'bVisible'  => (bool) $config['visible']
                );
            }
            
            $tableHeader .= '<th>' . $config['header_html'] ?: $config['label'] . '</th>' . "\n";
            $tableFooter .= '<th>' . $config['footer_html'] ?: '' . '</th>' . "\n";
        }
        
        return '
        <table id="datatable">
            <thead>
                <tr>
                    ' . $tableHeader . '
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    ' . $tableFooter . '
                </tr>
            </tfoot>
        </table>
        <script type=\"text/javascript\">
            <!-- // <![CDATA[
            var oTable = jQuery("#datatable").dataTable(' . (string) $this->config . ');
            // ]]> -->
        </script>
        ';
    }
}