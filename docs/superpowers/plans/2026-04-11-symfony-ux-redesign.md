# Symfony UX Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace Bootstrap 5 with Tailwind CSS and add Symfony UX Twig/Live Components to deliver a Moderne & Minimaliste design with a live search bar, live product quantity selector, and a cart widget that refreshes via Turbo.

**Architecture:** `symfonycasts/tailwind-bundle` compiles Tailwind from `assets/styles/app.css` (no Node build step). Four components are created: `ProductCard` and `CartWidget` as Twig Components; `SearchBar` and `ProductQuantity` as Live Components. All existing controllers, entities, and services remain unchanged.

**Tech Stack:** Symfony 7.4, symfonycasts/tailwind-bundle (Tailwind 3), symfony/ux-twig-component, symfony/ux-live-component, Turbo 7 (already installed)

---

## File Map

### New files
| Path | Role |
|---|---|
| `tailwind.config.js` | Content paths for Tailwind purge |
| `src/Twig/Components/ProductCard.php` | Twig Component — reusable product card |
| `src/Twig/Components/CartWidget.php` | Twig Component — navbar cart counter |
| `src/Twig/Components/SearchBar.php` | Live Component — live product search |
| `src/Twig/Components/ProductQuantity.php` | Live Component — qty selector with live total |
| `templates/components/ProductCard.html.twig` | ProductCard template |
| `templates/components/CartWidget.html.twig` | CartWidget template |
| `templates/components/SearchBar.html.twig` | SearchBar template |
| `templates/components/ProductQuantity.html.twig` | ProductQuantity template |
| `tests/Twig/Components/ProductQuantityTest.php` | Live Component unit tests |
| `tests/Twig/Components/SearchBarTest.php` | Live Component unit tests |
| `tests/Twig/Components/CartWidgetTest.php` | Twig Component integration test |

### Modified files
| Path | Change |
|---|---|
| `importmap.php` | Remove Bootstrap + Popper; keep Stimulus, Turbo |
| `assets/app.js` | Remove `console.log`; keep imports |
| `assets/styles/app.css` | Replace body rule with Tailwind directives |
| `src/Repository/ProductRepository.php` | Add `findBySearch(string $query): array` |
| `templates/base.html.twig` | Tailwind navbar + `<twig:CartWidget />` + `<twig:SearchBar />` |
| `templates/home/index.html.twig` | Tailwind hero + 3-col category grid |
| `templates/shop/category.html.twig` | Tailwind grid + `<twig:ProductCard />` |
| `templates/shop/product.html.twig` | Tailwind 2-col + `<twig:ProductQuantity />` |
| `templates/cart/index.html.twig` | Tailwind 2-col layout |
| `templates/checkout/index.html.twig` | Tailwind classes |
| `templates/checkout/success.html.twig` | Tailwind classes |
| `templates/profile/index.html.twig` | Tailwind classes |
| `templates/profile/edit.html.twig` | Tailwind classes |
| `templates/security/login.html.twig` | Tailwind classes |
| `templates/registration/register.html.twig` | Tailwind classes |

---

## Task 1 — Install packages + verify baseline

**Files:** `composer.json`, `composer.lock`, `config/bundles.php`

- [ ] **Step 1: Install Tailwind bundle and UX packages**

```bash
composer require symfonycasts/tailwind-bundle symfony/ux-twig-component symfony/ux-live-component
```

Expected: All three packages install without error. `config/bundles.php` gains `SymfonyCasts\TailwindBundle\SymfonyCastsTailwindBundle`, `Symfony\UX\TwigComponent\TwigComponentBundle`, `Symfony\UX\LiveComponent\LiveComponentBundle`.

- [ ] **Step 2: Initialise Tailwind**

```bash
php bin/console tailwind:init
```

Expected: Creates `tailwind.config.js` and updates `assets/styles/app.css` with Tailwind directives. Output says "Tailwind CSS initialized".

- [ ] **Step 3: Verify existing tests still pass**

```bash
php bin/phpunit --no-coverage
```

Expected: `OK (46 tests, 106 assertions)` — no regressions.

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.lock config/bundles.php tailwind.config.js assets/styles/app.css importmap.php
git commit -m "feat: install symfonycasts/tailwind-bundle, ux-twig-component, ux-live-component"
```

---

## Task 2 — Configure Tailwind CSS

**Files:** `tailwind.config.js`, `assets/styles/app.css`

- [ ] **Step 1: Update tailwind.config.js content paths**

Replace the generated `tailwind.config.js` with:

```js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './src/Twig/Components/**/*.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

- [ ] **Step 2: Update assets/styles/app.css**

Replace the entire file with:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

- [ ] **Step 3: Build Tailwind to verify config is valid**

```bash
php bin/console tailwind:build
```

Expected: Downloads Tailwind CLI binary (first run only), compiles CSS, outputs something like `Done in Xms`. No errors.

- [ ] **Step 4: Commit**

```bash
git add tailwind.config.js assets/styles/app.css
git commit -m "feat: configure Tailwind CSS content paths and directives"
```

---

## Task 3 — Remove Bootstrap, update importmap and app.js

**Files:** `importmap.php`, `assets/app.js`

- [ ] **Step 1: Update importmap.php**

Replace the entire file with (removing `bootstrap`, `@popperjs/core`, `bootstrap/dist/css/bootstrap.min.css`):

```php
<?php

return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
];
```

- [ ] **Step 2: Update assets/app.js**

