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

/**
 * String manipulation utility class. Includes functionality for generating UUIDs,
 * {:tag} and regex replacement, and tokenization. Also includes a cryptographically-strong random
 * number generator, and a base64 encoder for use with DES and XDES.
 * 
 * Original code from Lithium Framework 0.10
 * @see https://github.com/UnionOfRAD/lithium/blob/master/util/String.php
 */
abstract class String
{
    /* ______________________________________________________________________ */
    
    public static function hash($string, array $options = array()) {
        $defaults = array(
            'type'  => 'sha512',
            'salt'  => false,
            'key'   => false,
            'raw'   => false
        );
        $options += $defaults;

        if($options['salt']) {
            $string = $options['salt'] . $string;
        }
        if($options['key']) {
            return hash_hmac($options['type'], $string, $options['key'], $options['raw']);
        }
        return hash($options['type'], $string, $options['raw']);
    }

    /* ______________________________________________________________________ */
    
    public static function insert($str, array $data, array $options = array()) {
        $defaults = array(
            'before' => '{:',
            'after' => '}',
            'escape' => null,
            'format' => null,
            'clean' => false
        );
        $options += $defaults;
        $format = $options['format'];
        reset($data);

        if($format == 'regex' OR ( ! $format && $options['escape'])) {
            $format = sprintf(
                '/(?<!%s)%s%%s%s/',
                preg_quote($options['escape'], '/'),
                str_replace('%', '%%', preg_quote($options['before'], '/')),
                str_replace('%', '%%', preg_quote($options['after'], '/'))
            );
        }

        if( ! $format && key($data) !== 0) {
            $replace = array();
            foreach($data as $key => $value) {
                $replace["{$options['before']}{$key}{$options['after']}"] = $value;
            }
            $str = strtr($str, $replace);
            return $options['clean'] ? static::clean($str, $options) : $str;
        }

        if(strpos($str, '?') !== false && isset($data[0])) {
            $offset = 0;
            while(($pos = strpos($str, '?', $offset)) !== false) {
                $val = array_shift($data);
                $offset = $pos + strlen($val);
                $str = substr_replace($str, $val, $pos, 1);
            }
            return $options['clean'] ? static::clean($str, $options) : $str;
        }

        foreach($data as $key => $value) {
            $hashVal = crc32($key);
            $key = sprintf($format, preg_quote($key, '/'));

            if( ! $key) {
                continue;
            }
            $str = preg_replace($key, $hashVal, $str);

            if(is_object($value) && ! $value instanceof Closure) {
                try {
                    $value = $value->__toString();
                } catch(\Exception $e) {
                    $value = '';
                }
            }
            if( ! is_array($value)) {
                $str = str_replace($hashVal, $value, $str);
            }
        }

        if( ! isset($options['format']) && isset($options['before'])) {
            $str = str_replace($options['escape'] . $options['before'], $options['before'], $str);
        }
        return $options['clean'] ? static::clean($str, $options) : $str;
    }

    /* ______________________________________________________________________ */
    
    public static function clean($str, array $options = array()) {
        if( ! $options['clean']) {
            return $str;
        }
        $clean = $options['clean'];
        $clean = ($clean === true) ? array('method' => 'text') : $clean;
        $clean = (!is_array($clean)) ? array('method' => $options['clean']) : $clean;

        switch($clean['method']) {
            case 'html':
                $clean += array('word' => '[\w,.]+', 'andText' => true, 'replacement' => '');
                $kleenex = sprintf(
                    '/[\s]*[a-z]+=(")(%s%s%s[\s]*)+\\1/i',
                    preg_quote($options['before'], '/'),
                    $clean['word'],
                    preg_quote($options['after'], '/')
                );
                $str = preg_replace($kleenex, $clean['replacement'], $str);

                if ($clean['andText']) {
                    $options['clean'] = array('method' => 'text');
                    $str = static::clean($str, $options);
                }
                break;
            case 'text':
                $clean += array(
                    'word' => '[\w,.]+', 'gap' => '[\s]*(?:(?:and|or|,)[\s]*)?', 'replacement' => ''
                );
                $before = preg_quote($options['before'], '/');
                $after = preg_quote($options['after'], '/');

                $kleenex = sprintf(
                    '/(%s%s%s%s|%s%s%s%s|%s%s%s%s%s)/',
                    $before, $clean['word'], $after, $clean['gap'],
                    $clean['gap'], $before, $clean['word'], $after,
                    $clean['gap'], $before, $clean['word'], $after, $clean['gap']
                );
                $str = preg_replace($kleenex, $clean['replacement'], $str);
                break;
        }
        return $str;
    }

