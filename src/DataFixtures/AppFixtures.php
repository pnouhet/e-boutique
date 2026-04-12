<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\CustomerAddress;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Categories ---
        $electronics = new Category();
        $electronics->setName('Électronique')->setDescription('Smartphones, ordinateurs, accessoires et plus.');
        $manager->persist($electronics);

        $clothing = new Category();
        $clothing->setName('Vêtements')->setDescription('Mode homme, femme et enfant.');
        $manager->persist($clothing);

        $home = new Category();
        $home->setName('Maison & Jardin')->setDescription('Mobilier, décoration et outillage de jardin.');
        $manager->persist($home);

        // --- Products (10 total across 3 categories) ---
        $productsData = [
            ['Smartphone Pro X', 'Écran AMOLED 6,7", 256 Go, 5G.', '599.99', $electronics],
            ['Casque Bluetooth Z', 'Réduction de bruit active, 30h d\'autonomie.', '149.90', $electronics],
            ['Tablette Ultra 11"', 'Écran 2K, processeur octa-core, 128 Go.', '349.00', $electronics],
            ['Clavier mécanique RGB', 'Switches Cherry MX Red, rétroéclairage personnalisable.', '89.95', $electronics],
            ['T-shirt Coton Bio', '100% coton biologique, coupe classique.', '24.90', $clothing],
            ['Veste Imperméable', 'Membrane 3 couches, coutures soudées.', '119.00', $clothing],
            ['Jean Slim Stretch', 'Tissu élastique confort, coupe ajustée.', '59.90', $clothing],
            ['Canapé 3 places', 'Structure bois massif, tissu velours gris.', '699.00', $home],
            ['Lampe de bureau LED', 'Intensité réglable, bras articulé, port USB.', '39.50', $home],
            ['Tondeuse sans fil', 'Batterie 40V, largeur de coupe 42 cm.', '189.00', $home],
        ];

        foreach ($productsData as [$name, $desc, $price, $category]) {
            $product = new Product();
            $product->setName($name)
                ->setDescription($desc)
                ->setPriceHT($price)
                ->setAvailable(true)
                ->setImage(null)
                ->setCategory($category);
            $manager->persist($product);
        }

        // --- Admin user ---
        $admin = new User();
        $admin->setName('Administrateur')
            ->setEmail('admin@boutique.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);

        // --- Regular users with delivery address ---
        $usersData = [
            ['Alice Martin', 'alice@example.com', 'User1234!', '12 rue de la Paix', '75001', 'Paris', 'France'],
            ['Bob Dupont',   'bob@example.com',   'User1234!', '5 avenue Victor Hugo', '69002', 'Lyon',  'France'],
        ];

        foreach ($usersData as [$name, $email, $plain, $address, $cp, $city, $country]) {
            $user = new User();
            $user->setName($name)
                ->setEmail($email)
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->hasher->hashPassword($user, $plain));

            $addr = new CustomerAddress();
            $addr->setType('shipping')
                ->setName($name)
                ->setFirstName('')
                ->setAddress($address)
                ->setCp($cp)
                ->setCity($city)
                ->setCountry($country);
            $user->addAddress($addr);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
