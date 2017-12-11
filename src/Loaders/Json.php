<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Exceptions\InvalidFileException;

class Json extends Loader
{
	/**
	 * Retrieve the contents of a .json file and convert it to an array of
	 * configuration options.
	 *
	 * @return array Array of configuration options
	 * @throws \PHLAK\Config\Exceptions\InvalidFileException
	 */
    protected function getArray()
    {
        $contents = file_get_contents($this->context);

        $parsed = json_decode($contents, true);
        if(empty($parsed))
        {
		    throw new InvalidFileException('Failed to parse JSON file "' . $this->context . '": ' . json_last_error_msg());
        }

        return $parsed;
    }
}
