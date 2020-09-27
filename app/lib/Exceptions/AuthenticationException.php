<?php

namespace Library\Exceptions;


use Library\Http\AbstractResponse;

class AuthenticationException extends \Exception
{
    public function __construct($message = "Not Authorized", $code = AbstractResponse::HTTP_UNAUTHORIZED, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}