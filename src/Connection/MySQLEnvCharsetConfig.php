<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Connection;

use hstanleycrow\EasyPHPDBCore\Exception\ConnectionException;

class MySQLEnvCharsetConfig implements ICharsetConfig
{
    public function __construct(private array $env)
    {
    }

    public function getCharset(): string
    {
        if (!array_key_exists('DATABASE_CHARSET', $this->env)) {
            throw new ConnectionException('Missing required environment variable: DATABASE_CHARSET');
        }

        return (string) $this->env['DATABASE_CHARSET'];
    }
}
