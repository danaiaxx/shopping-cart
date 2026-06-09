<?php

function cart_init_session(): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function cart_load_from_db(mysqli $conn, int $userId): void
{
    cart_init_session();
    $_SESSION['cart'] = [];

    $stmt = $conn->prepare(
        'SELECT c.product_id, c.product_quantity, p.product_name, p.product_price
         FROM cart c
         JOIN products p ON p.product_id = c.product_id
         WHERE c.user_id = ?'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $image = cart_product_image($conn, (int) $row['product_id']);
        $_SESSION['cart'][$row['product_id']] = [
            'name' => $row['product_name'],
            'price' => (int) $row['product_price'],
            'quantity' => (int) $row['product_quantity'],
            'image' => $image,
        ];
    }
}

function cart_product_image(mysqli $conn, int $productId): string
{
    $stmt = $conn->prepare('SELECT product_image FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    return $product ? base64_encode($product['product_image']) : '';
}

function cart_add_item(mysqli $conn, int $userId, int $productId, string $name, int $price): void
{
    cart_init_session();

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += 1;
        $stmt = $conn->prepare('UPDATE cart SET product_quantity = product_quantity + 1 WHERE user_id = ? AND product_id = ?');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
    } else {
        $image = cart_product_image($conn, $productId);
        $_SESSION['cart'][$productId] = [
            'name' => $name,
            'price' => $price,
            'quantity' => 1,
            'image' => $image,
        ];
        $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, product_quantity) VALUES (?, ?, 1)');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
    }
}

function cart_update_quantity(mysqli $conn, int $userId, int $productId, int $quantity): void
{
    if ($quantity < 1) {
        return;
    }
    $stmt = $conn->prepare('UPDATE cart SET product_quantity = ? WHERE user_id = ? AND product_id = ?');
    $stmt->bind_param('iii', $quantity, $userId, $productId);
    $stmt->execute();

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }
}

function cart_remove_item(mysqli $conn, int $userId, int $productId): void
{
    $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
    $stmt->bind_param('ii', $userId, $productId);
    $stmt->execute();
    unset($_SESSION['cart'][$productId]);
}

function cart_clear(mysqli $conn, int $userId): void
{
    $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $_SESSION['cart'] = [];
}

function cart_items(): array
{
    cart_init_session();
    return $_SESSION['cart'];
}

function cart_count(): int
{
    $count = 0;
    foreach (cart_items() as $item) {
        $count += (int) $item['quantity'];
    }
    return $count;
}

function cart_totals(): array
{
    $subtotal = 0;
    foreach (cart_items() as $item) {
        $subtotal += (int) $item['price'] * (int) $item['quantity'];
    }
    $shipping = calculate_shipping($subtotal);
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $subtotal + $shipping,
    ];
}

function require_login_for_cart(): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'shop.php';
        flash_set('info', 'Please sign in to add items to your cart.');
        redirect('login.php');
    }
}
