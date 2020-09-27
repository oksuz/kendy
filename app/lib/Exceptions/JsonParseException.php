<?php

namespace Library\Exceptions;

use Library\Http\AbstractResponse;

class JsonParseException extends \Exception
{
    public function __construct($message = "", $code = AbstractResponse::HTTP_UNPROCESSABLE_ENTITY) {
        parent::__construct($message, $code);
    }
}