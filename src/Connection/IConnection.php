<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Connection;

use PDO;

interface IConnection
{
    public function isConnected(): bool;

    public function getConnection(): ?PDO;
}
