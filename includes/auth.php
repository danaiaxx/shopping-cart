<?php

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return is_logged_in() ? (int) $_SESSION['user_id'] : null;
}

function current_user(mysqli $conn): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    $stmt = $conn->prepare('SELECT user_id, full_name, email, phone, address_line1, address_line2, city, province, postal_code FROM users WHERE user_id = ?');
    $uid = current_user_id();
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

function require_login(string $redirect = 'login.php'): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        redirect($redirect);
    }
}

function login_user(int $userId): void
{
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    unset($_SESSION['user_id'], $_SESSION['cart']);
}

function register_user(mysqli $conn, string $name, string $email, string $password): array
{
    $check = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
    $check->bind_param('s', $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        return ['success' => false, 'error' => 'An account with this email already exists.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $email, $hash);
    if (!$stmt->execute()) {
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }

    login_user((int) $conn->insert_id);
    return ['success' => true];
}

function authenticate_user(mysqli $conn, string $email, string $password): array
{
    $stmt = $conn->prepare('SELECT user_id, password_hash FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    login_user((int) $user['user_id']);
    return ['success' => true];
}

function update_user_profile(mysqli $conn, int $userId, array $data): bool
{
    $stmt = $conn->prepare('UPDATE users SET full_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, city = ?, province = ?, postal_code = ? WHERE user_id = ?');
    $stmt->bind_param(
        'sssssssi',
        $data['full_name'],
        $data['phone'],
        $data['address_line1'],
        $data['address_line2'],
        $data['city'],
        $data['province'],
        $data['postal_code'],
        $userId
    );
    return $stmt->execute();
}
