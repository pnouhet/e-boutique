<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ControllerTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->cleanDatabase();
        static::ensureKernelShutdown();
    }

    protected function cleanDatabase(): void
    {
        $conn = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $conn->executeStatement('DELETE FROM order_line');
        $conn->executeStatement('DELETE FROM "order"');
        $conn->executeStatement('DELETE FROM customer_address');
        $conn->executeStatement('DELETE FROM "user"');
        $conn->executeStatement('DELETE FROM product');
        $conn->executeStatement('DELETE FROM category');
    }
}
