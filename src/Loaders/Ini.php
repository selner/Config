<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Exceptions\InvalidFileException;

/**
 * Class Ini
 * @package PHLAK\Config\Loaders
 */
class Ini extends Loader
{
	/**
	 * Retrieve the contents of an .ini file and convert it to an array of
	 * configuration options.
	 *
	 * @return array Array of configuration options
	 * @throws \PHLAK\Config\Exceptions\InvalidFileException
	 */
	protected function getArray()
    {
    	try
	    {
		    $parsed = parse_ini_file($this->context, true, INI_SCANNER_TYPED);
		    if (empty($parsed)) {
			    throw new InvalidFileException('Failed to parse INI file ' . $this->context . ": " . error_get_last()['message']);
		    }

		    return $parsed;
	    }
	    catch (\Exception $ex)
	    {
		    throw new InvalidFileException('Failed to parse INI file "' . $this->context . '": ' . $ex->getMessage(), $previous=$ex);
	    }
    }
}
