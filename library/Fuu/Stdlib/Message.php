<?php
/**
 * Fuu Framework 
 * 
 * @author      a43s
 * @copyright   Copyright (c) 2011-2012 (IL)
 * @license     http://opensource.org/licenses/bsd-3-clause New BSD License
 * @package     Fuu_Stdlib
 */

namespace Fuu\Stdlib;

use Traversable;
use InvalidArgumentException;

/**
 * Original code from Zend Framework 2rc2, cloned from github on Jul/31/2012
 * @see https://github.com/zendframework/zf2/blob/master/library/Zend/Stdlib/Message.php
 */
class Message
{
    protected $metadata = array();
    protected $content = '';

    /* ______________________________________________________________________ */
    
    public function setMetadata($spec, $value = null)
    {
        if (is_scalar($spec)) {
            $this->metadata[$spec] = $value;
            return $this;
        }
        if ( ! is_array($spec) && ! ($spec instanceof Traversable)) {
            $type = (is_object($spec) ? get_class($spec) : gettype($spec));
            throw new InvalidArgumentException(sprintf(
                'Expected a string, array, or Traversable argument in first position. `%s` given.', $type
            ));
        }
        foreach ($spec as $key => $value) {
            $this->metadata[$key] = $value;
        }
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function getMetadata($key = null, $default = null)
    {
        if (null === $key) {
            return $this->metadata;
        }

        if ( ! is_scalar($key)) {
            throw new InvalidArgumentException('Non-scalar argument provided for key');
        }

        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        return $default;
    }

    /* ______________________________________________________________________ */
    
    public function setContent($value)
    {
        $this->content = $value;
        return $this;
    }

    /* ______________________________________________________________________ */
    
    public function getContent()
    {
        return $this->content;
    }

    /* ______________________________________________________________________ */
    
    public function toString()
    {
        $request = '';
        foreach ($this->getMetadata() as $key => $value) {
            $request .= sprintf(
                "%s: %s\r\n",
                (string) $key,
                (string) $value
            );
        }
        $request .= "\r\n" . $this->getContent();
        return $request;
    }
}