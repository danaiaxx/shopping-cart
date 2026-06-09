<?php

define('SITE_NAME', 'LUSH COSMETICS');
define('CURRENCY', '₱');
define('FREE_SHIPPING_THRESHOLD', 2500);
define('SHIPPING_FEE', 150);

define('CATEGORIES', [
    'Makeup' => 'makeup',
    'Skincare' => 'skincare',
    'Tools & Accessories' => 'tools',
]);

function category_slug(string $name): string
{
    return CATEGORIES[$name] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
}

function category_from_slug(string $slug): ?string
{
    foreach (CATEGORIES as $name => $s) {
        if ($s === $slug) {
            return $name;
        }
    }
    return null;
}

function format_price(int $amount): string
{
    return CURRENCY . ' ' . number_format($amount, 2);
}

function calculate_shipping(int $subtotal): int
{
    return $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;
}
