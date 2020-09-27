<?php

namespace Library\Http;

class HeaderBag extends Bag
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->parse($this->data);
    }

    protected function parse()
    {
        $headers = [];
        foreach ($this->data as $key => $value) {
            if ("HTTP_" === substr($key, 0, 5)) {
                $headerKey = strtolower(str_replace("_", "-", substr($key, 5, strlen($key))));
                $headers[$headerKey] = $value;
            }
        }

        $this->data = $headers;
    }

}