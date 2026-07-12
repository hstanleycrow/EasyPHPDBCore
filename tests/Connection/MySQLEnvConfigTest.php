<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests\Connection;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvConfig;
use hstanleycrow\EasyPHPDBCore\Exception\ConnectionException;

final class MySQLEnvConfigTest extends TestCase
{
    private array $env = [
        'DATABASE_HOST' => '127.0.0.1',
        'DATABASE_NAME' => 'db_test',
        'DATABASE_USERNAME' => 'root',
        'DATABASE_PASSWORD' => 'secret',
        'DATABASE_PORT' => '3306',
    ];

    public function testGettersReturnEnvValues(): void
    {
        $config = new MySQLEnvConfig($this->env);

        $this->assertSame('127.0.0.1', $config->getHost());
        $this->assertSame('db_test', $config->getDatabaseName());
        $this->assertSame('root', $config->getUser());
        $this->assertSame('secret', $config->getPassword());
        $this->assertSame('3306', $config->getPort());
    }

    public function testThrowsWhenRequiredKeyIsMissing(): void
    {
        $config = new MySQLEnvConfig([]);

        $this->expectException(ConnectionException::class);

        $config->getHost();
    }
}