Replace the entire file with:

```js
import './bootstrap.js';
import './styles/app.css';
```

- [ ] **Step 3: Run existing tests — they must still pass**

```bash
php bin/phpunit --no-coverage
```

Expected: `OK (46 tests, 106 assertions)`. The tests check routes and data, not CSS classes.

- [ ] **Step 4: Commit**

```bash
git add importmap.php assets/app.js
git commit -m "feat: remove Bootstrap, Tailwind CSS takes over"
```

---

## Task 4 — ProductCard Twig Component (RED → GREEN)

**Files:**
- Create: `src/Twig/Components/ProductCard.php`
- Create: `templates/components/ProductCard.html.twig`

- [ ] **Step 1: Write the failing test**

Create `tests/Twig/Components/CartWidgetTest.php` (we also cover ProductCard here via an integration route test, since ProductCard is rendered inside the category page):

Actually, ProductCard is covered by the existing `ShopControllerTest::testCategoryPageShowsProducts`. Run it to confirm it still passes after the component is created. No separate unit test is needed for a pure Twig Component — route coverage is sufficient.

Skip to Step 2.

- [ ] **Step 2: Create the PHP component class**

Create `src/Twig/Components/ProductCard.php`:

```php
<?php

namespace App\Twig\Components;

use App\Entity\Product;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ProductCard
{
    public Product $product;
}
```

- [ ] **Step 3: Create the template**

Create `templates/components/ProductCard.html.twig`:

```twig
<a href="{{ path('app_product', {id: product.id}) }}"
   class="block border border-gray-100 rounded-xl overflow-hidden hover:shadow-sm transition-shadow">
    <div class="bg-gray-50 h-40 flex items-center justify-content-center">
        {% if product.image %}
            <img src="{{ asset('uploads/products/' ~ product.image) }}"
                 alt="{{ product.name }}"
                 class="h-full w-full object-cover">
        {% else %}
            <svg class="w-12 h-12 text-black/20" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18M9 21V9"/>
            </svg>
        {% endif %}
    </div>
    <div class="p-4">
        <div class="text-sm font-semibold text-black/80">{{ product.name }}</div>
        <div class="text-sm font-bold text-black/85 mt-1">
            {{ product.priceHT|number_format(2, ',', '\u{00A0}') }} €
        </div>
    </div>
</a>
```

- [ ] **Step 4: Verify existing tests still pass**

```bash
php bin/phpunit --no-coverage
```

Expected: `OK (46 tests, 106 assertions)`.

- [ ] **Step 5: Commit**

```bash
git add src/Twig/Components/ProductCard.php templates/components/ProductCard.html.twig
git commit -m "feat: add ProductCard Twig Component"
```

---

## Task 5 — CartWidget Twig Component (RED → GREEN)

**Files:**
- Create: `src/Twig/Components/CartWidget.php`
- Create: `templates/components/CartWidget.html.twig`
- Create: `tests/Twig/Components/CartWidgetTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Twig/Components/CartWidgetTest.php`:

```php
<?php

namespace App\Tests\Twig\Components;

use App\Entity\Category;
use App\Entity\Product;
use App\Tests\Controller\ControllerTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CartWidgetTest extends ControllerTestCase
{
    public function testCartBadgeHiddenWhenCartEmpty(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('[data-testid="cart-badge"]');
    }

    public function testCartBadgeShownAfterAddingProduct(): void
    {
        static::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $category = (new Category())->setName('Test')->setDescription('');
        $product = (new Product())
            ->setName('Widget Test')->setDescription('desc')
            ->setPriceHT('10.00')->setAvailable(true)->setCategory($category);
        $em->persist($category);
        $em->persist($product);
        $em->flush();
        $productId = $product->getId();
        static::ensureKernelShutdown();

        $client = static::createClient();
        $client->request('POST', '/cart/add/' . $productId, ['quantity' => 1]);
        $client->request('GET', '/');
        $this->assertSelectorExists('[data-testid="cart-badge"]');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php bin/phpunit tests/Twig/Components/CartWidgetTest.php --no-coverage
```

Expected: FAIL — `CartWidget` component not found / `data-testid="cart-badge"` selector never exists.

- [ ] **Step 3: Create the PHP component class**

Create `src/Twig/Components/CartWidget.php`:

```php
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
```

- [ ] **Step 4: Create the template**

Create `templates/components/CartWidget.html.twig`:

```twig
<a href="{{ path('app_cart') }}" class="relative inline-flex items-center">
    <svg class="w-5 h-5 text-black/75" xmlns="http://www.w3.org/2000/svg"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 0 1-8 0"/>
    </svg>
    {% if count > 0 %}
        <span data-testid="cart-badge"
              class="absolute -top-1.5 -right-2 bg-black text-white text-[9px]
                     font-bold rounded-full w-4 h-4 flex items-center justify-center">
            {{ count }}
        </span>
    {% endif %}
</a>
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php bin/phpunit tests/Twig/Components/CartWidgetTest.php --no-coverage
```

Expected: `OK (2 tests, ...)`.

- [ ] **Step 6: Run full suite**

```bash
php bin/phpunit --no-coverage
```

Expected: All pass.

- [ ] **Step 7: Commit**

```bash
git add src/Twig/Components/CartWidget.php templates/components/CartWidget.html.twig tests/Twig/Components/CartWidgetTest.php
git commit -m "feat: add CartWidget Twig Component with session cart count"
```

---

## Task 6 — ProductQuantity Live Component (RED → GREEN)

