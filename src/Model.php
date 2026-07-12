<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use hstanleycrow\EasyPHPDBCore\Connection\IConnection;

class Model
{
    protected ?string $table = null;
    protected string $primaryKey = 'id';
    protected string $query = '';
    protected ?int $lastInsertId = null;
    protected LoggerInterface $logger;

    public function __construct(
        protected IConnection $connection,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function query(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function create(array $fieldsList): self
    {
        $this->lastInsertId = (new CreateRecords($this->connection, $this->requireTable(), $this->logger))
            ->execute($fieldsList);

        return $this;
    }

    public function lastInsertId(): ?int
    {
        return $this->lastInsertId;
    }

    /**
     * @param array<int|string, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function getRecords(array $bindings = []): array
    {
        return (new ReadRecords($this->connection, $this->query, $this->logger))->execute($bindings);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getById(int|string $id): ?array
    {
        $this->query = "SELECT * FROM {$this->requireTable()} WHERE {$this->primaryKey} = ?";
        $records = $this->getRecords([$id]);

        return $records[0] ?? null;
    }

    public function update(array $updateFields, array $whereConditions): self
    {
        (new UpdateRecords($this->connection, $this->requireTable(), $this->logger))
            ->execute($updateFields, $whereConditions);

        return $this;
    }

    public function delete(array $whereConditions): self
    {
        (new DeleteRecords($this->connection, $this->requireTable(), $this->logger))
            ->execute($whereConditions);

        return $this;
    }

    public function beginTransaction(): void
    {
        $this->connection->getConnection()->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->getConnection()->commit();
    }

    public function rollback(): void
    {
        $this->connection->getConnection()->rollBack();
    }

    private function requireTable(): string
    {
        if ($this->table === null || $this->table === '') {
            throw new Exception\QueryException('No table has been defined for this model.');
        }

        return $this->table;
    }
}
