<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Exceptions\InvalidFileException;

class Xml extends Loader
{
	/**
	 * Retrieve the contents of a .XML file and convert it to an array of
	 * configuration options.
	 *
	 * @return array Array of configuration options
	 * @throws \PHLAK\Config\Exceptions\InvalidFileException
	 */
    protected function getArray()
    {
	    try
	    {
		    $parsed = simplexml_load_file($this->context);
		    if (empty($parsed)) {
			    throw new InvalidFileException('Failed to parse XML file ' . $this->context . ": " . libxml_get_last_error());
		    }
		    return json_decode(json_encode($parsed), true);
	    }
	    catch (\Exception $ex)
	    {
		    throw new InvalidFileException('Failed to parse XML file "' . $this->context . '": ' . $ex->getMessage(), $previous=$ex);
	    }
    }
}