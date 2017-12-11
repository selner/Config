<?php

namespace PHLAK\Config\Loaders;

use PHLAK\Config\Exceptions\InvalidFileException;
use DirectoryIterator;

class Directory extends Loader
{
    /**
     * Retrieve the contents of one or more configuration files in a directory
     * and convert them to an array of configuration options. Any invalid files
     * will be silently ignored.
     *
     * @return array Array of configuration options
     */
    protected function getArray()
    {
        $contents = [];

        foreach (new DirectoryIterator($this->context) as $file) {
            if ($file->isDot()) {
                continue;
            }

            $className = $file->isDir() ? 'Directory' : ucfirst(strtolower($file->getExtension()));
            $classPath = 'PHLAK\\Config\\Loaders\\' . $className;

            $loader = new $classPath($file->getPathname());

            try {
            	$loadedContents = $loader->getArray();
            	if(!empty($loadedContents) && is_array($loadedContents))
	                $contents = array_merge($contents, $loadedContents);
            } catch (InvalidFileException $e) {
                // Ignore it and continue
            }
        }

        return $contents;
    }
}
