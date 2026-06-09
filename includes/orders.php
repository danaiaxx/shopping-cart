<?php

function generate_order_number(): string
{
    return 'LUM-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
}

function generate_tracking_number(): string
{
    return 'TRK-LUM-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

function resolve_order_status(string $status, string $createdAt): string
{
    $created = strtotime($createdAt);
    $days = (time() - $created) / 86400;

    if ($status === 'delivered') {
        return 'delivered';
    }
    if ($days >= 3) {
        return 'delivered';
    }
    if ($days >= 1) {
        return 'shipped';
    }
    return 'processing';
}

function create_order(mysqli $conn, int $userId, array $shipping, string $paymentMethod, array $totals, array $items): ?string
{
    $orderNumber = generate_order_number();
    $trackingNumber = generate_tracking_number();

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare(
            'INSERT INTO orders (
                user_id, order_number, status, tracking_number, payment_method,
                subtotal, shipping, total,
                ship_name, ship_email, ship_phone,
                ship_address_line1, ship_address_line2, ship_city, ship_province, ship_postal_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $status = 'processing';
        $stmt->bind_param(
            'issssiiissssssss',
            $userId,
            $orderNumber,
            $status,
            $trackingNumber,
            $paymentMethod,
            $totals['subtotal'],
            $totals['shipping'],
            $totals['total'],
            $shipping['full_name'],
            $shipping['email'],
            $shipping['phone'],
            $shipping['address_line1'],
            $shipping['address_line2'],
            $shipping['city'],
            $shipping['province'],
            $shipping['postal_code']
        );
        $stmt->execute();
        $orderId = (int) $conn->insert_id;

        $itemStmt = $conn->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity) VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($items as $productId => $item) {
            $pid = (int) $productId;
            $name = $item['name'];
            $price = (int) $item['price'];
            $qty = (int) $item['quantity'];
            $itemStmt->bind_param('iisii', $orderId, $pid, $name, $price, $qty);
            $itemStmt->execute();
        }

        cart_clear($conn, $userId);
        $conn->commit();
        return $orderNumber;
    } catch (Throwable $e) {
        $conn->rollback();
        return null;
    }
}

function get_order_by_number(mysqli $conn, int $userId, string $orderNumber): ?array
{
    $stmt = $conn->prepare('SELECT * FROM orders WHERE order_number = ? AND user_id = ?');
    $stmt->bind_param('si', $orderNumber, $userId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) {
        return null;
    }

    $order['status'] = resolve_order_status($order['status'], $order['created_at']);
    $order['items'] = get_order_items($conn, (int) $order['order_id']);
    return $order;
}

function get_user_orders(mysqli $conn, int $userId): array
{
    $stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $row['status'] = resolve_order_status($row['status'], $row['created_at']);
        $row['items'] = get_order_items($conn, (int) $row['order_id']);
        $orders[] = $row;
    }

    return $orders;
}

function get_order_items(mysqli $conn, int $orderId): array
{
    $stmt = $conn->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}
