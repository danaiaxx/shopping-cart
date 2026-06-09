<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$orderNumber = $_GET['order'] ?? '';
if ($orderNumber === '') {
    redirect('account.php?tab=orders');
}

$order = get_order_by_number($conn, current_user_id(), $orderNumber);
if (!$order) {
    flash_set('error', 'Order not found.');
    redirect('account.php?tab=orders');
}

$pageTitle = 'Order Confirmed';
$activeNav = 'cart';
require __DIR__ . '/includes/header.php';
?>

<section class="success-page container">
    <div class="success-card">
        <div class="success-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h1 class="section-title">Thank You for Your Order!</h1>
        <p class="success-subtitle">Your order has been placed successfully.</p>

        <div class="success-details">
            <div class="success-row"><span>Order Number</span><strong><?= e($order['order_number']) ?></strong></div>
            <div class="success-row"><span>Tracking Number</span><strong><?= e($order['tracking_number']) ?></strong></div>
            <div class="success-row"><span>Payment Method</span><strong><?= e(payment_label($order['payment_method'])) ?></strong></div>
            <div class="success-row"><span>Status</span><span class="status-badge status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></div>
            <div class="success-row"><span>Total Paid</span><strong><?= e(format_price((int) $order['total'])) ?></strong></div>
        </div>

        <h3>Order Items</h3>
        <ul class="checkout-items">
            <?php foreach ($order['items'] as $item): ?>
                <li>
                    <span><?= e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></span>
                    <span><?= e(format_price((int) $item['product_price'] * (int) $item['quantity'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <p class="success-ship">
            Shipping to:<br>
            <strong><?= e($order['ship_name']) ?></strong><br>
            <?= e($order['ship_address_line1']) ?>,
            <?= e($order['ship_city']) ?>, <?= e($order['ship_province']) ?> <?= e($order['ship_postal_code']) ?>
        </p>

        <div class="success-actions">
            <a href="account.php?tab=orders&order=<?= e(urlencode($order['order_number'])) ?>" class="btn btn-outline">Track Order</a>
            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
