<?php

namespace Application;

class Container implements \ArrayAccess
{
    const NAME_FORMAT_PARAMETER = "__parameter.%s";

    protected $container = [];
    protected $env;

    public function __construct($env)
    {
        $this->env = $env;
    }

    public function offsetExists($id)
    {
        return array_key_exists($id, $this->container);
    }

    public function offsetGet($id)
    {
        if ($this->offsetExists($id)) {
            return $this->container[$id];
        }
        return null;
    }

    public function offsetSet($id, $value)
    {
        $this->container[$id] = $value;
    }

    public function offsetUnset($id)
    {
        unset($this->container[$id]);
    }

    // their own methods
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    public function add($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function getParameter($id)
    {
        $name = sprintf(self::NAME_FORMAT_PARAMETER, $id);
        return $this->hasParameter($id) ? $this->get($name) : null;
    }

    public function setParameter($id, $value)
    {
        $name = sprintf(self::NAME_FORMAT_PARAMETER, $id);
        $this->add($name, $value);
    }

    public function hasParameter($id)
    {
        return $this->has(sprintf(self::NAME_FORMAT_PARAMETER, $id));
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function __toString()
    {
        return sprintf("Application container, mode: '%s'", $this->getEnv());
    }
}