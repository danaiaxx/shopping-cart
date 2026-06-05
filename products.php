<?php
include('db_connect.php');
session_start();

#kani diri kay mag fetch syas mga product details basta maclick ang add to cart button
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];

    #pag retrieve ni syas image para makita ang image sa cart page
    $sql = "SELECT product_image FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $product_image = base64_encode($product['product_image']); 

    #mucheck if ang product naa nas cart or wala
    $check_sql = "SELECT * FROM cart WHERE product_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1; #if naa, set quantity to current quantity + 1
        $update_sql = "UPDATE cart SET product_quantity = product_quantity + 1 WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('i', $product_id);
        $update_stmt->execute();
    } else {
        $_SESSION['cart'][$product_id] = [ #if wala, add product sa cart then quantity is 1
            'name' => $product_name,
            'price' => $product_price,
            'quantity' => 1,
            'image' => $product_image 
        ];
        $insert_sql = "INSERT INTO cart (product_id, product_quantity) VALUES (?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('i', $product_id);
        $insert_stmt->execute();
    }

    $_SESSION['product_added'] = true;
    header("Location: products.php");
    exit();
}

$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
}

$sql = "SELECT product_id, product_name, product_price, product_image FROM products";
if ($search_query != '') {
    $sql .= " WHERE product_name LIKE '%$search_query%'";
}
$sql .= " ORDER BY product_name ASC"; #pag display sa products
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lush Cosmetics</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="logo.ico">

    <script>
        window.onload = function() {
            <?php if (isset($_SESSION['product_added']) && $_SESSION['product_added']): ?>
                document.getElementById('cart-popup').style.display = 'block';
                <?php unset($_SESSION['product_added']); ?>
            <?php endif; ?>
        };

        function closeModal() {
            document.getElementById('cart-popup').style.display = 'none';
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
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search" class="search-bar">
                </div>
            </form>

            <div class="icons">
                <a href="cart.php" class="cart-link">
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

    <main class="products-page">
        <h2 class="products">Products</h2>
        <div class="products-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product_id = $row['product_id'];
                    $product_name = $row['product_name'];
                    $product_price = $row['product_price'];
                    $product_image = base64_encode($row['product_image']);
                    ?>
                    <div class="product-card">
                        <img src="data:image/png;base64,<?php echo $product_image; ?>" alt="<?php echo $product_name; ?>">
                        <h3 class="product-name"><?php echo $product_name; ?></h3>
                        <p class="product-price">₱ <?php echo $product_price; ?></p>
                        <form method="POST" action="products.php">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $product_name; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $product_price; ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </main>

    <div id="cart-popup" class="cart-popup">
        <div class="cart-popup-content">
            <h2>Product Added to Cart!</h2>
            <p>Your product has been successfully added to the cart.</p>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
    </div>

    <?php
    $conn->close();
    ?>
</body>
</html>
