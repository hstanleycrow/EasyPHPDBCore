<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Connection;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use hstanleycrow\EasyPHPDBCore\Exception\ConnectionException;

class MySQLPDOConnection implements IConnection
{
    private PDO $connection;
    private bool $connectionStatus = false;
    private LoggerInterface $logger;

    public function __construct(
        private IConfig $config,
        private ICharsetConfig $charsetConfig,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->connect();
    }

    public function getConnection(): ?PDO
    {
        return $this->connection ?? null;
    }

    public function isConnected(): bool
    {
        return $this->connectionStatus;
    }

    private function connect(): void
    {
        try {
            $this->connection = new PDO(
                $this->buildDSN(),
                $this->config->getUser(),
                $this->config->getPassword(),
                $this->getOptions()
            );
            $this->connectionStatus = true;
        } catch (PDOException $e) {
            $this->connectionStatus = false;
            $this->logger->error('Database connection failed: ' . $e->getMessage());
            throw new ConnectionException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    private function buildDSN(): string
    {
        return 'mysql:dbname=' . $this->config->getDatabaseName()
            . ';port=' . $this->config->getPort()
            . ';host=' . $this->config->getHost();
    }

    private function getOptions(): array
    {
        return [
            PDO::MYSQL_ATTR_INIT_COMMAND => $this->charsetConfig->getCharset(),
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
    }
}
