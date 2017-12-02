<?php

namespace PHLAK\Config;

use PHLAK\Config\Traits\ArrayAccess;
use PHLAK\Config\Exceptions\InvalidContextException;
use \SplFileInfo;

/**
 * Class Config
 * @package PHLAK\Config
 */
class Config implements \ArrayAccess
{
    use ArrayAccess;

    /** @var array Array of configuration options */
    protected $config = [];

    /**
     * Class constructor, runs on object creation.
     *
     * @param mixed $context Raw array of configuration options or path to a
     *                       configuration file or directory
     * @param string $importKey Config key that may optionally contain child import contexts to load
     * @param bool   $override Whether or not to override existing options with
     *                         values from the loaded file
     */
    public function __construct($context = null, $importKey = "imports", $override = false)
    {

        switch (gettype($context)) {
            case 'NULL':
                break;
            case 'array':
                $this->config = $context;
                break;
            case 'string':
                $this->load($context);
                if(is_file($context))
                {
                    $childContexts = array();
                    $this->processImports($importKey, $this->config,$childContexts);
                    if(!empty($childContexts))
                        foreach($childContexts as $childContext)
                            $this->load($childContext, $override);
                }

                break;
            default:
                throw new InvalidContextException('Failed to initialize config');
        }

    }

    private function processImports($importKey, $context, &$childContexts)
    {
        if(array_key_exists($importKey, $context) && !empty($context[$importKey]) === true)
        {
            foreach(array_keys($context[$importKey]) as $childKey)
            {
                $childContexts[$childKey] = $context[$importKey][$childKey];
                $this->processImports($importKey, $childContexts[$childKey], $childContexts);
            }
        }
    }

    /**
     * Magic get method; allows accessing config items via object notation.
     *
     * @param string $property Unique configuration option key
     *
     * @return mixed New config object or config item value
     */
    public function __get($property)
    {
        $item = $this->get($property);

        if (is_array($item)) {
            return new static($item);
        }

        return $item;
    }

    /**
     * Store a config value with a specified key.
     *
     * @param string $key   Unique configuration option key
     * @param mixed  $value Config item value
     *
     * @return boolean Returns true if successful
     */
    public function set($key, $value)
    {
        $config = &$this->config;

        foreach (explode('.', $key) as $k) {
            $config = &$config[$k];
        }

        $config = $value;

        return true;
    }

    /**
     * Retrieve a configuration option via a provided key.
     *
     * @param string $key     Unique configuration option key
     * @param mixed  $default Default value to return if option does not exist
     *
     * @return mixed Stored config item or $default value
     */
    public function get($key = null, $default = null)
    {
        if (! isset($key)) {
            return $this->config;
        }

        $config = $this->config;

        foreach (explode('.', $key) as $k) {
            if (! isset($config[$k])) {
                return $default;
            }
            $config = $config[$k];
        }

        return $config;
    }

    /**
     * Check for the existance of a config item.
     *
     * @param string $key Unique configuration option key
     *
     * @return bool True if item existst, otherwise false
     */
    public function has($key)
    {
        $config = $this->config;

        foreach (explode('.', $key) as $k) {
            if (! isset($config[$k])) {
                return false;
            }
            $config = $config[$k];
        }

        return true;
    }

    /**
     * Load configuration options from a file or directory.
     *
     * @param string $path     Path to configuration file or directory
     * @param bool   $override Whether or not to override existing options with
     *                         values from the loaded file
     *
     * @return object This Config object
     */
    public function load($path, $override = true)
    {
        $file = new SplFileInfo($path);

        $fileType = $file->isDir() ? 'Directory' : ucfirst(strtolower($file->getExtension()));
        $className = "\\PHLAK\\Config\\Loaders\\" . $fileType;
        if(class_exists($className, true)) {
            $loader = new $className($file->getRealPath());

            $thisArr = $loader->toArray();
            if ($override === true) {
                $this->config = $this->array_merge_recursive_distinct($this->config, $thisArr);
            } else {
                $this->config = $this->array_merge_recursive_distinct($thisArr, $this->config);
            }

            return $this;
        }

        throw new \InvalidArgumentException("No loader defined for files of type '{$fileType}'.");

    }

    /**
     * Merge another Config object into this one.
     *
     * @param Config $config Instance of Config
     *
     * @return object This Config object
     */
    public function merge(Config $config)
    {
        $this->config = array_merge($this->config, $config->get());

        return $this;
    }

    /**
     * Split a sub-array of configuration options into it's own Config object.
     *
     * @param string $key Unique configuration option key
     *
     * @return Config A new Config object
     */
    public function split($key)
    {
        return new static($this->get($key));
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
