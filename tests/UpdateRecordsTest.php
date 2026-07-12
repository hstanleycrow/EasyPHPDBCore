<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\UpdateRecords;
use hstanleycrow\EasyPHPDBCore\ReadRecords;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;
use hstanleycrow\EasyPHPDBCore\Tests\Support\SqliteConnection;

final class UpdateRecordsTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new SqliteConnection();
    }

    public function testExecuteUpdatesMatchingRow(): void
    {
        $updateRecords = new UpdateRecords($this->connection, 'users');

        $result = $updateRecords->execute(['name' => 'Harold Crow'], ['id' => 1]);

        $this->assertTrue($result);
        $this->assertSame(1, $updateRecords->getAffectedRows());

        $rows = (new ReadRecords($this->connection, 'SELECT name FROM users WHERE id = ?'))->execute([1]);
        $this->assertSame('Harold Crow', $rows[0]['name']);
    }

    public function testExecuteThrowsQueryExceptionOnUnknownColumn(): void
    {
        $updateRecords = new UpdateRecords($this->connection, 'users');

        $this->expectException(QueryException::class);

        $updateRecords->execute(['missing' => 'x'], ['id' => 1]);
    }
}
