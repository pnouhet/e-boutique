<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Tests\Controller\ControllerTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminControllerTest extends ControllerTestCase
{
    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $em     = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $admin = new User();
        $admin->setName('Admin')->setEmail('admin@test.com')->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'Admin1!'));
        $em->persist($admin);

        $regular = new User();
        $regular->setName('User')->setEmail('user@test.com')->setRoles(['ROLE_USER']);
        $regular->setPassword($hasher->hashPassword($regular, 'User1!'));
        $em->persist($regular);

        $em->flush();
        $this->admin       = $admin;
        $this->regularUser = $regular;
        static::ensureKernelShutdown();
    }

    public function testAdminRedirectsToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminDeniedForRegularUser(): void
    {
        $client = static::createClient();
        $client->loginUser($this->regularUser);
        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminDashboardAccessibleForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin');
        // Dashboard redirects to first CRUD (Category)
        $this->assertResponseRedirects('/admin/category');
    }

    public function testCategoryCrudIndexAccessibleForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/category');
        $this->assertResponseIsSuccessful();
    }

    public function testProductCrudIndexAccessibleForAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/product');
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanCreateCategory(): void
    {
        $client = static::createClient();
        $client->loginUser($this->admin);

        $crawler = $client->request('GET', '/admin/category/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form([
            'Category[name]'        => 'Électronique',
            'Category[description]' => 'Produits électroniques',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects();

        static::bootKernel();
        $em      = static::getContainer()->get(EntityManagerInterface::class);
        $created = $em->getRepository(Category::class)->findOneBy(['name' => 'Électronique']);
        $this->assertNotNull($created);
        static::ensureKernelShutdown();
    }
}
