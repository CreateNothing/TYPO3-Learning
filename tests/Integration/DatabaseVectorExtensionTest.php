<?php

namespace App\Tests\Integration;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DatabaseVectorExtensionTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testVectorExtensionIsAvailable(): void
    {
        $connection = self::getContainer()->get(Connection::class);

        $connection->executeStatement('CREATE EXTENSION IF NOT EXISTS vector');
        $extname = $connection->fetchOne("SELECT extname FROM pg_extension WHERE extname = 'vector'");

        self::assertSame('vector', $extname);
    }
}
