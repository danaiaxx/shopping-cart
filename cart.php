<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$userId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid request.');
        redirect('cart.php');
    }
    if (isset($_POST['remove_from_cart'])) {
        cart_remove_item($conn, $userId, (int) $_POST['product_id']);
    }
    if (isset($_POST['update_quantity'])) {
        cart_update_quantity($conn, $userId, (int) $_POST['product_id'], max(1, (int) $_POST['quantity']));
    }
    redirect('cart.php');
}

$cartItems = cart_items();
$totals = cart_totals();

$pageTitle = 'Cart';
$activeNav = 'cart';
require __DIR__ . '/includes/header.php';
?>

<section class="cart-page container">
    <h1 class="section-title">Shopping Cart</h1>

    <div class="cart-layout">
        <div class="cart-items-panel">
            <?php if (empty($cartItems)): ?>
                <p class="empty-state">Your cart is empty. <a href="shop.php">Browse products</a></p>
            <?php else: ?>
                <?php foreach ($cartItems as $productId => $item): ?>
                    <div class="cart-item">
                        <img src="data:image/jpeg;base64,<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>">
                        <div class="cart-item-info">
                            <h3><?= e($item['name']) ?></h3>
                            <p><?= e(format_price((int) $item['price'])) ?></p>
                        </div>
                        <form method="POST" class="cart-qty-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int) $productId ?>">
                            <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" class="qty-input">
                            <button type="submit" name="update_quantity" class="btn btn-sm">Update</button>
                        </form>
                        <p class="cart-line-total"><?= e(format_price((int) $item['price'] * (int) $item['quantity'])) ?></p>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int) $productId ?>">
                            <button type="submit" name="remove_from_cart" class="remove-btn" title="Remove">&times;</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <aside class="order-summary-panel">
            <h2>Order Summary</h2>
            <div class="summary-row"><span>Subtotal</span><span><?= e(format_price($totals['subtotal'])) ?></span></div>
            <div class="summary-row">
                <span>Shipping</span>
                <span><?= $totals['shipping'] === 0 ? 'Free' : e(format_price($totals['shipping'])) ?></span>
            </div>
            <?php if ($totals['subtotal'] > 0 && $totals['subtotal'] < FREE_SHIPPING_THRESHOLD): ?>
                <p class="shipping-note">Add <?= e(format_price(FREE_SHIPPING_THRESHOLD - $totals['subtotal'])) ?> more for free shipping</p>
            <?php endif; ?>
            <div class="summary-row summary-total"><span>Total</span><span><?= e(format_price($totals['total'])) ?></span></div>
            <?php if (!empty($cartItems)): ?>
                <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
            <?php else: ?>
                <button class="btn btn-primary btn-block" disabled>Proceed to Checkout</button>
            <?php endif; ?>
            <a href="shop.php" class="btn btn-text btn-block">Continue Shopping</a>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
