<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\DeleteRecords;
use hstanleycrow\EasyPHPDBCore\ReadRecords;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;
use hstanleycrow\EasyPHPDBCore\Tests\Support\SqliteConnection;

final class DeleteRecordsTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new SqliteConnection();
    }

    public function testExecuteDeletesMatchingRow(): void
    {
        $deleteRecords = new DeleteRecords($this->connection, 'users');

        $result = $deleteRecords->execute(['id' => 1]);

        $this->assertTrue($result);
        $this->assertSame(1, $deleteRecords->getAffectedRows());

        $rows = (new ReadRecords($this->connection, 'SELECT id FROM users'))->execute();
        $this->assertSame([], $rows);
    }

    public function testExecuteThrowsQueryExceptionOnUnknownColumn(): void
    {
        $deleteRecords = new DeleteRecords($this->connection, 'users');

        $this->expectException(QueryException::class);

        $deleteRecords->execute(['missing' => 'x']);
    }
}
