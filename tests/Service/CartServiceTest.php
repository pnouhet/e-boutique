<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Service\CartService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CartServiceTest extends TestCase
{
    private CartService $cart;
    private Session $session;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($this->session);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->cart = new CartService($requestStack);
    }

    private function makeProduct(int $id, string $price): Product
    {
        $product = new Product();
        // Use reflection to set the private id (no setter for id)
        $ref = new \ReflectionProperty(Product::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($product, $id);
        $product->setPriceHT($price);
        $product->setName('Product ' . $id);
        return $product;
    }

    public function testGetCartReturnsEmptyArrayInitially(): void
    {
        $this->assertSame([], $this->cart->getCart());
    }

    public function testAddItemAddsProductToCart(): void
    {
        $product = $this->makeProduct(1, '10.00');
        $this->cart->addItem($product, 2);

        $cart = $this->cart->getCart();
        $this->assertCount(1, $cart);
        $this->assertSame(1, $cart[0]['product_id']);
        $this->assertSame(2, $cart[0]['quantity']);
        $this->assertSame('10.00', $cart[0]['unit_price']);
    }

    public function testAddItemIncreasesQuantityIfProductAlreadyInCart(): void
    {
        $product = $this->makeProduct(1, '10.00');
        $this->cart->addItem($product, 2);
        $this->cart->addItem($product, 3);

        $cart = $this->cart->getCart();
        $this->assertCount(1, $cart);
        $this->assertSame(5, $cart[0]['quantity']);
    }

    public function testUpdateQuantityChangesQuantityForExistingItem(): void
    {
        $product = $this->makeProduct(1, '10.00');
        $this->cart->addItem($product, 2);
        $this->cart->updateQuantity(1, 7);

        $cart = $this->cart->getCart();
        $this->assertSame(7, $cart[0]['quantity']);
    }

    public function testRemoveItemRemovesProductFromCart(): void
    {
        $p1 = $this->makeProduct(1, '10.00');
        $p2 = $this->makeProduct(2, '20.00');
        $this->cart->addItem($p1, 1);
        $this->cart->addItem($p2, 1);
        $this->cart->removeItem(1);

        $cart = $this->cart->getCart();
        $this->assertCount(1, $cart);
        $this->assertSame(2, $cart[0]['product_id']);
    }

    public function testClearEmptiesCart(): void
    {
        $product = $this->makeProduct(1, '10.00');
        $this->cart->addItem($product, 3);
        $this->cart->clear();

        $this->assertSame([], $this->cart->getCart());
    }

    public function testGetTotalReturnsSumOfLineAmounts(): void
    {
        $p1 = $this->makeProduct(1, '10.00');
        $p2 = $this->makeProduct(2, '5.50');
        $this->cart->addItem($p1, 2); // 20.00
        $this->cart->addItem($p2, 4); // 22.00

        $this->assertEqualsWithDelta(42.00, $this->cart->getTotal(), 0.001);
    }

    public function testGetItemCountReturnsNumberOfDistinctProducts(): void
    {
        $p1 = $this->makeProduct(1, '10.00');
        $p2 = $this->makeProduct(2, '5.00');
        $this->cart->addItem($p1, 3);
        $this->cart->addItem($p2, 1);

        $this->assertSame(2, $this->cart->getItemCount());
    }
}
