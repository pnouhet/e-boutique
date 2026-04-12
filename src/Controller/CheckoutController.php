<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function index(CartService $cart, ProductRepository $productRepository): Response
    {
        $items = $cart->getCart();

        $products = [];
        foreach ($items as $item) {
            $products[$item['product_id']] = $productRepository->find($item['product_id']);
        }

        $shippingCost = $this->getParameter('shipping_cost');

        return $this->render('checkout/index.html.twig', [
            'items'        => $items,
            'products'     => $products,
            'total'        => $cart->getTotal(),
            'shippingCost' => $shippingCost,
            'grandTotal'   => $cart->getTotal() + $shippingCost,
            'address'      => $this->getUser()->getAddresses()->first() ?: null,
        ]);
    }

    #[Route('/checkout/confirm', name: 'app_checkout_confirm', methods: ['POST'])]
    public function confirm(CartService $cart, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $items = $cart->getCart();
        if (empty($items)) {
            return $this->redirectToRoute('app_cart');
        }

        $shippingCost = (string) $this->getParameter('shipping_cost');
        $subtotal     = $cart->getTotal();
        $grandTotal   = $subtotal + (float) $shippingCost;

        $order = new Order();
        $order->setOrderNumber((string) Uuid::v4());
        $order->setUser($this->getUser());
        $order->setShippingCost($shippingCost);
        $order->setTotal((string) $grandTotal);

        foreach ($items as $item) {
            $product = $productRepository->find($item['product_id']);
            if (!$product) {
                continue;
            }
            $line = new OrderLine();
            $line->setProduct($product);
            $line->setQuantity($item['quantity']);
            $line->setUnitPrice($item['unit_price']);
            $order->addOrderLine($line);
        }

        $em->persist($order);
        $em->flush();

        $cart->clear();

        return $this->redirectToRoute('app_checkout_success', ['orderNumber' => $order->getOrderNumber()]);
    }

    #[Route('/checkout/success/{orderNumber}', name: 'app_checkout_success')]
    public function success(string $orderNumber): Response
    {
        return $this->render('checkout/success.html.twig', [
            'orderNumber' => $orderNumber,
        ]);
    }
}
