<?php

namespace Library;

use Application\App;
use Library\Http\JsonResponse;

class ErrorController extends AbstractController
{
    protected $e;

    public function __construct(App $app, \Exception $e)
    {
        parent::__construct($app);
        $this->e = $e;
    }

    public function error($code = null, $data = [])
    {
        $exceptionClass = get_class($this->e);
        $this->get("logger")->info(
            "Error controller called with args",
            ["code" => $code, "data" => $data, "message" => $this->e->getMessage(), "exception" => $this->e]
        );

        $data["exception"] = (false !== strpos($exceptionClass, "\\")) ?
            substr(strrchr($exceptionClass, "\\"), 1) :
            $exceptionClass;

        $data["message"] = $this->e->getMessage();
        $data["status_code"] = $this->e->getCode();

        if ("dev" == $this->app->getContainer()->getEnv()) {
            $data["trace"] = $this->e->getTrace();
        }

        $code = (null === $code) ? 500 : $code;
        if ($this->e->getCode() > 200 && $this->e->getCode() < 550) {
            $code = $this->e->getCode();
        }


        return new JsonResponse($data, $code);
    }

}