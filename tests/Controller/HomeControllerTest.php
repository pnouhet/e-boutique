<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class HomeControllerTest extends ControllerTestCase
{
    public function testHomePageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testHomePageShowsCategoryCards(): void
    {
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $cat = (new Category())->setName('Électronique')->setDescription('Gadgets');
        $em->persist($cat);
        $em->flush();
        static::ensureKernelShutdown();

        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Électronique');
    }
}
