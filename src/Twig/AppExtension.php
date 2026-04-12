<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private RequestStack $requestStack,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nav_categories', $this->getNavCategories(...)),
            new TwigFunction('cart_count', $this->getCartCount(...)),
        ];
    }

    private function getNavCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    private function getCartCount(): int
    {
        return count($this->requestStack->getSession()->get('cart', []));
    }
}
