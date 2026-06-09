<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$userId = current_user_id();
$user = current_user($conn);
$tab = $_GET['tab'] ?? 'profile';
$selectedOrder = $_GET['order'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (isset($_POST['update_profile'])) {
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
        ];
        if (update_user_profile($conn, $userId, $data)) {
            flash_set('success', 'Profile updated successfully.');
        } else {
            flash_set('error', 'Could not update profile.');
        }
        redirect('account.php?tab=profile');
    }
}

$user = current_user($conn);
$orders = get_user_orders($conn, $userId);
$orderDetail = $selectedOrder ? get_order_by_number($conn, $userId, $selectedOrder) : null;

$pageTitle = 'My Account';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
?>

<section class="account-page container">
    <h1 class="section-title">My Account</h1>
    <p class="account-greeting">Hello, <?= e($user['full_name']) ?></p>

    <div class="account-tabs">
        <a href="account.php?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">Profile</a>
        <a href="account.php?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">My Orders</a>
    </div>

    <?php if ($tab === 'profile'): ?>
        <div class="account-panel">
            <h2>Shipping Profile</h2>
            <form method="POST" class="profile-form">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required value="<?= e($user['full_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?= e($user['phone']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= e($user['email']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="address_line1">Address Line 1</label>
                    <input type="text" id="address_line1" name="address_line1" value="<?= e($user['address_line1']) ?>">
                </div>
                <div class="form-group">
                    <label for="address_line2">Address Line 2</label>
                    <input type="text" id="address_line2" name="address_line2" value="<?= e($user['address_line2']) ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?= e($user['city']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province" value="<?= e($user['province']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?= e($user['postal_code']) ?>">
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Save Profile</button>
            </form>
            <p class="account-logout"><a href="logout.php">Sign Out</a></p>
        </div>
    <?php else: ?>
        <div class="account-panel">
            <?php if (empty($orders)): ?>
                <p class="empty-state">You haven't placed any orders yet. <a href="shop.php">Start shopping</a></p>
            <?php else: ?>
                <div class="orders-layout">
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <a href="account.php?tab=orders&order=<?= e(urlencode($order['order_number'])) ?>"
                               class="order-card <?= $selectedOrder === $order['order_number'] ? 'active' : '' ?>">
                                <div class="order-card-top">
                                    <strong><?= e($order['order_number']) ?></strong>
                                    <span class="status-badge status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
                                </div>
                                <p><?= e(date('M j, Y', strtotime($order['created_at']))) ?> · <?= e(format_price((int) $order['total'])) ?></p>
                                <p class="tracking-text">Tracking: <?= e($order['tracking_number']) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-detail">
                        <?php if ($orderDetail): ?>
                            <h2>Order <?= e($orderDetail['order_number']) ?></h2>
                            <div class="status-timeline">
                                <?php
                                $steps = ['processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
                                $currentIdx = array_search($orderDetail['status'], array_keys($steps), true);
                                $i = 0;
                                foreach ($steps as $key => $label):
                                    $done = $i <= $currentIdx;
                                ?>
                                    <div class="timeline-step <?= $done ? 'done' : '' ?>">
                                        <span class="timeline-dot"></span>
                                        <span><?= e($label) ?></span>
                                    </div>
                                <?php $i++; endforeach; ?>
                            </div>
                            <p><strong>Tracking Number:</strong> <?= e($orderDetail['tracking_number']) ?></p>
                            <p><strong>Payment:</strong> <?= e(payment_label($orderDetail['payment_method'])) ?></p>
                            <p><strong>Ship to:</strong><br>
                                <?= e($orderDetail['ship_name']) ?><br>
                                <?= e($orderDetail['ship_address_line1']) ?><br>
                                <?php if ($orderDetail['ship_address_line2']): ?><?= e($orderDetail['ship_address_line2']) ?><br><?php endif; ?>
                                <?= e($orderDetail['ship_city']) ?>, <?= e($orderDetail['ship_province']) ?> <?= e($orderDetail['ship_postal_code']) ?>
                            </p>
                            <h3>Items</h3>
                            <ul class="order-items-list">
                                <?php foreach ($orderDetail['items'] as $item): ?>
                                    <li>
                                        <span><?= e($item['product_name']) ?> × <?= (int) $item['quantity'] ?></span>
                                        <span><?= e(format_price((int) $item['product_price'] * (int) $item['quantity'])) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="order-total"><strong>Total:</strong> <?= e(format_price((int) $orderDetail['total'])) ?></p>
                        <?php else: ?>
                            <p class="empty-state">Select an order to view details and tracking.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
