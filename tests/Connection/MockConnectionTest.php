<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests\Connection;

use PHPUnit\Framework\TestCase;
use hstanleycrow\EasyPHPDBCore\Connection\MockConnection;

final class MockConnectionTest extends TestCase
{
    public function testIsConnectedAlwaysReturnsTrue(): void
    {
        $this->assertTrue((new MockConnection())->isConnected());
    }

    public function testGetConnectionReturnsNull(): void
    {
        $this->assertNull((new MockConnection())->getConnection());
    }
}
