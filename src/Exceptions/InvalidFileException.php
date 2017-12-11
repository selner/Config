<?php

namespace PHLAK\Config\Exceptions;

use Exception;

class InvalidFileException extends Exception
{
	public function __construct($message = "", $previous = null)
	{
		if(in_array("\Throwable", class_implements(get_class($previous))))
			parent::__construct($message, null, $previous);
		else
			parent::__construct($message);
	}

}
