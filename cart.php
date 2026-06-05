<?php
include('db_connect.php');
session_start();

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
	$sql = "DELETE FROM cart WHERE product_id = ?";
    $remove_stmt = $conn->prepare($sql);
    $remove_stmt->bind_param('i', $product_id);
    $remove_stmt->execute();
    unset($_SESSION['cart'][$product_id]);
    header('Location: cart.php');
    exit();
}

if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['quantity'];

    $sql = "UPDATE cart SET product_quantity = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $new_quantity, $product_id);
    $stmt->execute();

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
    }
    header('Location: cart.php');
    exit();
}


if (isset($_POST['checkout'])) {
    $sql = "DELETE FROM cart";
    $remove_stmt = $conn->prepare($sql);
    $remove_stmt->execute();
	unset($_SESSION['cart']);
    $_SESSION['checkout_completed'] = true;
    header('Location: cart.php');
    exit();
}

$subtotal = 0;
foreach ($cart_items as $product_id => $item) {
    $product_subtotal = $item['price'] * $item['quantity'];
    $subtotal += $product_subtotal;
}

$shipping = (count($cart_items) > 0) ? 0 : null;
$total = $subtotal + ($shipping ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lush Cosmetics - Cart</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="logo.ico">
    <script>
        function showCheckoutPopup() {
            document.getElementById('checkout-popup').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('checkout-popup').style.display = 'none';
        }
    </script>
</head>
<body>
    <header>
        <a href="products.php" class="shop-name-link">
            <h1 class="shop-name">Lush Cosmetics</h1>
        </a>
        <div class="nav">
            <form method="POST" action="products.php">
                <div class="search-container">
                    <img src="search-icon.png" alt="Search Icon" class="search-icon">
                    <input type="text" name="search" placeholder="Search" class="search-bar">
                </div>
            </form>
            <div class="icons">
                <a href="cart.php" class="cart-link active">
                    <span class="icon">
                        <img src="cart-icon.png" alt="Cart Icon" class="cart-icon">
                    </span>
                </a>
                <span class="icon">
                    <img src="account-icon.png" alt="Account Icon" class="account-icon">
                </span>
            </div>
        </div>
    </header>
    <hr>

    <main>
        <div class="cart-page">
            <div class="cart-container">
                <h2 class="my-cart">My Cart</h2>

                <?php if (empty($cart_items)): ?>
                    <p>Your cart is empty.</p>
                <?php else: ?>
                    <div class="cart-items">
                        <?php foreach ($cart_items as $product_id => $item): ?>
                            <?php
                            $sql = "SELECT product_image FROM products WHERE product_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('i', $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $product = $result->fetch_assoc();
                            $product_image = base64_encode($product['product_image']);
                            ?>

                            <div class="cart-item">
                                <img src="data:image/png;base64,<?php echo $product_image; ?>" alt="<?php echo $item['name']; ?>">
                                <div class="item-details">
                                    <h3><?php echo $item['name']; ?></h3>
                                    <p>₱ <?php echo $item['price']; ?></p>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity">
                                        <button type="submit" name="update_quantity" class="update-btn">Update</button>
                                    </form>

                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <button type="submit" name="remove_from_cart" class="delete-btn">
                                            <img src="delete-icon.png" alt="Delete Icon">
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <p>Subtotal: <span class="price">₱ <?php echo $subtotal; ?></span></p>
                
                <?php if ($shipping === 0): ?>
                    <p>Shipping: <span class="shipping">Free</span></p>
                <?php endif; ?>

                <h2>Total: <span class="total">₱ <?php echo $total; ?></span></h2>
                <button class="checkout-btn" 
                        onclick="showCheckoutPopup()" 
                        <?php echo (empty($cart_items)) ? 'disabled' : ''; ?>>
                    Checkout
                </button>
            </div>
        </div>
    </main>

    <div id="checkout-popup" class="checkout-popup">
        <div class="checkout-popup-content">
            <h2>Confirm Checkout</h2>
            <p>Are you sure you want to proceed with the checkout?</p>
            <form method="POST" action="cart.php">
                <button type="submit" name="checkout" class="checkout-confirm-btn">Confirm Checkout</button>
                <button type="button" class="checkout-cancel-btn" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <?php
    $conn->close();
    ?>
</body>
</html>