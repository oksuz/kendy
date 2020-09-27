<?php

namespace Library;
use \Doctrine\DBAL\Logging\SQLLogger as DoctrineSqlLogger;
use Psr\Log\LoggerInterface;

class SqlLogger implements DoctrineSqlLogger
{

    /** @var $logger LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->logger->info(sprintf("Executing SQL: %s", $sql), $params);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        // TODO: Implement stopQuery() method.
    }


}