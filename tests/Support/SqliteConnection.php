<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests\Support;

use PDO;
use hstanleycrow\EasyPHPDBCore\Connection\IConnection;

/**
 * In-memory SQLite adapter used to exercise the CRUD classes without a MySQL server.
 */
final class SqliteConnection implements IConnection
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->migrate();
    }

    public function isConnected(): bool
    {
        return true;
    }

    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }

    private function migrate(): void
    {
        $this->pdo->exec(
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                username TEXT NOT NULL UNIQUE,
                active TEXT NOT NULL DEFAULT \'S\'
            )'
        );

        $this->pdo->exec(
            "INSERT INTO users (name, username, active) VALUES ('Harold', 'hstanleycrow', 'S')"
        );
    }
}
