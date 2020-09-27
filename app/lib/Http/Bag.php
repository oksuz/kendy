<?php

namespace Library\Http;

abstract class Bag implements \Countable, \IteratorAggregate {

    protected $data = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    public function count()
    {
        return (int)count($this->data);
    }

    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function all()
    {
        return $this->data;
    }

}