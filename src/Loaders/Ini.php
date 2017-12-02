<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Exceptions\InvalidFileException;

class Ini extends Loader
{
    /**
     * Retrieve the contents of a .ini file and convert it to an array of
     * configuration options.
     *
     * @return array Array of configuration options
     */
    protected function getArray()
    {
        $parsed = @parse_ini_file($this->context, true, INI_SCANNER_TYPED);

        if (! $parsed) {
            throw new InvalidFileException('Unable to parse invalid INI file at ' . $this->context);
        }

        return $parsed;
    }
}
