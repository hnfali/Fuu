<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Storage
 */

namespace Fuu\Storage\Session\Adapter;

use RuntimeException;
use Fuu\Stdlib\ArrayPath;

class Memory extends SessionAbstract implements AdapterInterface
{
    protected $prefix;
    protected $strorage;

    /* ______________________________________________________________________ */
    
    public function __construct(array $config = array())
    {
        $defaults = array(
            'namespace' => null,
            'namespace_delimiter' => '.',
            'initial_data'  => array()
        );
        parent::__construct($config + $defaults);
    }

    /* ______________________________________________________________________ */
    
    public function isStarted()
    {
        return ($this->strorage instanceof ArrayPath);
    }

    /* ______________________________________________________________________ */
    
    protected function start()
    {
        if ( ! $this->strorage) {
            $this->strorage = new ArrayPath($this->config['initial_data']);
            $this->strorage->setDelimiter($this->config['namespace_delimiter']);
        }
        return $this->isStarted();
    }

    /* ______________________________________________________________________ */
    
    public function check($key)
    {
        if ( ! $this->isStarted() && ! $this->start()) {
            throw new RuntimeException('Could not start session.');
        }
        return $this->strorage->path($this->prefix($key));
    }

    /* ______________________________________________________________________ */
    
    public function read($key = null, array $options = array())
    {
        if ( ! $this->isStarted() && ! $this->start()) {
            throw new RuntimeException('Could not start session.');
        }
        $data = $this->strorage->path($this->prefix($key));
        return $this->applyStrategies('read', $data, parent::READ_MODE);
    }

    /* ______________________________________________________________________ */
    
    public function write($key, $value = null, array $options = array())
    {
        $value = $this->applyStrategies('write', $value, parent::WRITE_MODE);
        $method = (isset($options['replace']) && $options['replace']) ? 'set' : 'add';
        return $this->strorage->{$method}($this->prefix($key), $value);
    }

    /* ______________________________________________________________________ */
    
    public function delete($key, array $options = array())
    {
        return $this->strorage->set($this->prefix($key), null);
    }

    /* ______________________________________________________________________ */
    
    public function destroy(array $options = array())
    {
        if (isset($options['destroy_internal']) && $options['destroy_internal']) {
            if ($this->config['prefix']) {
                $storage =& $this->strorage[$this->config['prefix']];
            } else {
                $storage =& $this->strorage;
            }
            
            foreach ($storage as $key => $val) {
                unset($storage[$key]);
            }
        }
        unset($this->strorage);
        return true;
    }

    /* ______________________________________________________________________ */
    
    protected function prefix($key)
    {
        $ns = $this->config['namespace'];
        $delim = $this->config['namespace_delimiter'];
        return ($ns ? rtrim($ns, $delim) . $delim : '') . $key;
    }
}