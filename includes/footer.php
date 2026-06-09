</main>

<section class="trust-bar">
    <div class="container trust-grid">
        <div class="trust-item">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <div><strong>Free Shipping</strong><span>On orders over <?= e(format_price(FREE_SHIPPING_THRESHOLD)) ?></span></div>
        </div>
        <div class="trust-item">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            <div><strong>Easy Returns</strong><span>30-day return policy</span></div>
        </div>
        <div class="trust-item">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <div><strong>Secure Payment</strong><span>100% secure checkout</span></div>
        </div>
        <div class="trust-item">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3v5z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3v5z"/></svg>
            <div><strong>Customer Support</strong><span>We're here to help</span></div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <h3><?= e(SITE_NAME) ?></h3>
            <p>Skincare and beauty essentials that bring out your natural radiance.</p>
        </div>
        <div class="footer-links">
            <a href="shop.php">Shop</a>
            <a href="account.php">My Account</a>
            <a href="cart.php">Cart</a>
        </div>
        <p class="footer-copy">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
    </div>
</footer>

<script>
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}
function openModal(id) {
    document.getElementById(id).classList.add('open');
}
document.querySelectorAll('.modal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.remove('open');
    });
});
</script>
</body>
</html>
