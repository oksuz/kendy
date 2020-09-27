<?php

namespace Library\Routing;

use Application\Util\TextUtil;

class Route
{
    protected $path;
    protected $method = null;
    protected $action = null;
    protected $args = [];
    protected $afterCallback;
    protected $beforeCallback;

    public function __construct($path, $method, $action, $args = [])
    {
        $this->path = $path;
        $this->method = $method;
        $this->action = $action;
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getAfterCallback()
    {
        return $this->afterCallback;
    }

    /**
     * @param mixed $afterCallback
     */
    public function setAfterCallback(callable $afterCallback = null)
    {
        $this->afterCallback = $afterCallback;
    }

    /**
     * @return mixed
     */
    public function getBeforeCallback()
    {
        return $this->beforeCallback;
    }

    /**
     * @param mixed $beforeCallback
     */
    public function setBeforeCallback(callable $beforeCallback = null)
    {
        $this->beforeCallback = $beforeCallback;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    public function __toString()
    {
        return "path: " . $this->path
            .   " method: " . $this->method
            .   " action " . $this->action
            .   " args: " . TextUtil::implodeArrayWithKeys($this->args);
    }


}