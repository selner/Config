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
    public function __construct($context = null, $override = true, $importKey = "imports")
    {
        $this->_importKey = $importKey;

        switch (gettype($context)) {
            case 'NULL':
                break;
            case 'array':
                $this->config = $context;
                break;
            case 'string':
                $this->load($context, $override, $importKey);
                break;
            default:
                throw new InvalidContextException('Failed to initialize config');
        }

    }

    private function _proccesLoadedData()
    {
		$this->convert_dot_notation($this->config);
		ksort($this->config);
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
     * Retrieve a configuration option via a provided key.
     *
     * @return mixed Stored config
     */
    public function getAll()
    {
        return $this->config;
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
    public function load($path, $override = true, $importKey="imports")
    {
        $file = new SplFileInfo($path);

        $fileType = $file->isDir() ? 'Directory' : ucfirst(strtolower($file->getExtension()));
        $className = "\\PHLAK\\Config\\Loaders\\" . $fileType;
        try {
            $loader = new $className($file->getRealPath(), $importKey);

            $thisArr = $loader->toArray($override);
            if ($override === true) {
                $this->config = $this->array_merge_recursive_distinct($this->config, $thisArr);
            } else {
                $this->config = $this->array_merge_recursive_distinct($thisArr, $this->config);
            }

            $this->_proccesLoadedData();

            return $this;
        }
        catch (\Exception $ex)
        {
        	throw $ex;
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
	private function array_merge_recursive_distinct ( array &$array1, array &$array2 )
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


	/**
	 * Returns an array of all the keys of all values at every level
	 * of a multi-dimensional array
	 *
	 * @param array $arr
	 *
	 * @return array the set of all keys used for values at all levels in the array
	 */
	private function array_keys_multi(array $arr)
	{
		$keys = array();

		foreach ($arr as $key => $value) {
			$keys[] = $key;

			if (is_array($value)) {
				$keys = array_merge($keys, $this->array_keys_multi($value));
			}
		}

		return $keys;
	}

	/**
	 * If you need, for some reason, to create variable Multi-Dimensional Arrays, here's a quick
	 * function that will allow you to have any number of sub elements without knowing how many
	 * elements there will be ahead of time. Note that this will overwrite an existing array
	 * value of the same path.
	 *
	 * @author brian at blueeye dot us
	 * @Link http://php.net/manual/en/function.array.php#52138
	 *
	 * @param $path
	 * @param $data
	 *
	 * @return mixed
	 */
	private function array_set_element(&$path, $data) {
		return ($key = array_pop($path)) ? $this->array_set_element($path, array($key=>$data)) : $data;
	}

	/**
	 *
	 * Some INI files use dot or colon notation to define section and subkeys.  Convert
	 * any keys with dot notion to be subarray elements of the overall config array.
	 *
	 * Example:
	 *      database.connector.mysql.host = dbserver01.myserver.net
	 *  becomes
	 *      config['database']['connector']['mysql']['myhost']
	 *
	 * @param $config array storing the loaded configuration data
	 *
	 */
	private function convert_dot_notation(&$config)
	{
		$allKeys = $this->array_keys_multi($config);
		$sectionedKeys = array_filter($allKeys, function ($v) {
			$keyLevels = preg_split("/\s*[\.:]\s*/", $v);
			if (count($keyLevels) > 1)
				return true;

			return false;
		});

		foreach ($sectionedKeys as $treeKey) {
			$keyPath = preg_split("/\s*[\.:]\s*/", $treeKey);
			$newValues = $this->array_set_element($keyPath, $config[$treeKey]);

			$config = $this->array_merge_recursive_distinct($config, $newValues);
			unset($config[$treeKey]);
		}
	}



}