**Files:**
- Create: `src/Twig/Components/ProductQuantity.php`
- Create: `templates/components/ProductQuantity.html.twig`
- Create: `tests/Twig/Components/ProductQuantityTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Twig/Components/ProductQuantityTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php bin/phpunit tests/Twig/Components/ProductQuantityTest.php --no-coverage
```

Expected: FAIL — `ProductQuantity` class not found.

- [ ] **Step 3: Create the PHP Live Component**

Create `src/Twig/Components/ProductQuantity.php`:

```php
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
```

- [ ] **Step 4: Create the template**

Create `templates/components/ProductQuantity.html.twig`:

```twig
<div {{ attributes.defaults({class: 'bg-gray-50 border border-gray-100 rounded-xl p-4'}) }}>
    <div class="text-[11px] uppercase tracking-widest text-black/40 mb-3">Quantité</div>
    <div class="flex items-center gap-3">
        <button data-action="live#action"
                data-live-action-param="decrement"
                class="w-8 h-8 rounded-lg border border-gray-200 bg-white text-black/60
                       flex items-center justify-center text-base hover:bg-gray-100 transition-colors">
            −
        </button>
        <span class="text-base font-semibold text-black/82 min-w-[1.5rem] text-center">
            {{ quantity }}
        </span>
        <button data-action="live#action"
                data-live-action-param="increment"
                class="w-8 h-8 rounded-lg border border-gray-200 bg-white text-black/60
                       flex items-center justify-center text-base hover:bg-gray-100 transition-colors">
            +
        </button>
        <span class="text-sm text-black/40 ml-1">
            = {{ this.total|number_format(2, ',', '\u{00A0}') }} €
        </span>
    </div>
    <input type="hidden" name="quantity" value="{{ quantity }}">
</div>
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php bin/phpunit tests/Twig/Components/ProductQuantityTest.php --no-coverage
```

Expected: `OK (5 tests, ...)`.

- [ ] **Step 6: Run full suite**

```bash
php bin/phpunit --no-coverage
```

Expected: All pass.

- [ ] **Step 7: Commit**

```bash
git add src/Twig/Components/ProductQuantity.php templates/components/ProductQuantity.html.twig tests/Twig/Components/ProductQuantityTest.php
git commit -m "feat: add ProductQuantity Live Component with increment/decrement"
```

---

## Task 7 — SearchBar Live Component + ProductRepository::findBySearch (RED → GREEN)

**Files:**
- Modify: `src/Repository/ProductRepository.php`
- Create: `src/Twig/Components/SearchBar.php`
- Create: `templates/components/SearchBar.html.twig`
- Create: `tests/Twig/Components/SearchBarTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Twig/Components/SearchBarTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php bin/phpunit tests/Twig/Components/SearchBarTest.php --no-coverage
```

Expected: FAIL — `SearchBar` class not found.

- [ ] **Step 3: Add findBySearch to ProductRepository**

Edit `src/Repository/ProductRepository.php`:

```php
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
```

- [ ] **Step 4: Create the PHP Live Component**

Create `src/Twig/Components/SearchBar.php`:

```php
<?php

namespace App\Twig\Components;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class SearchBar
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(private ProductRepository $productRepository)
    {
    }

    /** @return Product[] */
    public function getResults(): array
    {
        if (mb_strlen($this->query) < 2) {
            return [];
        }

        return $this->productRepository->findBySearch($this->query);
    }
}
```

- [ ] **Step 5: Create the template**

Create `templates/components/SearchBar.html.twig`:

```twig
<div {{ attributes.defaults({class: 'relative'}) }}>
    <div class="flex items-center gap-1.5 bg-gray-50 rounded-full px-3 py-1.5">
        <svg class="w-3 h-3 text-black/35" xmlns="http://www.w3.org/2000/svg"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input data-model="query"
               type="text"
               placeholder="Rechercher…"
               autocomplete="off"
               class="bg-transparent text-xs text-black/70 placeholder-black/35
                      focus:outline-none w-28">
    </div>
    {% if this.results is not empty %}
        <div class="absolute top-full mt-1 left-0 w-64 bg-white border border-gray-100
                    rounded-xl shadow-lg z-50 py-1">
            {% for product in this.results %}
                <a href="{{ path('app_product', {id: product.id}) }}"
                   class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition-colors">
                    <span class="text-sm text-black/80">{{ product.name }}</span>
                    <span class="ml-auto text-xs text-black/40">
                        {{ product.priceHT|number_format(2, ',', '\u{00A0}') }} €
                    </span>
                </a>
            {% endfor %}
        </div>
    {% endif %}
</div>
```

- [ ] **Step 6: Run test to verify it passes**

```bash
php bin/phpunit tests/Twig/Components/SearchBarTest.php --no-coverage
```

Expected: `OK (5 tests, ...)`.

- [ ] **Step 7: Run full suite**

```bash
php bin/phpunit --no-coverage
```

Expected: All pass.

- [ ] **Step 8: Commit**

```bash
git add src/Repository/ProductRepository.php src/Twig/Components/SearchBar.php templates/components/SearchBar.html.twig tests/Twig/Components/SearchBarTest.php
git commit -m "feat: add SearchBar Live Component and ProductRepository::findBySearch"
```

---

## Task 8 — base.html.twig (Tailwind navbar + components)

**Files:** `templates/base.html.twig`

- [ ] **Step 1: Replace base.html.twig**

