<?php

namespace App\Tests\Twig\Components;

use App\Entity\Category;
use App\Entity\Product;
use App\Twig\Components\SearchBar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

class SearchBarTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM product');
        $conn->executeStatement('DELETE FROM category');

        $category = (new Category())->setName('Tech')->setDescription('');
        $product = (new Product())
            ->setName('Smartphone Pro X')->setDescription('Écran AMOLED 6,7"')
            ->setPriceHT('599.99')->setAvailable(true)->setCategory($category);
        $this->em->persist($category);
        $this->em->persist($product);
        $this->em->flush();
    }

    public function testEmptyQueryReturnsNoResults(): void
    {
        $component = $this->createLiveComponent(
            name: SearchBar::class,
            data: ['query' => ''],
        );
        $this->assertEmpty($component->component()->getResults());
    }

    public function testShortQueryReturnsNoResults(): void
    {
        $component = $this->createLiveComponent(
            name: SearchBar::class,
            data: ['query' => 'S'],
        );
        $this->assertEmpty($component->component()->getResults());
    }

    public function testQueryMatchingProductNameReturnsResult(): void
    {
        $component = $this->createLiveComponent(
            name: SearchBar::class,
            data: ['query' => 'Smartphone'],
        );
        $results = $component->component()->getResults();
        $this->assertCount(1, $results);
        $this->assertSame('Smartphone Pro X', $results[0]->getName());
    }

    public function testQueryMatchingDescriptionReturnsResult(): void
    {
        $component = $this->createLiveComponent(
            name: SearchBar::class,
            data: ['query' => 'AMOLED'],
        );
        $this->assertCount(1, $component->component()->getResults());
    }

    public function testQueryWithNoMatchReturnsEmpty(): void
    {
        $component = $this->createLiveComponent(
            name: SearchBar::class,
            data: ['query' => 'Inexistant'],
        );
        $this->assertEmpty($component->component()->getResults());
    }
}
