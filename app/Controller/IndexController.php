<?php

namespace Application\Controller;

use Library\AbstractController;
use Library\Http\JsonResponse;

class IndexController extends AbstractController
{
    public function indexAction()
    {
        return new JsonResponse(["status" => "Api is running"]);
    }
}