```twig
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{% block title %}E-Boutique{% endblock %}</title>
    {% block stylesheets %}{% endblock %}
    {% block javascripts %}
        {% block importmap %}{{ importmap('app') }}{% endblock %}
    {% endblock %}
</head>
<body class="bg-white text-black antialiased">

    <nav class="border-b border-gray-100 px-8 h-14 flex items-center justify-between sticky top-0 bg-white z-40">
        <a href="{{ path('app_home') }}"
           class="font-bold text-lg tracking-tight text-black/90">E-Boutique</a>

        <div class="flex items-center gap-6">
            {% for category in nav_categories() %}
                <a href="{{ path('app_category', {id: category.id}) }}"
                   class="text-sm text-black/45 hover:text-black/70 transition-colors">
                    {{ category.name }}
                </a>
            {% endfor %}
        </div>

        <div class="flex items-center gap-4">
            <twig:SearchBar />
            <twig:CartWidget />
            {% if app.user %}
                <a href="{{ path('app_profile') }}"
                   class="text-sm text-black/45 hover:text-black/70 transition-colors border-l border-gray-100 pl-4">
                    {{ app.user.name }}
                </a>
                <a href="{{ path('app_logout') }}"
                   class="text-sm text-black/45 hover:text-black/70 transition-colors">
                    Déconnexion
                </a>
            {% else %}
                <a href="{{ path('app_login') }}"
                   class="text-sm text-black/45 hover:text-black/70 transition-colors border-l border-gray-100 pl-4">
                    Connexion
                </a>
            {% endif %}
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-8 py-8">
        {% for message in app.flashes('success') %}
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-100 rounded-xl
                        text-sm text-green-800">{{ message }}</div>
        {% endfor %}
        {% for message in app.flashes('error') %}
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-xl
                        text-sm text-red-800">{{ message }}</div>
        {% endfor %}
        {% block body %}{% endblock %}
    </main>

    <footer class="mt-12 py-6 border-t border-gray-100 text-center">
        <p class="text-xs text-black/30">&copy; {{ "now"|date("Y") }} E-Boutique</p>
    </footer>

</body>
</html>
```

- [ ] **Step 2: Build Tailwind to include new classes**

```bash
php bin/console tailwind:build
```

- [ ] **Step 3: Run full test suite**

```bash
php bin/phpunit --no-coverage
```

Expected: All pass.

- [ ] **Step 4: Commit**

```bash
git add templates/base.html.twig
git commit -m "feat: rebuild base.html.twig with Tailwind navbar and UX components"
```

---

## Task 9 — Home + Category templates

**Files:** `templates/home/index.html.twig`, `templates/shop/category.html.twig`

- [ ] **Step 1: Replace templates/home/index.html.twig**

```twig
{% extends 'base.html.twig' %}

{% block title %}E-Boutique — Accueil{% endblock %}

{% block body %}
<div class="py-10 text-center border-b border-gray-100 -mx-8 px-8 mb-10">
    <h1 class="text-3xl font-bold tracking-tight text-black/85">Bienvenue sur E-Boutique</h1>
    <p class="text-black/40 mt-2 text-base">Découvrez notre sélection de produits</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    {% for category in categories %}
        <a href="{{ path('app_category', {id: category.id}) }}"
           class="block border border-gray-100 rounded-xl overflow-hidden
                  hover:shadow-sm transition-shadow">
            <div class="bg-gray-50 h-28 flex items-center justify-center">
                <svg class="w-8 h-8 text-black/20" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18M9 21V9"/>
                </svg>
            </div>
            <div class="p-4">
                <div class="text-sm font-semibold text-black/80">{{ category.name }}</div>
                <div class="text-xs text-black/[0.32] mt-1">
                    {{ category.products|length }} produit{{ category.products|length > 1 ? 's' : '' }} →
                </div>
            </div>
        </a>
    {% endfor %}
</div>
{% endblock %}
```

- [ ] **Step 2: Replace templates/shop/category.html.twig**

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ category.name }} — E-Boutique{% endblock %}

{% block body %}
<div class="mb-8">
    <p class="text-xs text-black/35 mb-1">
        <a href="{{ path('app_home') }}" class="hover:text-black/60 transition-colors">Accueil</a>
        &rsaquo; <span class="text-black/60">{{ category.name }}</span>
    </p>
    <h1 class="text-2xl font-bold tracking-tight text-black/85">{{ category.name }}</h1>
    {% if category.description %}
        <p class="text-sm text-black/45 mt-1">{{ category.description }}</p>
    {% endif %}
</div>

