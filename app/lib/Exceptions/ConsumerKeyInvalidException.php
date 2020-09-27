<?php

namespace Library\Exceptions;

use Library\Http\AbstractResponse;

class ConsumerKeyInvalidException extends \Exception
{
    public function __construct($message = "", $code = AbstractResponse::HTTP_UNAUTHORIZED, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}