<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class CartControllerTest extends ControllerTestCase
{
    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $cat = (new Category())->setName('Test');
        $em->persist($cat);

        $product = (new Product())
            ->setName('Widget')
            ->setPriceHT('29.90')
            ->setCategory($cat);
        $em->persist($product);

        $em->flush();
        $this->productId = $product->getId();
        static::ensureKernelShutdown();
    }

    public function testCartPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart');
        $this->assertResponseIsSuccessful();
    }

    public function testCartPageShowsEmptyMessageWhenNoItems(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart');
        $this->assertSelectorTextContains('body', 'vide');
    }

    public function testAddItemRedirectsToCart(): void
    {
        $client = static::createClient();
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 2]);
        $this->assertResponseRedirects('/cart');
    }

    public function testCartShowsItemAfterAdd(): void
    {
        $client = static::createClient();
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 2]);
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'Widget');
    }

    public function testUpdateItemRedirectsToCart(): void
    {
        $client = static::createClient();
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 1]);
        $client->request('POST', '/cart/update/' . $this->productId, ['quantity' => 5]);
        $this->assertResponseRedirects('/cart');
    }

    public function testRemoveItemRedirectsToCart(): void
    {
        $client = static::createClient();
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 1]);
        $client->request('POST', '/cart/remove/' . $this->productId);
        $this->assertResponseRedirects('/cart');
    }

    public function testCartIsEmptyAfterRemove(): void
    {
        $client = static::createClient();
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 1]);
        $client->request('POST', '/cart/remove/' . $this->productId);
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'vide');
    }
}