{% if products is empty %}
    <p class="text-sm text-black/40">Aucun produit dans cette catégorie.</p>
{% else %}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        {% for product in products %}
            <twig:ProductCard :product="product" />
        {% endfor %}
    </div>
{% endif %}
{% endblock %}
```

- [ ] **Step 3: Build Tailwind**

```bash
php bin/console tailwind:build
```

- [ ] **Step 4: Run full test suite**

```bash
php bin/phpunit --no-coverage
```

Expected: All pass.

- [ ] **Step 5: Commit**

```bash
git add templates/home/index.html.twig templates/shop/category.html.twig
git commit -m "feat: migrate home and category templates to Tailwind + ProductCard component"
```

---

## Task 10 — Product page template

**Files:** `templates/shop/product.html.twig`

- [ ] **Step 1: Replace templates/shop/product.html.twig**

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ product.name }} — E-Boutique{% endblock %}

{% block body %}
<p class="text-xs text-black/35 mb-6">
    <a href="{{ path('app_home') }}" class="hover:text-black/60 transition-colors">Accueil</a>
    &rsaquo;
    <a href="{{ path('app_category', {id: product.category.id}) }}"
       class="hover:text-black/60 transition-colors">{{ product.category.name }}</a>
    &rsaquo; <span class="text-black/60">{{ product.name }}</span>
</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

    {# Image #}
    <div class="bg-gray-50 rounded-2xl aspect-square flex items-center justify-center overflow-hidden">
        {% if product.image %}
            <img src="{{ asset('uploads/products/' ~ product.image) }}"
                 alt="{{ product.name }}"
                 class="w-full h-full object-cover">
        {% else %}
            <svg class="w-20 h-20 text-black/15" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18M9 21V9"/>
            </svg>
        {% endif %}
    </div>

    {# Info #}
    <div class="flex flex-col justify-center gap-5">
        <div>
            <div class="text-xs uppercase tracking-widest text-black/40 mb-2">
                {{ product.category.name }}
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-black/85 mb-2">
                {{ product.name }}
            </h1>
            {% if product.description %}
                <p class="text-sm text-black/45 leading-relaxed">{{ product.description }}</p>
            {% endif %}
        </div>

        <div class="text-3xl font-bold tracking-tight text-black/88">
            {{ product.priceHT|number_format(2, ',', '\u{00A0}') }} €
        </div>

        <form action="{{ path('app_cart_add', {id: product.id}) }}" method="post">
            <twig:ProductQuantity unitPrice="{{ product.priceHT }}" />
            <button type="submit"
                    class="mt-4 w-full bg-black text-white rounded-xl py-3.5 px-6
                           text-sm font-semibold hover:bg-black/90 transition-colors">
                Ajouter au panier
            </button>
        </form>

        <p class="text-xs text-black/[0.32] text-center">
            🚚 Frais de livraison : {{ shipping_cost }} €
        </p>
    </div>
</div>
{% endblock %}
```

> **Note:** `shipping_cost` is injected by `AppExtension` (add a `shipping_cost()` Twig function in `AppExtension` if not already present). Alternatively pass it from the controller — see Step 1b below.

- [ ] **Step 1b: Add shipping_cost to ShopController::product**

In `src/Controller/ShopController.php`, update the `product()` action to pass `shippingCost`:

```php
#[Route('/product/{id}', name: 'app_product')]
public function product(int $id, ProductRepository $productRepository): Response
{
    $product = $productRepository->find($id);
    if (!$product) {
        throw $this->createNotFoundException('Produit introuvable.');
    }

    return $this->render('shop/product.html.twig', [
        'product'      => $product,
        'shipping_cost' => $this->getParameter('shipping_cost'),
    ]);
}
```

And update the template to use `{{ shipping_cost }}` (already used above).

- [ ] **Step 2: Build Tailwind**

```bash
php bin/console tailwind:build
```

- [ ] **Step 3: Run full test suite**

```bash
php bin/phpunit --no-coverage
```

Expected: All pass.

- [ ] **Step 4: Commit**

```bash
git add templates/shop/product.html.twig src/Controller/ShopController.php
git commit -m "feat: migrate product page to Tailwind + ProductQuantity Live Component"
```

---

## Task 11 — Cart template

**Files:** `templates/cart/index.html.twig`

- [ ] **Step 1: Replace templates/cart/index.html.twig**

```twig
{% extends 'base.html.twig' %}

{% block title %}Mon panier — E-Boutique{% endblock %}

{% block body %}
<h1 class="text-2xl font-bold tracking-tight text-black/85 mb-8">Mon panier</h1>

{% if items is empty %}
    <div class="text-center py-16">
        <p class="text-black/40 text-sm mb-4">Votre panier est vide.</p>
        <a href="{{ path('app_home') }}"
           class="inline-block bg-black text-white rounded-xl py-2.5 px-6 text-sm font-semibold">
            Continuer mes achats
        </a>
    </div>
{% else %}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {# Cart lines #}
        <div class="lg:col-span-2 space-y-0 divide-y divide-gray-100">
            {% for item in items %}
                {% set product = products[item.product_id] %}
                <div class="py-5 grid grid-cols-[56px_1fr_auto] gap-4 items-start">
                    <div class="w-14 h-14 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-black/20" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-black/80">
                            <a href="{{ path('app_product', {id: product.id}) }}"
                               class="hover:text-black transition-colors">{{ product.name }}</a>
                        </div>
                        <div class="text-xs text-black/40 mt-0.5">
                            {{ item.unit_price|number_format(2, ',', '\u{00A0}') }} € / unité
                        </div>
                        <form action="{{ path('app_cart_update', {id: product.id}) }}"
                              method="post" class="flex items-center gap-2 mt-3">
                            <button name="quantity" value="{{ [1, item.quantity - 1]|max }}"
                                    class="w-6 h-6 rounded-md border border-gray-200 bg-white
                                           text-black/55 text-sm flex items-center justify-center
                                           hover:bg-gray-50 transition-colors">−</button>
                            <span class="text-sm font-semibold text-black/80 min-w-[1rem] text-center">
                                {{ item.quantity }}
                            </span>
                            <button name="quantity" value="{{ item.quantity + 1 }}"
                                    class="w-6 h-6 rounded-md border border-gray-200 bg-white
                                           text-black/55 text-sm flex items-center justify-center
                                           hover:bg-gray-50 transition-colors">+</button>
                        </form>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-black/82">
                            {{ (item.unit_price * item.quantity)|number_format(2, ',', '\u{00A0}') }} €
                        </div>
                        <form action="{{ path('app_cart_remove', {id: product.id}) }}" method="post">
                            <button class="text-xs text-black/[0.28] hover:text-black/50 mt-2 transition-colors">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            {% endfor %}
        </div>

        {# Summary #}
        <div>
            <div class="bg-gray-50 rounded-2xl p-6 sticky top-20">
                <div class="text-sm font-bold text-black/82 mb-5 tracking-tight">Récapitulatif</div>

                <div class="space-y-3 text-sm text-black/55">
                    <div class="flex justify-between">
                        <span>Sous-total</span>
                        <span>{{ total|number_format(2, ',', '\u{00A0}') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Livraison</span>
                        <span>{{ shippingCost|number_format(2, ',', '\u{00A0}') }} €</span>
                    </div>
                </div>

                <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between
                            text-base font-bold text-black/88">
                    <span>Total</span>
                    <span>{{ (total + shippingCost)|number_format(2, ',', '\u{00A0}') }} €</span>
                </div>

                <a href="{{ path('app_checkout') }}"
                   class="mt-5 block w-full bg-black text-white rounded-xl py-3.5 text-sm
                          font-semibold text-center hover:bg-black/90 transition-colors">
                    Commander →
                </a>
                <p class="text-[11px] text-black/[0.32] text-center mt-3">
                    Connexion requise à la validation
                </p>
            </div>
        </div>

    </div>
{% endif %}
{% endblock %}
```

