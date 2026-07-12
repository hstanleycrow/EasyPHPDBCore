<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\ReadRecords;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;
use hstanleycrow\EasyPHPDBCore\Tests\Support\SqliteConnection;

final class ReadRecordsTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new SqliteConnection();
    }

    public function testExecuteReturnsRowsWithBoundParameters(): void
    {
        $readRecords = new ReadRecords($this->connection, 'SELECT id, name, username FROM users WHERE username = ?');

        $rows = $readRecords->execute(['hstanleycrow']);

        $this->assertCount(1, $rows);
        $this->assertSame(1, $readRecords->getAffectedRows());
        $this->assertSame('Harold', $rows[0]['name']);
    }

    public function testExecuteReturnsEmptyArrayWhenNoMatch(): void
    {
        $readRecords = new ReadRecords($this->connection, 'SELECT id FROM users WHERE username = ?');

        $rows = $readRecords->execute(['ghost']);

        $this->assertSame([], $rows);
        $this->assertSame(0, $readRecords->getAffectedRows());
    }

    public function testExecuteThrowsWhenQueryIsEmpty(): void
    {
        $readRecords = new ReadRecords($this->connection);

        $this->expectException(QueryException::class);

        $readRecords->execute();
    }

    public function testExecuteThrowsQueryExceptionOnInvalidSql(): void
    {
        $readRecords = new ReadRecords($this->connection, 'SELECT * FROM missing_table');

        $this->expectException(QueryException::class);

        $readRecords->execute();
    }
}
