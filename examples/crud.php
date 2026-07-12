<?php

declare(strict_types=1);

/**
 * Basic CRUD example running against a real MySQL/MariaDB database.
 *
 * Setup:
 *   composer install
 *   cp .env.example .env   # then fill in your credentials
 *   php examples/crud.php
 *
 * The script creates a demo user, reads it, updates it, reads it again,
 * and finally deletes it, so it leaves the database as it found it.
 */

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use hstanleycrow\EasyPHPDBCore\Examples\User;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvConfig;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLPDOConnection;
use hstanleycrow\EasyPHPDBCore\Connection\MySQLEnvCharsetConfig;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->load();

// Optional PSR-3 logger. Pass null to disable logging entirely.
$logger = new Logger('easyphpdbcore');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::ERROR));

$connection = new MySQLPDOConnection(
    new MySQLEnvConfig($_ENV),
    new MySQLEnvCharsetConfig($_ENV),
    $logger
);

$user = new User($connection, $logger);

// CREATE
$id = $user->create([
    'name' => 'Demo User',
    'username' => 'demo_user_' . time(),
    'password' => password_hash('secret', PASSWORD_DEFAULT),
    'active' => 'S',
])->lastInsertId();
echo "Created user #{$id}" . PHP_EOL;

// READ
$record = $user->getById($id);
echo 'Read: ' . json_encode($record) . PHP_EOL;

// UPDATE
$user->update(['name' => 'Demo User (edited)'], ['id' => $id]);
echo 'Updated: ' . json_encode($user->getById($id)) . PHP_EOL;

// LIST
echo 'Active users: ' . json_encode($user->getActive()) . PHP_EOL;

// DELETE
$user->delete(['id' => $id]);
echo "Deleted user #{$id}" . PHP_EOL;
