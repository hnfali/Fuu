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
 * Utility for modifying format of words. Change singular to plural and vice versa.
 * Under_score a CamelCased word and vice versa. Replace spaces and special characters.
 * Create a human readable word from the others. Used when consistency in naming
 * conventions must be enforced.
 * 
 * Original code from Lithium Framework 0.10
 * @see https://github.com/UnionOfRAD/lithium/blob/master/util/Inflector.php
 */
abstract class Inflector
{
    protected static $transliteration = array(
        '/à|á|å|â/' => 'a',
        '/è|é|ê|ẽ|ë/' => 'e',
        '/ì|í|î/' => 'i',
        '/ò|ó|ô|ø/' => 'o',
        '/ù|ú|ů|û/' => 'u',
        '/ç/' => 'c', '/ñ/' => 'n',
        '/ä|æ/' => 'ae', '/ö/' => 'oe',
        '/ü/' => 'ue', '/Ä/' => 'Ae',
        '/Ü/' => 'Ue', '/Ö/' => 'Oe',
        '/ß/' => 'ss'
    );

    protected static $uninflected = array(
        'Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus',
        'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps',
        'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder',
        'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
        'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
        'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media',
        'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese', 'People',
        'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
        'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors',
        'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'swine', 'testes',
        'trousers', 'trout','tuna', 'Vermontese', 'Wenchowese', 'whiting', 'wildebeest',
        'Yengeese'
    );

