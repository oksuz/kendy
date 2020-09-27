<?php

namespace Library;

use Application\App;

abstract class AbstractController implements ContainerAware
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getContainer()
    {
        return $this->app->getContainer();
    }

    protected function getRequest()
    {
        return $this->app->getRequest();
    }

    protected function get($id)
    {
        return $this->app->get($id);
    }

    protected function getRepository($name, $connection = App::DEFAULT_DATABASE_CONNECTION)
    {
        return $this->app->getRepository($name, $connection);
    }
}