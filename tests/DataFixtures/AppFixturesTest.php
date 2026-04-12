<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixturesTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $hasher   = static::getContainer()->get(UserPasswordHasherInterface::class);
        $fixtures = new AppFixtures($hasher);

        $loader = new Loader();
        $loader->addFixture($fixtures);

        $executor = new ORMExecutor($this->em, new ORMPurger($this->em));
        $executor->execute($loader->getFixtures());
    }

    public function testAdminUserIsCreated(): void
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@boutique.fr']);
        $this->assertNotNull($admin);
        $this->assertContains('ROLE_ADMIN', $admin->getRoles());
    }

    public function testTwoRegularUsersAreCreated(): void
    {
        $users = $this->em->getRepository(User::class)->findAll();
        // 1 admin + 2 regular = 3 total
        $this->assertCount(3, $users);
    }

    public function testThreeCategoriesAreCreated(): void
    {
        $categories = $this->em->getRepository(Category::class)->findAll();
        $this->assertCount(3, $categories);
    }

    public function testTenProductsAreCreated(): void
    {
        $products = $this->em->getRepository(Product::class)->findAll();
        $this->assertCount(10, $products);
    }

    public function testRegularUsersHaveDeliveryAddress(): void
    {
        $regular = $this->em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.email != :admin')
            ->setParameter('admin', 'admin@boutique.fr')
            ->getQuery()
            ->getResult();

        foreach ($regular as $user) {
            $this->assertGreaterThan(0, $user->getAddresses()->count(), "User {$user->getEmail()} has no address");
        }
    }

    public function testProductsHaveCategory(): void
    {
        $products = $this->em->getRepository(Product::class)->findAll();
        foreach ($products as $product) {
            $this->assertNotNull($product->getCategory(), "Product {$product->getName()} has no category");
        }
    }
}
