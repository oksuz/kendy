<?php

namespace Application;

use Application\Builder\ContainerBuilder;
use Application\Config\Configuration;
use Library\ContainerAware;
use Library\ErrorController;
use Library\Exceptions\ClassNotFoundException;
use Library\Exceptions\InvalidResponseTypeException;
use Library\Exceptions\NotFoundException;
use Library\Http\AbstractRequest;
use Library\Http\AbstractResponse;
use Library\Http\Request;
use Library\Routing\Action;
use Library\Utility\TextUtility;

class App implements ContainerAware
{
    protected $container;

    const CALLBACK_TYPE_AFTER = "afterCallback";
    const CALLBACK_TYPE_BEFORE = "beforeCallback";

    const NAME_FORMAT_REPOSITORY = "repository.%s";

    const DEFAULT_DATABASE_CONNECTION = "default.database.connection";

    public function __construct(AbstractRequest $request)
    {
        $this->container = new Container(Configuration::APP_ENV);
        $this->container->add("request", $request);
        $this->buildContainer();
    }

    protected function buildContainer()
    {
        $builder = new ContainerBuilder($this->container);
        $builder->build();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param \Exception $e
     * @return ErrorController
     */
    public function generateErrorController(\Exception $e)
    {
        return new ErrorController($this, $e);
    }

    /**
     * @param Action $action
     * @return null|AbstractResponse
     */
    protected function callAction(Action $action)
    {
        if (!empty($action) && ($method = $action->getMethod()) instanceof \Closure) {
            return call_user_func($method);
        } elseif (
            null !== ($class = $action->getClass()) &&
            null !== ($method = $action->getMethod()) &&
            is_callable([$class, $method])
        ) {
            $classInstance = new $class($this);
            return call_user_func_array(
                [$classInstance, $method],
                $action->getArgs()
            );
        }
    }

    /**
     * @throws InvalidResponseTypeException
     */
    final public function run()
    {
        $response = null;
        /** @var $action \Library\Routing\Action */
        $action = $this->getContainer()->get("router")->findAction();
        if (null !== $action) {
            try {
                $this->callCallback(self::CALLBACK_TYPE_AFTER, [$this]);
                $response = $this->callAction($action);
                $this->callCallback(self::CALLBACK_TYPE_BEFORE, [$this, $response]);
            } catch (\Exception $e) {
                $this->get("logger")->error(sprintf("%s occurred while processing request", get_class($e)), ["action" => $action]);
                $response = $this->generateErrorController($e)->error();
            }
        } else {
            $response = $this->generateErrorController(new NotFoundException("Not found"))->error();
        }

        if ($response instanceof AbstractResponse) {
            echo $response->getResponse();
            return;
        }

        throw new InvalidResponseTypeException("Return type must be \\Library\\Http\\AbstractResponse");
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->get("request");
    }

    /**
     * @param $service String serviceID
     * @return mixed
     * @throws \Exception
     */
    public function get($service)
    {
        if (!$this->container->has($service)) {
            throw new \Exception(sprintf("Service %s not found", $service));
        }
        return $this->container->get($service);
    }


    /**
     * @param $name String
     * @param $connection string id
     * @return null
     * @throws ClassNotFoundException
     * @throws \Exception
     *
     * @deprecated
     * declare repositories on services.json
     */
    public function getRepository($name, $connection = App::DEFAULT_DATABASE_CONNECTION)
    {
        $id = sprintf(self::NAME_FORMAT_REPOSITORY, strtolower($name)); // eg: repository.package
        if ($this->getContainer()->has($id)) {
            return $this->getContainer()->get($id);
        }

        $className = sprintf("Application\\Repository\\%sRepository", TextUtility::classify($name));
        if (!class_exists($className)) {
            throw new ClassNotFoundException(sprintf("Requested repository not found %s", $className));
        }

        $connection = $this->get($connection);

        $classInstance = new $className($connection);
        $this->getContainer()->add($id, $classInstance);

        return $classInstance;
    }


    protected function callCallback($type, array $args = [])
    {
        /** @var \Library\Routing\Route $route; */
        $route = $this->container->get("router")->getRoute();
        switch ($type) {
            case self::CALLBACK_TYPE_AFTER;
                $callback = $route->getAfterCallback();
                if (null !== $route && $callback instanceof \Closure || is_callable($callback)) {
                    $this->get("logger")->info(sprintf("Calling callback type: %s", $type), ["args" => $args, "route" => $route]);
                    return call_user_func_array($callback, $args);
                }
                break;

            case self::CALLBACK_TYPE_BEFORE:
                $callback = $route->getBeforeCallback();
                if (null !== $route && $route->getBeforeCallback() instanceof \Closure || is_callable($callback)) {
                    $this->get("logger")->info(sprintf("Calling callback type: %s", $type), ["args" => $args, "route" => $route]);
                    return call_user_func_array($callback, $args);
                }
                break;
        }

        return null;
    }




}