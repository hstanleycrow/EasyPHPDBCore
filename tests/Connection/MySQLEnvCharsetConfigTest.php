<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests\Connection;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvCharsetConfig;
use hstanleycrow\EasyPHPDBCore\Exception\ConnectionException;

final class MySQLEnvCharsetConfigTest extends TestCase
{
    public function testGetCharsetReturnsEnvValue(): void
    {
        $config = new MySQLEnvCharsetConfig(['DATABASE_CHARSET' => "SET NAMES 'utf8mb4'"]);

        $this->assertSame("SET NAMES 'utf8mb4'", $config->getCharset());
    }

    public function testThrowsWhenCharsetIsMissing(): void
    {
        $config = new MySQLEnvCharsetConfig([]);

        $this->expectException(ConnectionException::class);

        $config->getCharset();
    }
}
