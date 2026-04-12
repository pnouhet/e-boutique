<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CheckoutControllerTest extends ControllerTestCase
{
    private int $productId;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $cat = (new Category())->setName('Test');
        $em->persist($cat);

        $product = (new Product())
            ->setName('Widget')
            ->setPriceHT('49.90')
            ->setCategory($cat);
        $em->persist($product);

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setName('Alice')->setEmail('alice@test.com')->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'Password1!'));

        $address = (new CustomerAddress())
            ->setType('shipping')->setName('Alice')->setFirstName('')
            ->setAddress('1 rue Test')->setCp('75001')->setCity('Paris')->setCountry('France');
        $user->addAddress($address);
        $em->persist($user);

        $em->flush();
        $this->productId = $product->getId();
        $this->user = $user;
        static::ensureKernelShutdown();
    }

    public function testCheckoutRedirectsToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/checkout');
        $this->assertResponseRedirects('/login');
    }

    public function testCheckoutPageShowsCartForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->user);
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 2]);
        $client->request('GET', '/checkout');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Widget');
        $this->assertSelectorTextContains('body', '49');
    }

    public function testConfirmOrderCreatesOrderAndRedirectsToSuccess(): void
    {
        $client = static::createClient();
        $client->loginUser($this->user);
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 2]);
        $client->request('POST', '/checkout/confirm');

        $this->assertResponseRedirects();
        $location = $client->getResponse()->headers->get('Location');
        $this->assertStringContainsString('/checkout/success/', $location);

        // Verify order was persisted
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $order = $em->getRepository(Order::class)->findOneBy(['user' => $this->user]);
        $this->assertNotNull($order);
        $this->assertCount(1, $order->getOrderLines());
        $this->assertSame(2, $order->getOrderLines()->first()->getQuantity());
        static::ensureKernelShutdown();
    }

    public function testSuccessPageShowsOrderNumber(): void
    {
        $client = static::createClient();
        $client->loginUser($this->user);
        $client->request('POST', '/cart/add/' . $this->productId, ['quantity' => 1]);
        $client->request('POST', '/checkout/confirm');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'confirmée');
    }
}
