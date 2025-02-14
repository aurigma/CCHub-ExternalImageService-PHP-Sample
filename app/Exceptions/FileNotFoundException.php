<?php

namespace App\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    public function __construct(string $message = "File is not found")
    {
        parent::__construct($message, 404);
    }
}
