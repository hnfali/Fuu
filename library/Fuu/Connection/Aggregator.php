<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Connection
 */

namespace Fuu\Connection;

use Fuu\Stdlib\ResourceAggregatorAbstract;

class Aggregator extends ResourceAggregatorAbstract
{
    /* ______________________________________________________________________ */
    
    public function add($id, $adapter, array $config = array())
    {
        $this->adapters[$id] = Factory::factory($adapter, $config);
    }
}