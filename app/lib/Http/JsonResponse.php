<?php

namespace Library\Http;

class JsonResponse extends AbstractResponse
{
    protected $statusCode;

    public function __construct($data, $statusCode = AbstractResponse::HTTP_OK)
    {
        parent::__construct($data);
        $this->addHeader("Content-Type", "application/json");
        $this->statusCode = $statusCode;
    }

    public function getResponse()
    {
        if (empty($this->data)) {
            return json_encode([]);
        }

        $this->setStatusCode($this->statusCode);
        $this->sendHeaders();

        return json_encode($this->data);
    }
}