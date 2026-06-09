<?php
/** @var string $pageTitle */
/** @var string $activeNav home|shop|cart|account */
$pageTitle = $pageTitle ?? SITE_NAME;
$activeNav = $activeNav ?? '';
$cartCount = cart_count();
$user = current_user($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/css/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/css/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/css/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="assets/css/favicon_io/site.webmanifest">
    <link rel="shortcut icon" href="assets/css/favicon_io/favicon.ico">
</head>
<body>
<div class="announcement-bar">
    <div class="container announcement-inner">
        <span class="announce-left">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Free Shipping on orders over <?= e(format_price(FREE_SHIPPING_THRESHOLD)) ?>
        </span>
        <span class="announce-right">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Get 10% off on your first order — Use code: GLOW10
        </span>
    </div>
</div>

<header class="site-header">
    <div class="container header-inner">
        <button class="nav-toggle" aria-label="Toggle menu" onclick="document.body.classList.toggle('nav-open')">
            <span></span><span></span><span></span>
        </button>

        <a href="index.php" class="logo"><?= e(SITE_NAME) ?></a>

        <nav class="main-nav">
            <a href="index.php" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Home</a>
            <a href="shop.php" class="<?= $activeNav === 'shop' ? 'active' : '' ?>">Shop</a>
            <a href="shop.php?featured=1">Best Sellers</a>
            <a href="shop.php?sort=new">New Arrivals</a>
            <a href="index.php#about">About Us</a>
        </nav>

        <div class="header-actions">
            <form method="GET" action="shop.php" class="header-search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" name="q" placeholder="Search products..." value="<?= e($_GET['q'] ?? '') ?>">
            </form>

            <a href="<?= is_logged_in() ? 'account.php' : 'login.php' ?>" class="icon-btn" title="Account">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>

            <a href="cart.php" class="icon-btn cart-btn <?= $activeNav === 'cart' ? 'active' : '' ?>" title="Cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<?php if ($msg = flash_get('success')): ?>
    <div class="flash flash-success container"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash_get('error')): ?>
    <div class="flash flash-error container"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash_get('info')): ?>
    <div class="flash flash-info container"><?= e($msg) ?></div>
<?php endif; ?>

<main class="site-main">
