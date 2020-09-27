<?php

namespace Application\Controller;

use Library\AbstractController;
use Library\Http\JsonResponse;

class AccessTokenController extends AbstractController
{

    public function postCreateAccessTokenAction()
    {
        // create access token  here
        return new JsonResponse([]);
    }

}