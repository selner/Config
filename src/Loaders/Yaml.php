<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Exceptions\InvalidFileException;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;

class Yaml extends Loader
{
	/**
	 * Retrieve the contents of a YAML file and convert it to an array of
	 * configuration options.
	 *
	 * @return array Array of configuration options
	 * @throws \PHLAK\Config\Exceptions\InvalidFileException
	 */
    protected function getArray()
    {
	    try
	    {
		    $parsed = YamlParser::parse(file_get_contents($this->context));
		    if (empty($parsed)) {
			    throw new InvalidFileException('Failed to parse INI file ' . $this->context . ": " . error_get_last());
		    }

		    return $parsed;
	    }
	    catch (ParseException $e)
	    {
		    throw new InvalidFileException('Failed to parse YAML file "' . $this->context . '": ' . $e->getMessage(), $previous=$e);
	    }
	    catch (\Exception $ex)
	    {
		    throw new InvalidFileException('Failed to parse YAML file "' . $this->context . '": ' . $ex->getMessage(), $previous=$ex);
	    }
    }
}
