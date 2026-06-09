<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('account.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $result = authenticate_user($conn, $email, $password);

        if ($result['success']) {
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            flash_set('success', 'Welcome back!');
            redirect($redirect);
        }
        $error = $result['error'];
    }
}

$pageTitle = 'Sign In';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="auth-card">
        <h1 class="section-title">Sign In</h1>
        <p class="auth-subtitle">Welcome back to <?= e(SITE_NAME) ?></p>

        <?php if ($error): ?>
            <div class="form-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
