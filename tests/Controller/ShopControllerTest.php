<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ShopControllerTest extends ControllerTestCase
{
    private int $categoryId;
    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $cat = (new Category())->setName('Électronique')->setDescription('Gadgets');
        $em->persist($cat);

        $product = (new Product())
            ->setName('Laptop Pro')
            ->setDescription('Un super laptop')
            ->setPriceHT('999.99')
            ->setCategory($cat);
        $em->persist($product);

        $em->flush();
        $this->categoryId = $cat->getId();
        $this->productId  = $product->getId();
        static::ensureKernelShutdown();
    }

    public function testCategoryPageShowsProducts(): void
    {
        $client = static::createClient();
        $client->request('GET', '/category/' . $this->categoryId);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Laptop Pro');
    }

    public function testUnknownCategoryReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/category/99999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testProductPageShowsDetails(): void
    {
        $client = static::createClient();
        $client->request('GET', '/product/' . $this->productId);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Laptop Pro');
        $this->assertSelectorTextContains('body', '999');
    }

    public function testUnknownProductReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/product/99999');
        $this->assertResponseStatusCodeSame(404);
    }
}