- [ ] **Step 2: Build Tailwind + run full suite**

```bash
php bin/console tailwind:build && php bin/phpunit --no-coverage
```

Expected: All pass.

- [ ] **Step 3: Commit**

```bash
git add templates/cart/index.html.twig
git commit -m "feat: migrate cart page to Tailwind 2-col layout"
```

---

## Task 12 — Checkout, Profile, Auth templates

**Files:** `templates/checkout/*.html.twig`, `templates/profile/*.html.twig`, `templates/security/login.html.twig`, `templates/registration/register.html.twig`

Shared Tailwind patterns used across all forms:
- Label: `class="block text-xs uppercase tracking-widest text-black/50 mb-1.5"`
- Input: `class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10"`
- Error: `class="text-xs text-red-500 mt-1"`
- Primary button: `class="bg-black text-white rounded-xl py-3 px-6 text-sm font-semibold hover:bg-black/90 transition-colors"`
- Secondary button: `class="border border-gray-200 text-black/60 rounded-xl py-3 px-6 text-sm font-semibold hover:bg-gray-50 transition-colors"`

- [ ] **Step 1: Replace templates/checkout/index.html.twig**

```twig
{% extends 'base.html.twig' %}
{% block title %}Finaliser ma commande — E-Boutique{% endblock %}

{% block body %}
<h1 class="text-2xl font-bold tracking-tight text-black/85 mb-8">Finaliser ma commande</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 space-y-6">
        <div class="border border-gray-100 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-black/80">Articles</h2>
            </div>
            <div class="divide-y divide-gray-100">
                {% for item in items %}
                    {% set product = products[item.product_id] %}
                    <div class="px-6 py-4 grid grid-cols-[1fr_auto_auto_auto] gap-4
                                items-center text-sm">
                        <span class="text-black/80">{{ product.name }}</span>
                        <span class="text-black/40">{{ item.unit_price|number_format(2,',','\u{00A0}') }} €</span>
                        <span class="text-black/55">× {{ item.quantity }}</span>
                        <span class="font-semibold text-black/82">
                            {{ (item.unit_price * item.quantity)|number_format(2,',','\u{00A0}') }} €
                        </span>
                    </div>
                {% endfor %}
            </div>
        </div>

        {% if address %}
            <div class="border border-gray-100 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-black/80 mb-3">Adresse de livraison</h2>
                <address class="text-sm text-black/55 not-italic leading-relaxed">
                    {{ address.address }}<br>
                    {{ address.cp }} {{ address.city }}<br>
                    {{ address.country }}
                </address>
                <a href="{{ path('app_profile_edit') }}"
                   class="text-xs text-black/40 hover:text-black/60 mt-2 inline-block transition-colors">
                    Modifier l'adresse →
                </a>
            </div>
        {% endif %}
    </div>

    <div>
        <div class="bg-gray-50 rounded-2xl p-6 sticky top-20">
            <div class="text-sm font-bold text-black/82 mb-5">Récapitulatif</div>
            <div class="space-y-3 text-sm text-black/55">
                <div class="flex justify-between">
                    <span>Sous-total</span>
                    <span>{{ total|number_format(2,',','\u{00A0}') }} €</span>
                </div>
                <div class="flex justify-between">
                    <span>Livraison</span>
                    <span>{{ shippingCost|number_format(2,',','\u{00A0}') }} €</span>
                </div>
            </div>
            <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between
                        text-base font-bold text-black/88 mb-6">
                <span>Total</span>
                <span>{{ grandTotal|number_format(2,',','\u{00A0}') }} €</span>
            </div>
            <form action="{{ path('app_checkout_confirm') }}" method="post">
                <button class="w-full bg-black text-white rounded-xl py-3.5 text-sm
                               font-semibold hover:bg-black/90 transition-colors">
                    Confirmer la commande
                </button>
            </form>
        </div>
    </div>

</div>
{% endblock %}
```

