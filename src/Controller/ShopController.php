<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/category/{id}', name: 'app_category', requirements: ['id' => '\d+'])]
    public function category(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        return $this->render('shop/category.html.twig', [
            'category' => $category,
            'products'  => $category->getProducts(),
        ]);
    }

    #[Route('/product/{id}', name: 'app_product', requirements: ['id' => '\d+'])]
    public function product(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        return $this->render('shop/product.html.twig', [
            'product'       => $product,
            'shipping_cost' => $this->getParameter('shipping_cost'),
        ]);
    }
}
