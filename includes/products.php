<?php

function render_product_card(array $row, bool $showBadge = false): void
{
    $productId = (int) $row['product_id'];
    $productName = $row['product_name'];
    $productPrice = (int) $row['product_price'];
    $productImage = base64_encode($row['product_image']);
    $isFeatured = !empty($row['is_featured']);
    $reviewCount = 40 + ($productId * 7) % 200;
    ?>
    <article class="product-card">
        <div class="product-image-wrap">
            <?php if ($showBadge && $isFeatured): ?>
                <span class="product-badge">Best Seller</span>
            <?php elseif ($productId <= 3): ?>
                <span class="product-badge badge-new">New</span>
            <?php endif; ?>
            <img src="data:image/jpeg;base64,<?= e($productImage) ?>" alt="<?= e($productName) ?>">
            <form method="POST" action="<?= e(basename($_SERVER['PHP_SELF'])) ?>" class="quick-add-form">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $productId ?>">
                <input type="hidden" name="product_name" value="<?= e($productName) ?>">
                <input type="hidden" name="product_price" value="<?= $productPrice ?>">
                <button type="submit" name="add_to_cart" class="quick-add-btn" title="Add to cart">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </button>
            </form>
        </div>
        <h3 class="product-name"><?= e($productName) ?></h3>
        <div class="product-rating">
            <span class="stars">★★★★★</span>
            <span class="review-count">(<?= $reviewCount ?>)</span>
        </div>
        <p class="product-price"><?= e(format_price($productPrice)) ?></p>
    </article>
    <?php
}

function fetch_products(mysqli $conn, array $options = []): mysqli_result|false
{
    $sql = 'SELECT product_id, product_name, product_price, product_image, category, is_featured FROM products WHERE 1=1';
    $params = [];
    $types = '';

    if (!empty($options['category'])) {
        $sql .= ' AND category = ?';
        $params[] = $options['category'];
        $types .= 's';
    }
    if (!empty($options['featured'])) {
        $sql .= ' AND is_featured = 1';
    }
    if (!empty($options['search'])) {
        $sql .= ' AND product_name LIKE ?';
        $params[] = '%' . $options['search'] . '%';
        $types .= 's';
    }

    if (!empty($options['sort']) && $options['sort'] === 'new') {
        $sql .= ' ORDER BY product_id DESC';
    } else {
        $sql .= ' ORDER BY product_name ASC';
    }

    if (!empty($options['limit'])) {
        $sql .= ' LIMIT ?';
        $params[] = (int) $options['limit'];
        $types .= 'i';
    }

    if ($params) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    return $conn->query($sql);
}

function fetch_categories(mysqli $conn): array
{
    $result = $conn->query('SELECT category, COUNT(*) AS cnt FROM products GROUP BY category ORDER BY category');
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function category_sample_image(mysqli $conn, string $category): string
{
    $stmt = $conn->prepare('SELECT product_image FROM products WHERE category = ? LIMIT 1');
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? base64_encode($row['product_image']) : '';
}
