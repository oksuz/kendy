<?php

namespace Library\Routing;

class Action
{
    protected $class;
    protected $method;
    protected $path;
    protected $args = [];

    public function __construct($class, $method, $args, $path = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->args = $args;
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
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

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    public function __toString()
    {
        return __CLASS__ . " - class: " . $this->class . " method:" . $this->method . " path:" . $this->path
        ." args: " . implode(",", $this->args);
    }


}