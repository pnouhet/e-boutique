<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /** @return Product[] */
    public function findBySearch(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :q OR p.description LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();
    }
}
