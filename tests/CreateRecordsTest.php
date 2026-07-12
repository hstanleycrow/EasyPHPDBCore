<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\CreateRecords;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;
use hstanleycrow\EasyPHPDBCore\Tests\Support\SqliteConnection;

final class CreateRecordsTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new SqliteConnection();
    }

    public function testExecuteInsertsRowAndReturnsLastInsertId(): void
    {
        $createRecords = new CreateRecords($this->connection, 'users');

        $id = $createRecords->execute([
            'name' => 'Jorge',
            'username' => 'jorge',
            'active' => 'S',
        ]);

        $this->assertSame(2, $id);
        $this->assertSame(2, $createRecords->lastInsertId());
    }

    public function testExecuteThrowsQueryExceptionOnUnknownColumn(): void
    {
        $createRecords = new CreateRecords($this->connection, 'users');

        $this->expectException(QueryException::class);

        $createRecords->execute(['nonexistent_column' => 'value']);
    }
}
