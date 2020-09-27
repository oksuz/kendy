<?php

namespace Library\Http;

use Library\Exceptions\JsonParseException;

class Request extends AbstractRequest
{
    public function init()
    {
        if (
            self::REQUEST_METHOD_PUT === $this->server()->get("REQUEST_METHOD") ||
            self::REQUEST_METHOD_DELETE === $this->server()->get("REQUEST_METHOD")
        ) {
            $rawPostData = file_get_contents("php://input");
            $decoded = json_decode($rawPostData, true);
            if (null === $decoded && !empty($rawPostData)) {
                throw new JsonParseException("invalid json body");
            }

            $this->params = new ParamBag((array)$decoded);
        }
    }
}