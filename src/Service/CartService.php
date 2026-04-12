<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(private RequestStack $requestStack) {}

    public function getCart(): array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, []);
    }

    public function addItem(Product $product, int $qty): void
    {
        $cart = $this->getCart();

        foreach ($cart as &$line) {
            if ($line['product_id'] === $product->getId()) {
                $line['quantity'] += $qty;
                $this->save($cart);
                return;
            }
        }

        $cart[] = [
            'product_id' => $product->getId(),
            'quantity'   => $qty,
            'unit_price' => $product->getPriceHT(),
        ];

        $this->save($cart);
    }

    public function updateQuantity(int $productId, int $qty): void
    {
        $cart = $this->getCart();

        foreach ($cart as &$line) {
            if ($line['product_id'] === $productId) {
                $line['quantity'] = $qty;
                break;
            }
        }

        $this->save($cart);
    }

    public function removeItem(int $productId): void
    {
        $cart = array_values(array_filter(
            $this->getCart(),
            fn(array $line) => $line['product_id'] !== $productId
        ));

        $this->save($cart);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    public function getTotal(): float
    {
        return array_reduce(
            $this->getCart(),
            fn(float $carry, array $line) => $carry + ((float) $line['unit_price'] * $line['quantity']),
            0.0
        );
    }

    public function getItemCount(): int
    {
        return count($this->getCart());
    }

    private function save(array $cart): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $cart);
    }
}
