<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->getConnection()->executeStatement('DELETE FROM customer_address');
        $em->getConnection()->executeStatement('DELETE FROM "user"');
        static::ensureKernelShutdown();
    }

    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testRegistrationCreatesUserAndRedirectsToCheckout(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton("S'inscrire")->form([
            'registration_form[name]'            => 'Jean Dupont',
            'registration_form[email]'           => 'jean@example.com',
            'registration_form[plainPassword]'   => 'Password1!',
            'registration_form[address]'         => '1 rue de la Paix',
            'registration_form[cp]'              => '75001',
            'registration_form[city]'            => 'Paris',
            'registration_form[country]'         => 'France',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/checkout');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'jean@example.com']);
        $this->assertNotNull($user);
        $this->assertSame('Jean Dupont', $user->getName());
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));
    }
}
