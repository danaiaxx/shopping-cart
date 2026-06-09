<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/products.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    require_login_for_cart();
    if (!verify_csrf()) {
        flash_set('error', 'Invalid request.');
        redirect('shop.php');
    }
    cart_add_item(
        $conn,
        current_user_id(),
        (int) $_POST['product_id'],
        $_POST['product_name'],
        (int) $_POST['product_price']
    );
    $_SESSION['product_added'] = true;
    redirect('shop.php' . (isset($_GET['category']) ? '?category=' . urlencode($_GET['category']) : ''));
}

$search = trim($_GET['q'] ?? $_GET['search'] ?? '');
$categorySlug = $_GET['category'] ?? '';
$categoryName = $categorySlug ? category_from_slug($categorySlug) : null;
$featuredOnly = isset($_GET['featured']);
$sort = $_GET['sort'] ?? '';

$options = [];
if ($search !== '') {
    $options['search'] = $search;
}
if ($categoryName) {
    $options['category'] = $categoryName;
}
if ($featuredOnly) {
    $options['featured'] = true;
}
if ($sort === 'new') {
    $options['sort'] = 'new';
}

$result = fetch_products($conn, $options);

$pageTitle = $featuredOnly ? 'Best Sellers' : ($categoryName ?: 'Shop');
$activeNav = 'shop';
require __DIR__ . '/includes/header.php';
?>

<section class="shop-page container">
    <div class="shop-header">
        <h1 class="section-title"><?= e($pageTitle) ?></h1>
        <?php if ($search): ?>
            <p class="shop-meta">Results for "<?= e($search) ?>"</p>
        <?php elseif ($categoryName): ?>
            <p class="shop-meta"><?= e($categoryName) ?> collection</p>
        <?php endif; ?>
    </div>

    <div class="shop-filters">
        <a href="shop.php" class="filter-chip <?= !$categoryName && !$featuredOnly ? 'active' : '' ?>">All</a>
        <?php foreach (CATEGORIES as $name => $slug): ?>
            <a href="shop.php?category=<?= e(urlencode($slug)) ?>" class="filter-chip <?= $categoryName === $name ? 'active' : '' ?>"><?= e($name) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="products-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php render_product_card($row, true); ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-state">No products found.</p>
        <?php endif; ?>
    </div>
</section>

<div id="cart-popup" class="modal <?= !empty($_SESSION['product_added']) ? 'open' : '' ?>">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal('cart-popup')">&times;</button>
        <h2>Added to Cart</h2>
        <p>Your product has been successfully added to the cart.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" onclick="closeModal('cart-popup')">Continue Shopping</button>
            <a href="cart.php" class="btn btn-primary">View Cart</a>
        </div>
    </div>
</div>
<?php unset($_SESSION['product_added']); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
