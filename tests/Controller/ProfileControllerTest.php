<?php

namespace App\Tests\Controller;

use App\Entity\CustomerAddress;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileControllerTest extends ControllerTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setName('Bob')->setEmail('bob@test.com')->setRoles(['ROLE_USER']);
        $user->setPassword($hasher->hashPassword($user, 'Password1!'));

        $address = (new CustomerAddress())
            ->setType('shipping')->setName('Bob')->setFirstName('')
            ->setAddress('5 avenue Test')->setCp('69001')->setCity('Lyon')->setCountry('France');
        $user->addAddress($address);
        $em->persist($user);
        $em->flush();
        $this->user = $user;
        static::ensureKernelShutdown();
    }

    public function testProfileRedirectsToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');
        $this->assertResponseRedirects('/login');
    }

    public function testProfilePageShowsUserName(): void
    {
        $client = static::createClient();
        $client->loginUser($this->user);
        $client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Bob');
    }

    public function testProfilePageShowsUserEmail(): void
    {
        $client = static::createClient();
        $client->loginUser($this->user);
        $client->request('GET', '/profile');
        $this->assertSelectorTextContains('body', 'bob@test.com');
    }

    public function testEditPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->loginUser($this->user);
        $client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditProfileUpdatesUserName(): void
    {
        $client = static::createClient();
        $client->loginUser($this->user);
        $crawler = $client->request('GET', '/profile/edit');

        $form = $crawler->selectButton('Enregistrer')->form([
            'profile_form[name]'    => 'Robert',
            'profile_form[email]'   => 'bob@test.com',
            'profile_form[address]' => '5 avenue Test',
            'profile_form[cp]'      => '69001',
            'profile_form[city]'    => 'Lyon',
            'profile_form[country]' => 'France',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/profile');

        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $updated = $em->getRepository(User::class)->findOneBy(['email' => 'bob@test.com']);
        $this->assertSame('Robert', $updated->getName());
        static::ensureKernelShutdown();
    }
}
