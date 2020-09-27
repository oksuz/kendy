<?php

namespace Library;

use Doctrine\DBAL\Connection;

abstract class AbstractRepository
{
    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}