LUSH COSMETICS — PHP Shopping Cart

A simple PHP + MySQL e-commerce demo for a cosmetics store. This app includes product browsing, category filtering, cart management, checkout flow, user authentication, and order processing.

Features

- Home page with featured products and categories
- Product browsing and category filters
- Add to cart, update quantity, remove item
- Shopping cart summary with free shipping threshold
- Checkout flow with shipping details and payment selection
- User registration and login
- Order placement and success confirmation
- MySQL-backed product, user, cart, and order data

Tech Stack

- PHP
- MySQL / MariaDB
- HTML, CSS
- Procedural PHP code with reusable includes

Project Structure

- `index.php` — home page
- `shop.php` — product listing / category filters
- `cart.php` — shopping cart
- `checkout.php` — checkout flow
- `order-success.php` — order confirmation
- `login.php` — login form
- `register.php` — registration form
- `account.php` — user account/profile
- `includes/` — common app logic and utilities
- `db_connect.php` — database connection
- `shopping.sql` — schema / initial database import

Local Setup

Requirements

- PHP 7.4+ or newer
- MySQL / MariaDB
- Local web server (XAMPP, WAMP, MAMP, etc.)

Steps

1. Clone or download the repository into your web server folder
   - Example: `C:\xampp\htdocs\shopping-cart`

2. Start Apache and MySQL

3. Create the database
   - Open phpMyAdmin or MySQL client
   - Create a database named `shopping`

4. Import the schema
   - Import `shopping.sql` into the `shopping` database

5. Update database connection
   - Open `db_connect.php`
   - Set your DB credentials:

```php
<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'shopping';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

6. Visit the app
   - `http://localhost/shopping-cart`

Usage

- Browse products
- Register or log in
- Add items to cart
- Proceed through checkout
- View order confirmation
