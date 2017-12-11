<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Exceptions\InvalidFileException;

class Php extends Loader
{
	/**
	 * Retrieve the contents of a .php configuration and convert it to an array of
	 * configuration options.
	 *
	 * @return array Array of configuration options
	 * @throws \PHLAK\Config\Exceptions\InvalidFileException
	 */
    protected function getArray()
    {
        $contents = include $this->context;
		if(empty($contents))
		{
            throw new InvalidFileException($this->context . ' did not return a valid array. ' . error_get_last());
        }

        return $contents;
    }
}
