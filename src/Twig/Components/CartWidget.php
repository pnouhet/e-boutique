<?php

namespace App\Twig\Components;

use App\Service\CartService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class CartWidget
{
    public int $count;

    public function __construct(CartService $cartService)
    {
        $this->count = $cartService->getItemCount();
    }
}
