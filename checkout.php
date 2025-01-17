<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/checkout.php';
    header('Location: /login.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: /cart.php');
    exit;
}

$errors = [];
$success = false;

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Calculate totals
$cartItems = [];
$subtotal = 0;
$shipping = 10.00; // Basic flat rate shipping
$tax_rate = 0.10; // 10% tax rate

if (!empty($_SESSION['cart'])) {
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT p.*, pi.image_path 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.id IN ($placeholders)
    ");
    
    $stmt->execute(array_keys($_SESSION['cart']));
    $products = $stmt->fetchAll();
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $itemTotal = $product['price'] * $quantity;
        $subtotal += $itemTotal;
        
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'total' => $itemTotal
        ];
    }
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping + $tax;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    $shipping_address = filter_input(INPUT_POST, 'shipping_address', FILTER_SANITIZE_STRING);
    $billing_address = filter_input(INPUT_POST, 'billing_address', FILTER_SANITIZE_STRING);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    
    if (empty($shipping_address)) $errors[] = "Shipping address is required";
    if (empty($billing_address)) $errors[] = "Billing address is required";
    if (empty($payment_method)) $errors[] = "Payment method is required";
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, total_amount, shipping_address, 
                    billing_address, payment_method, shipping_cost, tax_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $order_number = 'ORD-' . time() . '-' . mt_rand(1000, 9999);
            $stmt->execute([
                $_SESSION['user_id'],
                $order_number,
                $total,
                $shipping_address,
                $billing_address,
                $payment_method,
                $shipping,
                $tax
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Create order items
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cartItems as $item) {
                $stmt->execute([
                    $order_id,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['product']['price']
                ]);
                
                // Update product stock
                $updateStock = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE id = ?
                ");
                $updateStock->execute([$item['quantity'], $item['product']['id']]);
            }
            
            $pdo->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to order confirmation
            header("Location: /order-confirmation.php?order=" . $order_number);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Order processing failed. Please try again.";
        }
    }
}

$pageTitle = "Checkout";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="checkout-layout">
        <div class="checkout-form">
            <h1>Checkout</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-section">
                    <h2>Shipping Information</h2>
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address</label>
                        <textarea id="shipping_address" name="shipping_address" 
                                class="form-control" required><?php echo htmlspecialchars($user['shipping_address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Billing Information</h2>
                    <div class="form-group">
                        <label for="billing_address">Billing Address</label>
                        <textarea id="billing_address" name="billing_address" 
                                class="form-control" required><?php echo htmlspecialchars($user['billing_address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Payment Method</h2>
                    <div class="form-group">
                        <select name="payment_method" class="form-control" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <div class="item-info">
                        <h4><?php echo htmlspecialchars($item['product']['name']); ?></h4>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="item-price">
                        $<?php echo number_format($item['total'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="summary-totals">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>$<?php echo number_format($shipping, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax</span>
                    <span>$<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 