- [ ] **Step 2: Replace templates/checkout/success.html.twig**

```twig
{% extends 'base.html.twig' %}
{% block title %}Commande confirmée — E-Boutique{% endblock %}

{% block body %}
<div class="max-w-lg mx-auto text-center py-16">
    <div class="w-14 h-14 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1 class="text-2xl font-bold tracking-tight text-black/85 mb-2">Commande confirmée !</h1>
    <p class="text-sm text-black/45 mb-1">Merci pour votre achat.</p>
    <p class="text-xs text-black/[0.32] mb-8">
        Numéro de commande : <code class="font-mono">{{ orderNumber }}</code>
    </p>
    <div class="flex items-center justify-center gap-3">
        <a href="{{ path('app_home') }}"
           class="border border-gray-200 text-black/60 rounded-xl py-2.5 px-5
                  text-sm font-semibold hover:bg-gray-50 transition-colors">
            Continuer mes achats
        </a>
        <a href="{{ path('app_profile') }}"
           class="bg-black text-white rounded-xl py-2.5 px-5 text-sm font-semibold
                  hover:bg-black/90 transition-colors">
            Voir mes commandes
        </a>
    </div>
</div>
{% endblock %}
```

- [ ] **Step 3: Replace templates/profile/index.html.twig**

```twig
{% extends 'base.html.twig' %}
{% block title %}Mon profil — E-Boutique{% endblock %}

{% block body %}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="space-y-5">
        <div class="border border-gray-100 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-sm font-semibold text-black/80">Mes informations</h2>
                <a href="{{ path('app_profile_edit') }}"
                   class="text-xs text-black/40 hover:text-black/60 transition-colors">Modifier</a>
            </div>
            <div class="px-6 py-4 text-sm space-y-2">
                <p><span class="text-black/45">Nom :</span>
                   <span class="text-black/80 font-medium ml-1">{{ user.name }}</span></p>
                <p><span class="text-black/45">E-mail :</span>
                   <span class="text-black/80 font-medium ml-1">{{ user.email }}</span></p>
            </div>
        </div>

        {% if address %}
            <div class="border border-gray-100 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-black/80">Adresse de livraison</h2>
                </div>
                <div class="px-6 py-4">
                    <address class="text-sm text-black/55 not-italic leading-relaxed">
                        {{ address.address }}<br>
                        {{ address.cp }} {{ address.city }}<br>
                        {{ address.country }}
                    </address>
                </div>
            </div>
        {% endif %}
    </div>

    <div class="lg:col-span-2">
        <h2 class="text-sm font-semibold text-black/80 mb-4">Mes commandes</h2>
        {% if orders is empty %}
            <p class="text-sm text-black/40">Vous n'avez pas encore de commande.</p>
        {% else %}
            <div class="border border-gray-100 rounded-2xl overflow-hidden">
                <div class="divide-y divide-gray-100">
                    {% for order in orders %}
                        <div class="px-6 py-4 grid grid-cols-3 text-sm">
                            <code class="font-mono text-xs text-black/55 truncate">
                                {{ order.orderNumber }}
                            </code>
                            <span class="text-black/45 text-center">
                                {{ order.createdAt|date('d/m/Y') }}
                            </span>
                            <span class="text-right font-semibold text-black/80">
                                {{ order.total|number_format(2,',','\u{00A0}') }} €
                            </span>
                        </div>
                    {% endfor %}
                </div>
            </div>
        {% endif %}
    </div>

</div>
{% endblock %}
```

- [ ] **Step 4: Replace templates/profile/edit.html.twig**

```twig
{% extends 'base.html.twig' %}
{% block title %}Modifier mon profil — E-Boutique{% endblock %}

{% block body %}
<div class="max-w-lg">
    <h1 class="text-2xl font-bold tracking-tight text-black/85 mb-8">Modifier mon profil</h1>

    {{ form_start(form, {attr: {class: 'space-y-5'}}) }}

    <div>
        {{ form_label(form.name, 'Nom', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
        {{ form_widget(form.name, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
        {{ form_errors(form.name) }}
    </div>

    <div>
        {{ form_label(form.email, 'E-mail', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
        {{ form_widget(form.email, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
        {{ form_errors(form.email) }}
    </div>

    <div>
        {{ form_label(form.plainPassword, null, {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
        {{ form_widget(form.plainPassword, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
        {{ form_errors(form.plainPassword) }}
    </div>

    <div class="border-t border-gray-100 pt-5">
        <h2 class="text-sm font-semibold text-black/70 mb-4">Adresse de livraison</h2>
        <div class="space-y-4">
            <div>
                {{ form_label(form.address, 'Adresse', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
                {{ form_widget(form.address, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
                {{ form_errors(form.address) }}
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    {{ form_label(form.cp, 'Code postal', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
                    {{ form_widget(form.cp, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
                    {{ form_errors(form.cp) }}
                </div>
                <div class="col-span-2">
                    {{ form_label(form.city, 'Ville', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
                    {{ form_widget(form.city, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
                    {{ form_errors(form.city) }}
                </div>
            </div>
            <div>
                {{ form_label(form.country, 'Pays', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
                {{ form_widget(form.country, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
                {{ form_errors(form.country) }}
            </div>
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="bg-black text-white rounded-xl py-3 px-6 text-sm font-semibold
                       hover:bg-black/90 transition-colors">
            Enregistrer
        </button>
        <a href="{{ path('app_profile') }}"
           class="border border-gray-200 text-black/60 rounded-xl py-3 px-6 text-sm
                  font-semibold hover:bg-gray-50 transition-colors">
            Annuler
        </a>
    </div>

    {{ form_end(form) }}
</div>
{% endblock %}
```

