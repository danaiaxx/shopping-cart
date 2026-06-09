<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/products.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    require_login_for_cart();
    if (!verify_csrf()) {
        flash_set('error', 'Invalid request.');
        redirect('index.php');
    }
    cart_add_item(
        $conn,
        current_user_id(),
        (int) $_POST['product_id'],
        $_POST['product_name'],
        (int) $_POST['product_price']
    );
    $_SESSION['product_added'] = true;
    redirect('index.php');
}

$featured = fetch_products($conn, ['featured' => true, 'limit' => 8]);
$categories = fetch_categories($conn);

$heroProducts = fetch_products($conn, ['limit' => 1]);
$heroProduct = $heroProducts ? $heroProducts->fetch_assoc() : null;

$pageTitle = 'Home';
$activeNav = 'home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-inner">
        <div class="hero-content">
            <h1 class="hero-title">Glow Naturally.<br>Shine Confidently.</h1>
            <p class="hero-text">Skincare and beauty essentials that bring out your natural radiance.</p>
            
            <div class="hero-actions">
                <a href="shop.php" class="btn btn-primary">Shop Now</a>
                <a href="shop.php?featured=1" class="btn btn-text">Explore Collection →</a>
            </div>
            
            <div class="hero-trust">
                <div class="trust-item">
                    <span>Clean Ingredients</span>
                </div>
                <div class="trust-item">
                    <span>Cruelty Free</span>
                </div>
                <div class="trust-item">
                    <span>Dermatologist Tested</span>
                </div>
                <div class="trust-item">
                    <span>For All Skin Types</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section categories-section">
    <div class="container">
        <h2 class="section-title text-center">Shop by Category</h2>
        <div class="category-grid">
            <?php foreach ($categories as $cat):
                $slug = category_slug($cat['category']);
                $img = category_sample_image($conn, $cat['category']);
            ?>
                <a href="shop.php?category=<?= e(urlencode($slug)) ?>" class="category-card">
                    <div class="category-image" style="background-image:url('data:image/jpeg;base64,<?= e($img) ?>')"></div>
                    <h3><?= e(strtoupper($cat['category'])) ?></h3>
                    <span class="category-link">Shop Now →</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section about-section" id="about">
    <div class="container about-inner">
        <h2 class="section-title">About <?= e(SITE_NAME) ?></h2>
        <p>We believe beauty begins with confidence. Our curated collection of premium cosmetics and skincare is designed to enhance your natural glow — clean, cruelty-free, and made for every skin type.</p>
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

<script>
function scrollCarousel(dir) {
    var el = document.getElementById('bestseller-carousel');
    el.scrollBy({ left: dir * 280, behavior: 'smooth' });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
