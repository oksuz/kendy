<?php
/**
 * You can add your own routes here like below :)
 * Routes supports also post,get,delete
 *
 */

namespace Application\RouteDeclarations;

use Application\App;
use Application\Model\User;
use Library\Exceptions\AuthenticationException;
use Library\Exceptions\ConsumerKeyInvalidException;
use Library\Routing\Routes;

$consumerKeyController = function (App $app) {
    $key = $app->get("request")->headers()->get("x-consumer-key");
    $authenticator = $app->get("security.authenticator");
    if (!$authenticator->consumerKeyIsValid($key)) {
        throw new ConsumerKeyInvalidException("Consumer key is invalid");
    }
};

$authenticationController = function (App $app) use ($consumerKeyController) {
    $consumerKeyController($app);
    $token = $app->get("request")->headers()->get("x-access-token");
    $user = $app->get("security.authenticator")->authenticateToken($token);
    if ($user instanceof User) {
        $app->getContainer()->add("user", $user);
    }

    throw new AuthenticationException("Token is invalid");
};

Routes::get("/", "Index@index", $consumerKeyController);

Routes::post("/access-token", "AccessToken@postCreateAccessToken", $consumerKeyController);

