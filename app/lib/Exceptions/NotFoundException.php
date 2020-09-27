<?php

namespace Library\Exceptions;

use Library\Http\AbstractResponse;

class NotFoundException extends \Exception
{
    public function __construct($message = "", $code = AbstractResponse::HTTP_NOT_FOUND) {
        parent::__construct($message, $code);
    }
}