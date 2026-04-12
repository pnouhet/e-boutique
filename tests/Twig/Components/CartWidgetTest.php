<?php

namespace App\Tests\Twig\Components;

use App\Entity\Category;
use App\Entity\Product;
use App\Tests\Controller\ControllerTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CartWidgetTest extends ControllerTestCase
{
    public function testCartBadgeHiddenWhenCartEmpty(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('[data-testid="cart-badge"]');
    }

    public function testCartBadgeShownAfterAddingProduct(): void
    {
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $category = (new Category())->setName('Test')->setDescription('');
        $product = (new Product())
            ->setName('Widget Test')->setDescription('desc')
            ->setPriceHT('10.00')->setAvailable(true)->setCategory($category);
        $em->persist($category);
        $em->persist($product);
        $em->flush();
        $productId = $product->getId();
        static::ensureKernelShutdown();

        $client = static::createClient();
        $client->request('POST', '/cart/add/' . $productId, ['quantity' => 1]);
        $client->request('GET', '/');
        $this->assertSelectorExists('[data-testid="cart-badge"]');
    }
}