    protected static $singular = array(
        'rules' => array(
            '/(s)tatuses$/i' => '\1\2tatus',
            '/^(.*)(menu)s$/i' => '\1\2',
            '/(quiz)zes$/i' => '\\1',
            '/(matr)ices$/i' => '\1ix',
            '/(vert|ind)ices$/i' => '\1ex',
            '/^(ox)en/i' => '\1',
            '/(alias)(es)*$/i' => '\1',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
            '/(cris|ax|test)es$/i' => '\1is',
            '/(shoe)s$/i' => '\1',
            '/(o)es$/i' => '\1',
            '/ouses$/' => 'ouse',
            '/uses$/' => 'us',
            '/([m|l])ice$/i' => '\1ouse',
            '/(x|ch|ss|sh)es$/i' => '\1',
            '/(m)ovies$/i' => '\1\2ovie',
            '/(s)eries$/i' => '\1\2eries',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([lr])ves$/i' => '\1f',
            '/(tive)s$/i' => '\1',
            '/(hive)s$/i' => '\1',
            '/(drive)s$/i' => '\1',
            '/([^fo])ves$/i' => '\1fe',
            '/(^analy)ses$/i' => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/([ti])a$/i' => '\1um',
            '/(p)eople$/i' => '\1\2erson',
            '/(m)en$/i' => '\1an',
            '/(c)hildren$/i' => '\1\2hild',
            '/(n)ews$/i' => '\1\2ews',
            '/^(.*us)$/' => '\\1',
            '/s$/i' => ''
        ),
        'irregular' => array(),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss'
        )
    );

    protected static $singularized = array();
    protected static $plural = array(
        'rules' => array(
            '/(s)tatus$/i' => '\1\2tatuses',
            '/(quiz)$/i' => '\1zes',
            '/^(ox)$/i' => '\1\2en',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)(ix|ex)$/i'  => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(hive)$/i' => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/(p)erson$/i' => '\1eople',
            '/(m)an$/i' => '\1en',
            '/(c)hild$/i' => '\1hildren',
            '/(buffal|tomat)o$/i' => '\1\2oes',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
            '/us$/' => 'uses',
            '/(alias)$/i' => '\1es',
            '/(ax|cri|test)is$/i' => '\1es',
            '/s$/' => 's',
            '/^$/' => '',
            '/$/' => 's'
        ),
        'irregular' => array(
            'atlas' => 'atlases', 'beef' => 'beefs', 'brother' => 'brothers',
            'child' => 'children', 'corpus' => 'corpuses', 'cow' => 'cows',
            'ganglion' => 'ganglions', 'genie' => 'genies', 'genus' => 'genera',
            'graffito' => 'graffiti', 'hoof' => 'hoofs', 'loaf' => 'loaves', 'man' => 'men',
            'leaf' => 'leaves', 'money' => 'monies', 'mongoose' => 'mongooses', 'move' => 'moves',
            'mythos' => 'mythoi', 'numen' => 'numina', 'occiput' => 'occiputs',
            'octopus' => 'octopuses', 'opus' => 'opuses', 'ox' => 'oxen', 'penis' => 'penises',
            'person' => 'people', 'sex' => 'sexes', 'soliloquy' => 'soliloquies',
            'testis' => 'testes', 'trilby' => 'trilbys', 'turf' => 'turfs'
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep'
        )
    );

    protected static $pluralized = array();
    protected static $camelized = array();
    protected static $underscored = array();
    protected static $humanized = array();

    /* ______________________________________________________________________ */

    public static function rules($type, $config = array())
    {
        $var = '_' . $type;
        if ( ! isset(static::${$var})) {
            return null;
        }
        if (empty($config)) {
            return static::${$var};
        }
        switch ($type) {
            case 'transliteration':
                $_config = array();

                foreach ($config as $key => $val) {
                    if ($key[0] != '/') {
                        $key = '/' . join('|', array_filter(preg_split('//u', $key))) . '/';
                    }
                    $_config[$key] = $val;
                }
                static::$transliteration = array_merge(
                    $_config, static::$transliteration, $_config
                );
                break;
            case 'uninflected':
                static::$uninflected = array_merge(static::$uninflected, (array) $config);
                static::$plural['regexUninflected'] = null;
                static::$singular['regexUninflected'] = null;

                foreach ((array) $config as $word) {
                    unset(static::$singularized[$word], static::$pluralized[$word]);
                }
                break;
            case 'singular':
            case 'plural':
                if (isset(static::${$var}[key($config)])) {
                    foreach ($config as $rType => $set) {
                        static::${$var}[$rType] = array_merge($set, static::${$var}[$rType], $set);

                        if ($rType == 'irregular') {
                            $swap = ($type == 'singular' ? '_plural' : '_singular');
                            static::${$swap}[$rType] = array_flip(static::${$var}[$rType]);
                        }
                    }
                } else {
                    static::${$var}['rules'] = array_merge(
                        $config, static::${$var}['rules'], $config
                    );
                }
                break;
        }
    }

    /* ______________________________________________________________________ */

    public static function pluralize($word)
    {
        if (isset(static::$pluralized[$word])) {
            return static::$pluralized[$word];
        }
        extract(static::$plural);

        if ( ! isset($regexUninflected) OR ! isset($regexIrregular)) {
            $regexUninflected = static::enclose(join( '|', $uninflected + static::$uninflected));
            $regexIrregular = static::enclose(join( '|', array_keys($irregular)));
            static::$plural += compact('regexUninflected', 'regexIrregular');
        }
        if (preg_match('/(' . $regexUninflected . ')$/i', $word, $regs)) {
            return static::$pluralized[$word] = $word;
        }
        if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
            $plural = substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
            return static::$pluralized[$word] = $regs[1] . $plural;
        }
        foreach ($rules as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return static::$pluralized[$word] = preg_replace($rule, $replacement, $word);
            }
        }
        return static::$pluralized[$word] = $word;
    }

    /* ______________________________________________________________________ */

    public static function singularize($word)
    {
        if (isset(static::$singularized[$word])) {
            return static::$singularized[$word];
        }
        if (empty(static::$singular['irregular'])) {
            static::$singular['irregular'] = array_flip(static::$plural['irregular']);
        }
        extract(static::$singular);

        if ( ! isset($regexUninflected) OR ! isset($regexIrregular)) {
            $regexUninflected = static::enclose(join('|', $uninflected + static::$uninflected));
            $regexIrregular = static::enclose(join('|', array_keys($irregular)));
            static::$singular += compact('regexUninflected', 'regexIrregular');
        }
        if (preg_match("/(.*)\\b({$regexIrregular})\$/i", $word, $regs)) {
            $singular = substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
            return static::$singularized[$word] = $regs[1] . $singular;
        }
        if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
            return static::$singularized[$word] = $word;
        }
        foreach ($rules as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return static::$singularized[$word] = preg_replace($rule, $replacement, $word);
            }
        }
        return static::$singularized[$word] = $word;
    }

    /* ______________________________________________________________________ */

    public static function reset()
    {
        static::$singularized = static::$pluralized = array();
        static::$camelized = static::$underscored = array();
        static::$humanized = array();

        static::$plural['regexUninflected'] = static::$singular['regexUninflected'] = null;
        static::$plural['regexIrregular'] = static::$singular['regexIrregular'] = null;
        static::$transliteration = array(
            '/à|á|å|â/' => 'a', '/è|é|ê|ẽ|ë/' => 'e',
            '/ì|í|î/' => 'i', '/ò|ó|ô|ø/' => 'o',
            '/ù|ú|ů|û/' => 'u', '/ç/' => 'c',
            '/ñ/' => 'n', '/ä|æ/' => 'ae', '/ö/' => 'oe',
            '/ü/' => 'ue', '/Ä/' => 'Ae',
            '/Ü/' => 'Ue', '/Ö/' => 'Oe',
            '/ß/' => 'ss'
        );
    }

    /* ______________________________________________________________________ */

    public static function camelize($word, $cased = true)
    {
        $_word = $word;

        if (isset(static::$camelized[$_word]) && $cased) {
            return static::$camelized[$_word];
        }
        $word = str_replace(" ", "", ucwords(str_replace(array("_", '-'), " ", $word)));

        if ( ! $cased) {
            return lcfirst($word);
        }
        return static::$camelized[$_word] = $word;
    }

    /* ______________________________________________________________________ */

    public static function underscore($word)
    {
        if (isset(static::$underscored[$word])) {
            return static::$underscored[$word];
        }
        return static::$underscored[$word] = strtolower(static::slug($word, '_'));
    }

    /* ______________________________________________________________________ */

    public static function slug($string, $replacement = '-')
    {
        $map = static::$transliteration + array(
            '/[^\w\s]/' => ' ', '/\\s+/' => $replacement,
            '/(?<=[a-z])([A-Z])/' => $replacement . '\\1',
            str_replace(':rep', preg_quote($replacement, '/'), '/^[:rep]+|[:rep]+$/') => ''
        );
        return strtolower(preg_replace(array_keys($map), array_values($map), $string));
    }

    /* ______________________________________________________________________ */

    public static function humanize($word, $separator = '_')
    {
        if (isset(static::$humanized[$key = $word . ':' . $separator])) {
            return static::$humanized[$key];
        }
        return static::$humanized[$key] = ucwords(str_replace($separator, " ", $word));
    }

    /* ______________________________________________________________________ */

    public static function tableize($string)
    {
        return static::pluralize(static::underscore($string));
    }

    /* ______________________________________________________________________ */

    public static function classify($string)
    {
        return static::camelize(static::singularize($string));
    }

    /* ______________________________________________________________________ */

    protected static function enclose($string)
    {
        return '(?:' . $string . ')';
    }
}