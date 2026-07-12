<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore;

use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use hstanleycrow\EasyPHPDBCore\Connection\IConnection;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;

class UpdateRecords
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

    public function execute(array $updateFields, array $whereConditions): bool
    {
        try {
            $statement = $this->connection->getConnection()->prepare($this->buildQuery($updateFields, $whereConditions));
            $result = $statement->execute(array_merge(array_values($updateFields), array_values($whereConditions)));
            $this->affectedRows = $statement->rowCount();

            return $result;
        } catch (PDOException $e) {
            $this->logger->error(sprintf(
                'Update failed on table "%s": %s. Fields: %s. Where: %s',
                $this->table,
                $e->getMessage(),
                json_encode($updateFields),
                json_encode($whereConditions)
            ));
            throw new QueryException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    private function buildQuery(array $updateFields, array $whereConditions): string
    {
        $setString = implode(', ', array_map(
            static fn (string $field): string => "{$field} = ?",
            array_keys($updateFields)
        ));

        $whereString = implode(' AND ', array_map(
            static fn (string $field): string => "{$field} = ?",
            array_keys($whereConditions)
        ));

        return "UPDATE {$this->table} SET {$setString} WHERE {$whereString}";
    }
}
