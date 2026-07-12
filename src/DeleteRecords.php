<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore;

use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use hstanleycrow\EasyPHPDBCore\Connection\IConnection;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;

class DeleteRecords
{
    private LoggerInterface $logger;
    private int $affectedRows = 0;

    public function __construct(
        private IConnection $connection,
        private string $table,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function execute(array $whereConditions): bool
    {
        try {
            $statement = $this->connection->getConnection()->prepare($this->buildQuery($whereConditions));
            $result = $statement->execute(array_values($whereConditions));
            $this->affectedRows = $statement->rowCount();

            return $result;
        } catch (PDOException $e) {
            $this->logger->error(sprintf(
                'Delete failed on table "%s": %s. Where: %s',
                $this->table,
                $e->getMessage(),
                json_encode($whereConditions)
            ));
            throw new QueryException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    private function buildQuery(array $whereConditions): string
    {
        $whereString = implode(' AND ', array_map(
            static fn (string $field): string => "{$field} = ?",
            array_keys($whereConditions)
        ));

        return "DELETE FROM {$this->table} WHERE {$whereString}";
    }
}
