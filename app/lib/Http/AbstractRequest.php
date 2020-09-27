<?php

namespace Library\Http;

abstract class AbstractRequest {

    protected $server;
    protected $headers;
    protected $params;
    protected $query;

    const REQUEST_METHOD_POST = "POST";
    const REQUEST_METHOD_PUT = "PUT";
    const REQUEST_METHOD_GET = "GET";
    const REQUEST_METHOD_DELETE = "DELETE";

    public function __construct()
    {
        $this->headers = new HeaderBag($_SERVER);
        $this->params = new ParamBag($_POST);
        $this->query = new QueryBag($_GET);
        $this->server = new ServerBag($_SERVER);
        $this->init();
    }

    /**
     * @return ServerBag
     */
    public function server()
    {
        return $this->server;
    }

    /**
     * @return HeaderBag
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * @return ParamBag
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * @return QueryBag
     */
    public function query()
    {
        return $this->query;
    }

    abstract function init();
}