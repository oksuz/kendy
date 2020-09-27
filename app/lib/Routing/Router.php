<?php

namespace Library\Routing;

use Library\Http\AbstractRequest;

class Router
{
    protected $request;
    protected $absolutePath;

    public function __construct(AbstractRequest $request)
    {
        $this->request = $request;
        $this->absolutePath();
    }

    protected function findCurrentRoute()
    {
        $routes = Routes::getRequestMethodRoutes($this->request->server()->get("REQUEST_METHOD"));
        if (empty($routes)) {
            return null;
        }

        $currentRoute = null;
        /** @var $route Route */
        foreach ($routes as $route) {
            $matches = [];
            if (preg_match("@^". $route->getPath()."$@", $this->absolutePath, $matches)) {
                $currentRoute = $route;
                array_shift($matches);
                $currentRoute->setArgs($matches);
            }
        }

        return $currentRoute;
    }

    public function findAction()
    {
        $currentRoute = $this->findCurrentRoute();

        if (null === $currentRoute) {
            return null;
        }

        $action = $currentRoute->getAction();

        if (is_callable($action)) {
            return new Action(null, $currentRoute->getAction(), $currentRoute->getArgs(), $this->absolutePath);
        }

        if (false !== strpos($action, "@")) {
            list($class, $method) = array_map("trim", explode("@", $action, 2));
            $class = sprintf("Application\\Controller\\%sController", $class);
            $method = sprintf("%sAction", $method);
            return new Action($class, $method, $currentRoute->getArgs(), $this->absolutePath);

        }

        throw new \BadMethodCallException("Unknown method call");
    }

    public function getRoute()
    {
        // @FIXME: throw exception here when findCurrentRoute return null
        return $this->findCurrentRoute();
    }

    protected function absolutePath()
    {
        $this->request->server()->get("PATH_INFO");
        if (null !== $this->request->server()->get("PATH_INFO")) {
            $this->absolutePath = $this->request->server()->get("PATH_INFO");
        }

        $requestUri = $this->request->server()->get("REQUEST_URI");
        if (null !== $requestUri) {
            if (false !== strpos($requestUri, "?")) {
                $explodedRequestUri = explode("?", $requestUri);
                $requestUri = current($explodedRequestUri);

            }
            $this->absolutePath = $requestUri;
        }
    }

}