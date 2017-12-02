<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Config;
use PHLAK\Config\Interfaces\Loadable;

abstract class Loader implements Loadable
{
    /** @var mixed Raw array of path to a configuration file or directory */
    protected $context;

    /**
     * Class constructor, loads on object creation.
     *
     * @param mixed $context Path to configuration file or directory
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    public function toArray($override=true)
    {
        $arr = $this->getArray();
        if(empty($arr))
            return array();

        $this->processImports($arr, $override);
        return $arr;
    }

    protected function getArray()
    {
        return array();
    }

    private function processImports(&$config, $override=true)
    {
        if(!empty($this->importKey) &&
            array_key_exists($this->importKey, $config) &&
            !empty($config[$this->importKey]))
        {
            foreach(array_keys($config[$this->importKey]) as $childKey)
            {
                $childConfig = new Config($config[$this->importKey][$childKey]);
                if ($override === true) {
                    $config = $this->array_merge_recursive_distinct($config, $childConfig);
                } else {
                    $config = $this->array_merge_recursive_distinct($childConfig, $config);
                }
            }
        }
    }




    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     * @see http://php.net/manual/en/function.array-merge-recursive.php#92195
     */
    function array_merge_recursive_distinct ( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }
}
