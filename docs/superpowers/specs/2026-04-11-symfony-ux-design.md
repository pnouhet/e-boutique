# Symfony UX — Redesign E-Boutique

**Date:** 2026-04-11
**Statut:** Approuvé

---

## Objectif

Remplacer le design Bootstrap 5 existant par un design Moderne & Minimaliste basé sur Tailwind CSS, en intégrant les packages Symfony UX (Turbo, Twig Components, Live Components) pour apporter interactivité et réutilisabilité aux templates.

---

## Choix validés

| Dimension | Choix |
|---|---|
| Style visuel | Moderne & Minimaliste — fond blanc, typographie épurée, couleurs via `rgba` avec opacité |
| CSS framework | Tailwind CSS (remplace Bootstrap 5) via Tailwind standalone CLI |
| Interactivité | Turbo (navigation fluide) + Live Components (panier, quantité, recherche) + Twig Components (composants réutilisables) |
| Live features | Panier live, sélecteur de quantité fiche produit, barre de recherche live |

---

## Palette de couleurs (opacité)

Tous les textes s'appuient sur `rgba(0,0,0,x)` — pas de gris fixes.

| Usage | Valeur |
|---|---|
| Titre principal | `rgba(0,0,0,0.85)` |
| Sous-titre / description | `rgba(0,0,0,0.42)` |
| Labels nav / tags catégorie | `rgba(0,0,0,0.45)` |
| Titres cartes produit | `rgba(0,0,0,0.78)` |
| Textes secondaires ("4 produits →") | `rgba(0,0,0,0.32)` |
| Bordures | `#f1f1f1` |
| Fond cartes / zones grises | `#f7f7f7` |
| Bouton principal | `bg-black text-white` |

---

## Architecture technique

### Packages à installer

```bash
composer require symfony/ux-twig-component symfony/ux-live-component
npm install -D tailwindcss   # ou Tailwind standalone CLI
```

### Structure des nouveaux fichiers

```
assets/
  styles/app.css                  ← @tailwind base/components/utilities
  tailwind.config.js              ← content paths vers templates/ et src/Twig/Components/

src/Twig/Components/
  ProductCard.php                 ← Twig Component (carte produit réutilisable)
  CartWidget.php                  ← Live Component (compteur panier navbar)
  SearchBar.php                   ← Live Component (recherche live)
  ProductQuantity.php             ← Live Component (sélecteur qty fiche produit)

templates/components/
  ProductCard.html.twig
  CartWidget.html.twig
  SearchBar.html.twig
  ProductQuantity.html.twig
```

### Modifications existantes

| Fichier | Changement |
|---|---|
| `importmap.php` | Supprimer Bootstrap, ajouter Tailwind CSS compilé |
| `assets/app.css` | Remplacer import Bootstrap par directives Tailwind |
| `templates/base.html.twig` | Navbar Tailwind + `<twig:CartWidget />` + `<twig:SearchBar />` |
| `templates/home/index.html.twig` | Grille catégories Tailwind |
| `templates/shop/category.html.twig` | Grille produits avec `<twig:ProductCard />` |
| `templates/shop/product.html.twig` | Layout 2 colonnes + `<twig:ProductQuantity />` |
| `templates/cart/index.html.twig` | Layout 2 colonnes + contrôles qty live |
| `templates/checkout/*.html.twig` | Tailwind classes |
| `templates/profile/*.html.twig` | Tailwind classes |
| `templates/security/*.html.twig` | Tailwind classes |
| `templates/registration/*.html.twig` | Tailwind classes |

---

## Composants détaillés

### `ProductCard` (Twig Component)

Carte produit réutilisable affichée dans les pages catégorie et accueil.

**Props :** `product: Product`
**Template :** image placeholder, nom, prix, lien vers fiche produit
**Utilisé dans :** `shop/category.html.twig` via `<twig:ProductCard :product="product" />`

---

### `CartWidget` (Live Component)

Icône panier dans la navbar avec badge indiquant le nombre d'articles. Se met à jour automatiquement quand le panier change.

**State :** `count: int` (lu depuis `CartService`)
**Comportement :** rafraîchi par Turbo après chaque ajout/suppression au panier
**Template :** icône SVG + badge `rgba` noir

---

### `SearchBar` (Live Component)

Barre de recherche dans la navbar. Filtre les produits en temps réel en tapant.

**State :** `query: string`, `results: Product[]`
**Comportement :** `#[LiveAction]` déclenché à chaque frappe (`debounce: 300ms`), recherche dans `name` et `description`
**Template :** input arrondi + liste déroulante de résultats

---

### `ProductQuantity` (Live Component)

Sélecteur de quantité sur la fiche produit. Affiche le total mis à jour (`quantité × prix`).

**State :** `quantity: int`, `unitPrice: float`
**Comportement :** boutons `−` / `+` déclenchent `#[LiveAction]`, `total` recalculé côté serveur
**Template :** boutons ronds, quantité centrale, total affiché en opacité réduite

---

## Pages — layout validé

### Accueil (`/`)
- Navbar : logo gauche, tags catégories centre (opacité 0.45), search + panier + connexion droite
- Hero : titre centré (opacité 0.82), sous-titre (opacité 0.38)
- Grille 3 colonnes de cartes catégorie : icône SVG, nom (opacité 0.78), compteur produits (opacité 0.32)

### Fiche produit (`/product/{id}`)
- Breadcrumb discret (opacité 0.35/0.6)
- Layout 2 colonnes : image gauche (fond `#f7f7f7`, `border-radius: 16px`), infos droite
- Catégorie en label uppercase (opacité 0.38), titre H1, description, prix bold
- `<twig:ProductQuantity />` dans bloc gris arrondi avec label "⚡ Live Component"
- Bouton "Ajouter au panier" pleine largeur, fond `#111`

### Panier (`/cart`)
- Layout 2 colonnes : lignes panier gauche, récapitulatif sticky droite
- Chaque ligne : miniature produit, nom + prix unitaire, contrôles `−` / `+` live, total ligne, lien "Supprimer"
- Récapitulatif : sous-total, livraison fixe (5,90 €), total, bouton "Commander →"

---

## Comportement Turbo

- Navigation entre pages : transition Turbo (pas de rechargement complet)
- Soumissions de formulaires (connexion, inscription, profil, checkout) : Turbo Form
- Ajout/suppression panier : POST standard — Turbo rafraîchit la page + CartWidget se met à jour

---

## Tests

Les tests fonctionnels existants (46 tests) restent valides car ils testent les routes et les données, pas les classes CSS. Aucun test à modifier. Les nouveaux composants Twig/Live seront couverts par des tests fonctionnels ciblés :

- `CartWidgetTest` : compte panier affiché dans la navbar
- `SearchBarTest` : résultats filtrés par query
- `ProductQuantityTest` : total recalculé lors du changement de quantité

---

## Ce qui ne change pas

- Toutes les routes, contrôleurs, entités, services — inchangés
- EasyAdmin back-office — inchangé (son propre système CSS)
- Logique métier (CartService, CheckoutController, etc.) — inchangée
- Base de données et migrations — inchangées
