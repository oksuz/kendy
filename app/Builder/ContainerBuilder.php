<?php

namespace Application\Builder;

use Application\Container;

class ContainerBuilder
{
    const SERVICE_CONFIGURATION = "../Config/services.json";
    const CONTAINER_ID = "container";
    const CONFIG_CLASS = "\\Application\\Config\\Configuration";

    protected $config;
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $config = sprintf("%s/%s", dirname(__FILE__), self::SERVICE_CONFIGURATION);
        $this->config = json_decode(file_get_contents($config));
    }

    public function build()
    {
        if (empty($this->config)) {
            throw new \Exception("Configuration is invalid");
        }
        $this->buildParameters();
        $this->buildServices();
    }

    protected function buildParameters()
    {
        if (!empty($this->config->parameters)) {
            foreach ($this->config->parameters as $parameter => $value) {
                $value = $this->replaceVariablesInParameter($value);
                $this->container->setParameter($parameter, $value);
            }
        }
    }

    /**
     * it replaces $CONST_NAME like value to
     * constant(Application\Configuration::CONST_NAME)
     *
     * its just supporting parameters
     *
     * @param $value
     * @return mixed
     */
    private function replaceVariablesInParameter($value)
    {
        $matches = [];
        preg_match_all("/\\$[A-Z_]+/", $value, $matches);
        if (!empty($matches)) {
            $matches = current($matches);
            foreach ($matches as $match) {
                $constant = str_replace("\$", "", $match);
                $value = str_replace($match, constant(sprintf("%s::%s", self::CONFIG_CLASS, $constant)), $value);
            }
        }

        return $value;
    }

    protected function buildServices()
    {
        if (!empty($this->config->services)) {
            foreach ($this->config->services as $service) {
                $this->generateService($service);
                if (!empty($service->calls) && is_array($service->calls)) {
                    $this->calls($service);
                }
            }
        }
    }

    protected function generateService($service)
    {
        if (empty($service->id)) {
            throw new \Exception("Service id cannot be empty");
        }

        if (empty($service->class) || !class_exists($service->class)) {
            throw new \Exception(sprintf("Class doesn't exits or empty for `%s`", $service->id));
        }

        if ($this->container->has($service->id)) {
            return;
        }

        if (!empty($service->factory)) {
            $this->generateFromFactory($service);
            return;
        }

        if (!empty($service->args)) {
            $this->generateUsingConstructor($service);
            return;
        }

        $this->generateNormal($service);
    }

    private function generateFromFactory($service)
    {
        if (empty($service->factory->method)) {
            throw new \Exception(sprintf("Factory method cannot be empty for service `%s`", $service->id));
        }

        $callable = [$service->class, $service->factory->method];
        if (!is_callable($callable)) {
            throw new \Exception(sprintf("`%s`::`%s` is not callable", $service->class, $service->factory->method));
        }

        if (empty($service->factory->args)) {
            $instance = call_user_func($callable);
            $this->addServiceToContainer($service->id, $instance);
            return;
        }

        $args = $this->parseArguments($service->factory->args);
        $instance = null;
        if (is_array($service->factory->args) && is_array($args)) {
            $instance = call_user_func_array($callable, $args);
        }

        if (null === $instance) {
            throw new \Exception(sprintf("Instance for `%s` is null", $service->id));
        }

        $this->addServiceToContainer($service->id, $instance);
    }

    private function generateUsingConstructor($service)
    {
        $args = $this->parseArguments($service->args);
        $class = new \ReflectionClass($service->class);
        $instance = $class->newInstanceArgs($args);
        $this->addServiceToContainer($service->id, $instance);
    }

    private function generateNormal($service)
    {
        $class = new \ReflectionClass($service->class);
        $instance = $class->newInstance();
        $this->addServiceToContainer($service->id, $instance);
    }

    private function calls($service)
    {
        foreach ($service->calls as $index => $call) {
            if (empty($call->method)) {
                throw new \Exception(sprintf("Service `%s` call doesn't have method at index `%s`", $service->id, $index));
            }

            $args = [];
            if (!empty($call->args)) {
                $args = (array)$this->parseArguments($call->args);
            }

            $instance = $this->container->get($service->id);
            $callable = [$instance, $call->method];
            if (!is_callable($callable)) {
                throw new \Exception(sprintf("Service `%s`s method `%s` doesn't callable", $service->id, $call->metod));
            }
            call_user_func_array($callable, $args);
        }
    }

    private function getService($id)
    {
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        foreach ($this->config->services as $service) {
            if ($service->id === $id) {
                $this->generateService($service);
            }
        }

        if (!$this->container->has($id)) {
            throw new \Exception(sprintf("Services `%s` not found in config and container", $id));
        }

        return $this->container->get($id);
    }

    private function addServiceToContainer($id, $value)
    {
        $this->container->add($id, $value);
    }

    private function isParameter($value)
    {
        return preg_match("/^%[A-Za-z0-9_.-]+%$/", $value);
    }

    private function isService($value)
    {
        return preg_match("/^@[A-Za-z0-9_.-]+/", $value);
    }

    private function parseArguments($arguments)
    {
        $return = [];
        foreach ($arguments as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $return[$key] = (array)$this->parseArguments($value);
                continue;
            }
            if ($this->isParameter($value)) {
                $value = str_replace("%", "", $value);
                $return[$key] = $this->container->getParameter($value);
            } elseif ($this->isService($value)) {
                $value = str_replace("@", "", $value);
                $return[$key] = ($value === self::CONTAINER_ID) ? $this->container : $this->getService($value);
            } else {
                $return[$key] = $value;
            }
        }

        return is_array($arguments) ? $return : (object)$return;

    }

}