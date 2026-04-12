<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ProductQuantity
{
    use DefaultActionTrait;

    #[LiveProp]
    public float $unitPrice = 0.0;

    #[LiveProp(writable: true)]
    public int $quantity = 1;

    #[LiveAction]
    public function increment(): void
    {
        $this->quantity++;
    }

    #[LiveAction]
    public function decrement(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function getTotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }
}
