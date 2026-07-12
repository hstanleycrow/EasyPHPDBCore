<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use hstanleycrow\EasyPHPDBCore\Connection\IConnection;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;

class ReadRecords
{
    private LoggerInterface $logger;
    private int $affectedRows = 0;

    public function __construct(
        private IConnection $connection,
        private ?string $query = null,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @param array<int|string, mixed> $bindings Positional (?) or named (:name) parameter values.
     * @return array<int, array<string, mixed>>
     */
    public function execute(array $bindings = []): array
    {
        if ($this->query === null || $this->query === '') {
            throw new QueryException('No query has been set to execute.');
        }

        try {
            $statement = $this->connection->getConnection()->prepare($this->query);
            $statement->execute($bindings);
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            $this->affectedRows = count($rows);

            return $rows;
        } catch (PDOException $e) {
            $this->logger->error(sprintf('Read failed. Query: %s. %s', $this->query, $e->getMessage()));
            throw new QueryException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }
}
