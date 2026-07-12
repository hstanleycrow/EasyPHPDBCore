<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\Model;
use hstanleycrow\EasyPHPDBCore\Exception\QueryException;
use hstanleycrow\EasyPHPDBCore\Tests\Support\SqliteConnection;
use hstanleycrow\EasyPHPDBCore\Tests\Support\UserModel;

final class ModelTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new SqliteConnection();
    }

    public function testCreateReturnsLastInsertId(): void
    {
        $user = new UserModel($this->connection);

        $id = $user->create(['name' => 'Jorge', 'username' => 'jorge', 'active' => 'S'])->lastInsertId();

        $this->assertSame(2, $id);
    }

    public function testGetByIdReturnsRow(): void
    {
        $user = new UserModel($this->connection);

        $row = $user->getById(1);

        $this->assertIsArray($row);
        $this->assertSame('hstanleycrow', $row['username']);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $user = new UserModel($this->connection);

        $this->assertNull($user->getById(999));
    }

    public function testGetRecordsWithCustomQueryAndBindings(): void
    {
        $user = new UserModel($this->connection);

        $rows = $user->query('SELECT name FROM users WHERE active = ?')->getRecords(['S']);

        $this->assertCount(1, $rows);
        $this->assertSame('Harold', $rows[0]['name']);
    }

    public function testUpdateChangesRow(): void
    {
        $user = new UserModel($this->connection);

        $user->update(['name' => 'Updated'], ['id' => 1]);

        $this->assertSame('Updated', $user->getById(1)['name']);
    }

    public function testDeleteRemovesRow(): void
    {
        $user = new UserModel($this->connection);

        $user->delete(['id' => 1]);

        $this->assertNull($user->getById(1));
    }

    public function testTransactionCommitPersistsChanges(): void
    {
        $user = new UserModel($this->connection);

        $user->beginTransaction();
        $user->create(['name' => 'Ana', 'username' => 'ana', 'active' => 'S']);
        $user->commit();

        $rows = $user->query('SELECT id FROM users')->getRecords();
        $this->assertCount(2, $rows);
    }

    public function testTransactionRollbackDiscardsChanges(): void
    {
        $user = new UserModel($this->connection);

        $user->beginTransaction();
        $user->create(['name' => 'Ana', 'username' => 'ana', 'active' => 'S']);
        $user->rollback();

        $rows = $user->query('SELECT id FROM users')->getRecords();
        $this->assertCount(1, $rows);
    }

    public function testThrowsWhenTableIsNotDefined(): void
    {
        $model = new Model($this->connection);

        $this->expectException(QueryException::class);

        $model->create(['name' => 'x']);
    }
}
