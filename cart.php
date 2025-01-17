<?php
require_once 'config.php';

// Initialize or get cart from session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch cart items with product details
$cartItems = [];
$total = 0;

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
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

$pageTitle = "Shopping Cart";
require_once 'templates/header.php';
?>

<div class="container">
    <h1>Shopping Cart</h1>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <p>Your cart is empty</p>
            <a href="/shop.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $item['product']['id']; ?>">
                        <div class="item-image">
                            <img src="<?php echo htmlspecialchars($item['product']['image_path'] ?? '/assets/images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                        </div>
                        <div class="item-details">
                            <h3>
                                <a href="/product-details.php?slug=<?php echo htmlspecialchars($item['product']['slug']); ?>">
                                    <?php echo htmlspecialchars($item['product']['name']); ?>
                                </a>
                            </h3>
                            <p class="price">$<?php echo number_format($item['product']['price'], 2); ?></p>
                            
                            <div class="quantity-controls">
                                <button class="decrease-quantity">-</button>
                                <input type="number" 
                                       class="cart-quantity" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $item['product']['stock_quantity']; ?>"
                                       data-product-id="<?php echo $item['product']['id']; ?>">
                                <button class="increase-quantity">+</button>
                            </div>
                            
                            <p class="subtotal">
                                Subtotal: $<?php echo number_format($item['subtotal'], 2); ?>
                            </p>
                            
                            <button class="remove-item" 
                                    data-product-id="<?php echo $item['product']['id']; ?>">
                                Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <a href="/checkout.php" class="btn btn-primary checkout-btn">
                    Proceed to Checkout
                </a>
                
                <a href="/shop.php" class="btn btn-secondary">
                    Continue Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.cart-quantity').forEach(input => {
    input.addEventListener('change', updateCartItem);
});

document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', removeCartItem);
});

document.querySelectorAll('.increase-quantity, .decrease-quantity').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.cart-quantity');
        const currentValue = parseInt(input.value);
        
        if (this.classList.contains('increase-quantity')) {
            input.value = Math.min(currentValue + 1, parseInt(input.max));
        } else {
            input.value = Math.max(currentValue - 1, parseInt(input.min));
        }
        
        input.dispatchEvent(new Event('change'));
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 