<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cart, ProductRepository $productRepository): Response
    {
        $items = $cart->getCart();

        $products = [];
        foreach ($items as $item) {
            $products[$item['product_id']] = $productRepository->find($item['product_id']);
        }

        return $this->render('cart/index.html.twig', [
            'items'        => $items,
            'products'     => $products,
            'total'        => $cart->getTotal(),
            'shippingCost' => $this->getParameter('shipping_cost'),
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function add(int $id, Request $request, CartService $cart, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $qty = max(1, $request->request->getInt('quantity', 1));
        $cart->addItem($product, $qty);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, CartService $cart): Response
    {
        $qty = max(1, $request->request->getInt('quantity', 1));
        $cart->updateQuantity($id, $qty);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function remove(int $id, CartService $cart): Response
    {
        $cart->removeItem($id);

        return $this->redirectToRoute('app_cart');
    }
}
