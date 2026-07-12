<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Connection;

use PDO;

class MockConnection implements IConnection
{
    public function isConnected(): bool
    {
        return true;
    }

    public function getConnection(): ?PDO
    {
        return null;
    }
}
