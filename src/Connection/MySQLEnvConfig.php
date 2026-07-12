<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Connection;

use hstanleycrow\EasyPHPDBCore\Exception\ConnectionException;

class MySQLEnvConfig implements IConfig
{
    public function __construct(private array $env)
    {
    }

    public function getUser(): string
    {
        return $this->require('DATABASE_USERNAME');
    }

    public function getPassword(): string
    {
        return $this->require('DATABASE_PASSWORD');
    }

    public function getDatabaseName(): string
    {
        return $this->require('DATABASE_NAME');
    }

    public function getPort(): string
    {
        return $this->require('DATABASE_PORT');
    }

    public function getHost(): string
    {
        return $this->require('DATABASE_HOST');
    }

    private function require(string $key): string
    {
        if (!array_key_exists($key, $this->env)) {
            throw new ConnectionException("Missing required environment variable: {$key}");
        }

        return (string) $this->env[$key];
    }
}