- [ ] **Step 5: Replace templates/security/login.html.twig**

```twig
{% extends 'base.html.twig' %}
{% block title %}Connexion — E-Boutique{% endblock %}

{% block body %}
<div class="max-w-sm mx-auto py-8">
    <h1 class="text-2xl font-bold tracking-tight text-black/85 mb-8 text-center">Connexion</h1>

    {% if error %}
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-xl
                    text-sm text-red-700">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
    {% endif %}

    <form method="post" action="{{ path('app_login') }}" class="space-y-4">
        <div>
            <label for="email"
                   class="block text-xs uppercase tracking-widest text-black/50 mb-1.5">
                E-mail
            </label>
            <input type="email" id="email" name="_username"
                   value="{{ last_username }}" autocomplete="email" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-black/10">
        </div>
        <div>
            <label for="password"
                   class="block text-xs uppercase tracking-widest text-black/50 mb-1.5">
                Mot de passe
            </label>
            <input type="password" id="password" name="_password"
                   autocomplete="current-password" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-black/10">
        </div>
        <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">
        <button type="submit"
                class="w-full bg-black text-white rounded-xl py-3 text-sm font-semibold
                       hover:bg-black/90 transition-colors">
            Se connecter
        </button>
    </form>

    <p class="text-center text-sm text-black/40 mt-6">
        Pas encore de compte ?
        <a href="{{ path('app_register') }}"
           class="text-black/70 hover:text-black transition-colors font-medium">
            S'inscrire
        </a>
    </p>
</div>
{% endblock %}
```

- [ ] **Step 6: Replace templates/registration/register.html.twig**

```twig
{% extends 'base.html.twig' %}
{% block title %}Créer un compte — E-Boutique{% endblock %}

{% block body %}
<div class="max-w-sm mx-auto py-8">
    <h1 class="text-2xl font-bold tracking-tight text-black/85 mb-8 text-center">
        Créer un compte
    </h1>

    {{ form_start(form, {attr: {class: 'space-y-4'}}) }}

    <div>
        {{ form_label(form.name, 'Nom', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
        {{ form_widget(form.name, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
        {{ form_errors(form.name) }}
    </div>

    <div>
        {{ form_label(form.email, 'E-mail', {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
        {{ form_widget(form.email, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
        {{ form_errors(form.email) }}
    </div>

    <div>
        {{ form_label(form.plainPassword, null, {label_attr: {class: 'block text-xs uppercase tracking-widest text-black/50 mb-1.5'}}) }}
        {{ form_widget(form.plainPassword, {attr: {class: 'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-black/10'}}) }}
        {{ form_errors(form.plainPassword) }}
    </div>

    <button type="submit"
            class="w-full bg-black text-white rounded-xl py-3 text-sm font-semibold
                   hover:bg-black/90 transition-colors">
        Créer mon compte
    </button>

    {{ form_end(form) }}

    <p class="text-center text-sm text-black/40 mt-6">
        Déjà un compte ?
        <a href="{{ path('app_login') }}"
           class="text-black/70 hover:text-black transition-colors font-medium">
            Se connecter
        </a>
    </p>
</div>
{% endblock %}
```

- [ ] **Step 7: Build Tailwind + run full suite**

```bash
php bin/console tailwind:build && php bin/phpunit --no-coverage
```

Expected: All tests pass.

- [ ] **Step 8: Commit**

```bash
git add templates/checkout/ templates/profile/ templates/security/ templates/registration/
git commit -m "feat: migrate all remaining templates to Tailwind CSS"
```

---

## Task 13 — Final build + full verification

- [ ] **Step 1: Production Tailwind build (minified)**

```bash
php bin/console tailwind:build --minify
```

Expected: Output mentions minification. CSS file is smaller.

- [ ] **Step 2: Run complete test suite**

```bash
php bin/phpunit --no-coverage
```

Expected: All tests pass — count should be at least 58 (46 original + 12 new component tests).

- [ ] **Step 3: Final commit**

```bash
git add -A
git commit -m "feat: complete Symfony UX + Tailwind redesign — all tests passing"
```

---

## Self-Review

**Spec coverage:**
- ✅ Tailwind CSS replacing Bootstrap — Tasks 1–3
- ✅ `symfonycasts/tailwind-bundle` (no Node) — Task 1
- ✅ `ProductCard` Twig Component — Task 4
- ✅ `CartWidget` Twig Component — Task 5
- ✅ `ProductQuantity` Live Component — Task 6
- ✅ `SearchBar` Live Component — Task 7
- ✅ Opacity-based color palette (`text-black/85`, etc.) — Tasks 8–12
- ✅ Navbar redesign — Task 8
- ✅ Home page hero + 3-col category grid — Task 9
- ✅ Product page 2-col layout — Task 10
- ✅ Cart page 2-col with sticky summary — Task 11
- ✅ All auth/profile/checkout templates — Task 12

**Type consistency:** `ProductQuantity.quantity` is `int` throughout. `SearchBar.query` is `string`. `CartWidget.count` is `int`. `ProductCard.product` is `Product`. All consistent across tasks.

**No placeholders:** All steps contain actual code. No TBD/TODO.
