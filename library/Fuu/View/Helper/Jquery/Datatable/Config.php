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

use Fuu\Stdlib\Config as StdlibConfig;

class Config extends StdlibConfig
{
    protected $data = array(
        'bProcessing'       => true,
        'bServerSide'       => true,
        'sAjaxSource'       => null,
        'bPaginate'         => true,
        'sPaginationType'   => 'full_numbers',
        'bLengthChange'     => true,
        'bFilter'           => true,
        'bSort'             => true,
        'bInfo'             => false,
        'bAutoWidth'        => false,
        'bStateSave'        => false,
        'aLengthMenu'       => array(5, 10, 20, 50, 100, 500, 'All'),
        'aaSorting'         => array(array(1, 'desc')),
        'sScrollX'          => '100%',
        'sScrollXInner'     => '100%',
        'bScrollCollapse'   => false,
        'bScrollInfinite'   => false,
        'bJQueryUI'         => true,
        'iDisplayLength'    => 20,
        'oLanguage'         => array(),
        'sScrollY'          => null,
        'fnDrawCallback'    => null
    );
    
    /* ______________________________________________________________________ */
    
    public function __toString()
    {
        // fnDrawCallback may contains js callbacks
        $fnDrawCallback = isset($this['fnDrawCallback']) ? $this['fnDrawCallback'] : null;
        
        $replaceId = '{{' . uniqid() . '}}';
        if ($fnDrawCallback) {
            $this['fnDrawCallback'] = $replaceId;
        }
        
        $config = json_encode(array_filter($this->data), JSON_FORCE_OBJECT);
        $config = str_replace('"' . $replaceId . '"', $fnDrawCallback, $config);
        return $config;
    }
}