<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore;

use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use hstanleycrow\EasyPHPDBCore\Connection\IConnection;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;

class CreateRecords
{
    private LoggerInterface $logger;
    private int $lastInsertId = 0;

    public function __construct(
        private IConnection $connection,
        private string $table,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function execute(array $fieldsList): int
    {
        try {
            $statement = $this->connection->getConnection()->prepare($this->buildQuery($fieldsList));
            if ($statement->execute(array_values($fieldsList))) {
                $this->lastInsertId = (int) $this->connection->getConnection()->lastInsertId();
            }
        } catch (PDOException $e) {
            $this->logger->error(sprintf(
                'Insert failed on table "%s": %s. Values: %s',
                $this->table,
                $e->getMessage(),
                json_encode(array_values($fieldsList))
            ));
            throw new QueryException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $this->lastInsertId;
    }

    public function lastInsertId(): int
    {
        return $this->lastInsertId;
    }

    private function buildQuery(array $fieldsList): string
    {
        $columns = implode(', ', array_keys($fieldsList));
        $placeholders = implode(', ', array_fill(0, count($fieldsList), '?'));

        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }
}