    /* ______________________________________________________________________ */
    
    public static function extract($regex, $str, $index = 0) {
        if( ! preg_match($regex, $str, $match)) {
            return false;
        }
        return isset($match[$index]) ? $match[$index] : null;
    }

    /* ______________________________________________________________________ */
    
    public static function tokenize($data, array $options = array()) {
        $defaults = array('separator' => ',', 'leftBound' => '(', 'rightBound' => ')');
        extract($options + $defaults);

        if( ! $data OR is_array($data)) {
            return $data;
        }

        $depth = 0;
        $offset = 0;
        $buffer = '';
        $results = array();
        $length = strlen($data);
        $open = false;

        while($offset <= $length) {
            $tmpOffset = -1;
            $offsets = array(
                strpos($data, $separator, $offset),
                strpos($data, $leftBound, $offset),
                strpos($data, $rightBound, $offset)
            );

            for($i = 0; $i < 3; $i++) {
                if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset OR $tmpOffset == -1)) {
                    $tmpOffset = $offsets[$i];
                }
            }

            if($tmpOffset === -1) {
                $results[] = $buffer . substr($data, $offset);
                $offset = $length + 1;
                continue;
            }
            $buffer .= substr($data, $offset, ($tmpOffset - $offset));

            if($data{$tmpOffset} == $separator && $depth == 0) {
                $results[] = $buffer;
                $buffer = '';
            } else {
                $buffer .= $data{$tmpOffset};
            }

            if($leftBound != $rightBound) {
                if($data{$tmpOffset} == $leftBound) {
                    $depth++;
                }
                if($data{$tmpOffset} == $rightBound) {
                    $depth--;
                }
                $offset = ++$tmpOffset;
                continue;
            }

            if($data{$tmpOffset} == $leftBound) {
                ($open) ? $depth-- : $depth++;
                $open = !$open;
            }
            $offset = ++$tmpOffset;
        }

        if( ! $results && $buffer) {
            $results[] = $buffer;
        }
        return $results ? array_map('trim', $results) : array();
    }

    /* ______________________________________________________________________ */
    
    public static function extractQuery($query, $delimiter = '&', $return = 'array') {
        if(is_array($query)) {
            return $query;
        }

        $query = (string) $query;
        $pairs = explode($delimiter, $query);
        $array = array();
        foreach($pairs as $pair) {
            list($key, $val) = explode('=', $pair, 2) + array('', null);
            $array[$key] = $val;
        }

        switch(true) {
            case ($return == 'object'):
                return (object) $array;

            case (class_exists($return)):
                return $return($array);

            default:
                return $array;
        }
    }

    /* ______________________________________________________________________ */
    
    public static function charLimiter($string, $limit = 100, $append = '&#8230;') {
        $string = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $string));
        if(strlen($string) < $limit) {
            return $string;
        }
        $string = substr($string, 0, $limit);
        $parts = explode(' ', $string);
        array_pop($parts);
        return implode(' ', $parts) . $append;
    }

    /* ______________________________________________________________________ */
    
    public static function normalize($str, $normalize = '.,-_/\\') {
        if(is_array($normalize)) {
            $normalize = implode('', $normalize);
        }
        return preg_replace('{([' . preg_quote($normalize) . '])\1+}', '$1', $str);
    }
}