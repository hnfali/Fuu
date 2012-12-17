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

use Traversable;
use InvalidArgumentException;
use Fuu\Mvc\Action\Request;
use Fuu\Stdlib\Config;

class Schema extends Config
{
    protected $data = array();

    /* ______________________________________________________________________ */
    
    public function __construct(array $schema = array())
    {
        $this->data = $schema;
    }

    /* ______________________________________________________________________ */
    
    public function getField($name)
    {
        return $this->offsetGet($name);
    }

    /* ______________________________________________________________________ */
    
    public function setField($name, array $config = array())
    {
        $defaults = array(
            'output_filter' => null,
            'input_filter'  => null,
            'width'         => null,
            'visible'       => true,
            'sortable'      => true,
            'searchable'    => true,
            'label'         => '',
            'default'       => '',
            'header_html'   => '',
            'footer_html'   => '',
            'key_prefix'    => ''
        );
        
        $this[$name] = $config + $defaults;
    }

    /* ______________________________________________________________________ */
    
    public function getFields()
    {
        return $this->data;
    }

    /* ______________________________________________________________________ */
    
    public function setFields(array $config = array())
    {
        foreach ($config as $key => $value) {
            $this->setField($key, $value);
        }
    }

    /* ______________________________________________________________________ */
    /** overrides parent method */
    public function offsetSet($key, $value)
    {
        if ( ! is_array($value)) {
            throw new InvalidArgumentException(__METHOD__ . ': expects args #2 to be an array.');
        }
        $this->setField($key, $value);
    }

    /* ______________________________________________________________________ */
    
    public function adapt($data, $filter = null)
    {
        if ( ! is_array($data) && ! ($data instanceof Traversable)) {
            throw new InvalidArgumentException(__METHOD__ . ': requires args #1 to be an array or Traversable object.');
        }
        
        $defaults = array_map(function($e) { return $e['default']; }, $this);

        $new = array();
        foreach ($data as $row) {
            if (is_callable($filter)) {
                $row = $filter($row);
            }

            $tmp = array_merge($defaults, array_intersect_key($row, $this->data));
            foreach ($tmp as $key => $cell) {
                $callback = $this[$key]['output_filter'];
                if (is_callable($callback)) {
                    $tmp[$key] = $callback($cell, $row);
                }
            }

            $new[] = array_values($tmp);
        }

        return $new;
    }

    /* ______________________________________________________________________ */
    
    public function getRequests(Request $request)
    {
        $search = (int) $request->get('sSearch');
        $offset = (int) $request->get('iDisplayStart');
        $limit  = (int) $request->get('iDisplayLength', 10);
        $page   = $limit ? (floor($offset / $limit) + 1) : 1;
        
        $sort_order = array();
        if ($n = $request->get('iSortingCols', 0)) {
            $fields = array_keys($this->data);
            
            for ($i = 0; $i < $n; $i++) {
                $key = "iSortCol_{$i}";
                
                if (($j = $request->get($key)) && isset($fields[$j]) && $this[$fields[$j]]['sortable']) {
                    $direction = $request->get("sSortDir_{$i}", 'asc', array(function($input) {
                        return in_array(strtoupper($input), array('ASC', 'DESC')) ? strtoupper($input) : 'ASC';
                    }));
                    $sort_order[$fields[$j]] = $direction;
                }
            }
        }
        
        $filters = array();
        $cols = array_filter($this, function($e) {
            return $e['searchable'] ? true : false;
        });
        
        $n = count($cols);
        for ($i = 0; $i < $n; $i++) {
            if (($j = $request->get("sSearch_{$i}")) && trim($j) != '' && $this[$cols[$i]]['searchable']) {
                $filters[$cols[$i]] = $j;
            }
        }
        
        $sort = null;
        $order = null;
        if ($sort_order) {
            list($sort, $order) = each($sort_order);
        }
        
        return compact('search', 'offset', 'limit', 'page', 'sort_order', 'filters', 'sort', 'order');
    }
}