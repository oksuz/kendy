<?php

namespace Library\Routing;

class Routes
{
    protected static $collection = ["get" => null, "post" => null, "put" => null, "delete" => null];

    public static function get($path, $action, $afterCallback = null, $beforeCallback = null)
    {
        return self::$collection["get"][$path] = self::makeRoute($path, $action, "GET", $afterCallback, $beforeCallback);
    }

    public static function post($path, $action, $afterCallback = null, $beforeCallback = null)
    {
        return self::$collection["post"][$path] = self::makeRoute($path, $action, "POST", $afterCallback, $beforeCallback);
    }

    public static function put($path, $action, $afterCallback = null, $beforeCallback = null)
    {
        return self::$collection["put"][$path] = self::makeRoute($path, $action, "PUT", $afterCallback, $beforeCallback);
    }

    public static function delete($path, $action, $afterCallback = null, $beforeCallback = null)
    {
        return self::$collection["delete"][$path] = self::makeRoute($path, $action, "DELETE", $afterCallback, $beforeCallback);
    }

    protected static function makeRoute($path, $action, $method, $afterCallback = null, $beforeCallback = null)
    {
        $route = new Route($path, $method, $action);
        $route->setAfterCallback($afterCallback);
        $route->setBeforeCallback($beforeCallback);
        return $route;
    }

    public static function getRequestMethodRoutes($requestMethod)
    {
        if (in_array($requestMethod, ["GET", "POST", "PUT", "DELETE"])) {
            return self::$collection[strtolower($requestMethod)];
        }

        throw new \InvalidArgumentException("Unknown request method");
    }

}