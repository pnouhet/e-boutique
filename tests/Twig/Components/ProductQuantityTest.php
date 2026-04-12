<?php

namespace App\Tests\Twig\Components;

use App\Twig\Components\ProductQuantity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

class ProductQuantityTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    public function testInitialTotalEqualsUnitPrice(): void
    {
        $component = $this->createLiveComponent(
            name: ProductQuantity::class,
            data: ['unitPrice' => 49.90, 'quantity' => 1],
        );
        $this->assertEqualsWithDelta(49.90, $component->component()->getTotal(), 0.001);
    }

    public function testIncrementIncreasesQuantity(): void
    {
        $component = $this->createLiveComponent(
            name: ProductQuantity::class,
            data: ['unitPrice' => 49.90, 'quantity' => 1],
        );
        $component->call('increment');
        $this->assertSame(2, $component->component()->quantity);
    }

    public function testIncrementUpdatesTotal(): void
    {
        $component = $this->createLiveComponent(
            name: ProductQuantity::class,
            data: ['unitPrice' => 49.90, 'quantity' => 1],
        );
        $component->call('increment');
        $this->assertEqualsWithDelta(99.80, $component->component()->getTotal(), 0.001);
    }

    public function testDecrementDoesNotGoBelowOne(): void
    {
        $component = $this->createLiveComponent(
            name: ProductQuantity::class,
            data: ['unitPrice' => 49.90, 'quantity' => 1],
        );
        $component->call('decrement');
        $this->assertSame(1, $component->component()->quantity);
    }

    public function testDecrementFromTwoGoesToOne(): void
    {
        $component = $this->createLiveComponent(
            name: ProductQuantity::class,
            data: ['unitPrice' => 49.90, 'quantity' => 2],
        );
        $component->call('decrement');
        $this->assertSame(1, $component->component()->quantity);
    }
}
