<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$userId = current_user_id();
$user = current_user($conn);
$cartItems = cart_items();

if (empty($cartItems)) {
    flash_set('info', 'Your cart is empty.');
    redirect('cart.php');
}

$step = max(1, min(3, (int) ($_GET['step'] ?? 1)));
$totals = cart_totals();
$error = '';

if (!isset($_SESSION['checkout_shipping'])) {
    $_SESSION['checkout_shipping'] = [
        'full_name' => $user['full_name'] ?? '',
        'email' => $user['email'] ?? '',
        'phone' => $user['phone'] ?? '',
        'address_line1' => $user['address_line1'] ?? '',
        'address_line2' => $user['address_line2'] ?? '',
        'city' => $user['city'] ?? '',
        'province' => $user['province'] ?? '',
        'postal_code' => $user['postal_code'] ?? '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request. Please try again.';
    } elseif (isset($_POST['save_shipping'])) {
        $_SESSION['checkout_shipping'] = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
        ];
        $required = ['full_name', 'email', 'phone', 'address_line1', 'city', 'province', 'postal_code'];
        foreach ($required as $field) {
            if ($_SESSION['checkout_shipping'][$field] === '') {
                $error = 'Please complete all required shipping fields.';
                $step = 2;
                break;
            }
        }
        if (!$error) {
            redirect('checkout.php?step=3');
        }
    } elseif (isset($_POST['place_order'])) {
        $payment = $_POST['payment_method'] ?? '';
        if (!in_array($payment, ['cod', 'gcash', 'card'], true)) {
            $error = 'Please select a payment method.';
            $step = 3;
        } else {
            $orderNumber = create_order(
                $conn,
                $userId,
                $_SESSION['checkout_shipping'],
                $payment,
                $totals,
                $cartItems
            );
            unset($_SESSION['checkout_shipping']);
            if ($orderNumber) {
                redirect('order-success.php?order=' . urlencode($orderNumber));
            }
            $error = 'Could not place order. Please try again.';
            $step = 3;
        }
    }
}

$shipping = $_SESSION['checkout_shipping'];

$pageTitle = 'Checkout';
$activeNav = 'cart';
require __DIR__ . '/includes/header.php';
?>

<section class="checkout-page container">
    <h1 class="section-title">Checkout</h1>

    <div class="checkout-steps">
        <a href="checkout.php?step=1" class="checkout-step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'done' : '' ?>">1. Cart Review</a>
        <a href="checkout.php?step=2" class="checkout-step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'done' : '' ?>">2. Shipping</a>
        <span class="checkout-step <?= $step >= 3 ? 'active' : '' ?>">3. Payment</span>
    </div>

    <?php if ($error): ?>
        <div class="form-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="checkout-layout">
        <div class="checkout-main">
            <?php if ($step === 1): ?>
                <h2>Review Your Cart</h2>
                <ul class="checkout-items">
                    <?php foreach ($cartItems as $item): ?>
                        <li>
                            <span><?= e($item['name']) ?> × <?= (int) $item['quantity'] ?></span>
                            <span><?= e(format_price((int) $item['price'] * (int) $item['quantity'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="checkout.php?step=2" class="btn btn-primary">Continue to Shipping</a>

            <?php elseif ($step === 2): ?>
                <h2>Shipping Details</h2>
                <form method="POST" class="checkout-form">
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" required value="<?= e($shipping['full_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone *</label>
                            <input type="text" id="phone" name="phone" required value="<?= e($shipping['phone']) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required value="<?= e($shipping['email']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="address_line1">Address Line 1 *</label>
                        <input type="text" id="address_line1" name="address_line1" required value="<?= e($shipping['address_line1']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="address_line2">Address Line 2</label>
                        <input type="text" id="address_line2" name="address_line2" value="<?= e($shipping['address_line2']) ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required value="<?= e($shipping['city']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="province">Province *</label>
                            <input type="text" id="province" name="province" required value="<?= e($shipping['province']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="postal_code">Postal Code *</label>
                            <input type="text" id="postal_code" name="postal_code" required value="<?= e($shipping['postal_code']) ?>">
                        </div>
                    </div>
                    <div class="checkout-form-actions">
                        <a href="checkout.php?step=1" class="btn btn-outline">Back</a>
                        <button type="submit" name="save_shipping" class="btn btn-primary">Continue to Payment</button>
                    </div>
                </form>

            <?php else: ?>
                <h2>Payment Method</h2>
                <form method="POST" class="checkout-form" id="payment-form">
                    <?= csrf_field() ?>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked onchange="toggleCardFields()">
                            <span>Cash on Delivery</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="gcash" onchange="toggleCardFields()">
                            <span>GCash</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="card" onchange="toggleCardFields()">
                            <span>Credit / Debit Card</span>
                        </label>
                    </div>

                    <div id="gcash-fields" class="payment-extra" style="display:none">
                        <div class="form-group">
                            <label for="gcash_number">GCash Mobile Number</label>
                            <input type="text" id="gcash_number" name="gcash_number" placeholder="09XX XXX XXXX">
                        </div>
                    </div>

                    <div id="card-fields" class="payment-extra" style="display:none">
                        <div class="form-group">
                            <label for="card_name">Name on Card</label>
                            <input type="text" id="card_name" name="card_name">
                        </div>
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="0000 0000 0000 0000">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card_expiry">Expiry</label>
                                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="card_cvv">CVV</label>
                                <input type="text" id="card_cvv" name="card_cvv" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <div class="checkout-form-actions">
                        <a href="checkout.php?step=2" class="btn btn-outline">Back</a>
                        <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <aside class="order-summary-panel">
            <h2>Order Summary</h2>
            <div class="summary-row"><span>Subtotal</span><span><?= e(format_price($totals['subtotal'])) ?></span></div>
            <div class="summary-row"><span>Shipping</span><span><?= $totals['shipping'] === 0 ? 'Free' : e(format_price($totals['shipping'])) ?></span></div>
            <div class="summary-row summary-total"><span>Total</span><span><?= e(format_price($totals['total'])) ?></span></div>
        </aside>
    </div>
</section>

<script>
function toggleCardFields() {
    var method = document.querySelector('input[name="payment_method"]:checked').value;
    document.getElementById('card-fields').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('gcash-fields').style.display = method === 'gcash' ? 'block' : 'none';
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
