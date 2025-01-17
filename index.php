<?php
require_once 'config.php';

// Fetch featured products
$stmt = $pdo->prepare("
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
    WHERE p.stock_quantity > 0 
    ORDER BY p.created_at DESC 
    LIMIT 12
");
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

$pageTitle = "Welcome to Modern E-commerce";
require_once 'templates/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Welcome to Modern E-commerce</h1>
            <p>Discover amazing products at great prices</p>
            <a href="/shop.php" class="btn btn-primary">Shop Now</a>
        </div>
    </div>
</div>

<section class="services-section">
    <div class="container">
        <h2>Our Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <i class="fas fa-shipping-fast"></i>
                <h3>Fast Delivery</h3>
                <p>Free shipping on orders over $50</p>
            </div>
            <div class="service-card">
                <i class="fas fa-lock"></i>
                <h3>Secure Payment</h3>
                <p>100% secure payment processing</p>
            </div>
            <div class="service-card">
                <i class="fas fa-headset"></i>
                <h3>24/7 Support</h3>
                <p>Dedicated support team</p>
            </div>
            <div class="service-card">
                <i class="fas fa-undo"></i>
                <h3>Easy Returns</h3>
                <p>30-day return policy</p>
            </div>
        </div>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <img src="/assets/images/placeholder.jpg" alt="Product image placeholder">
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3>
                            <a href="/product-details.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <button class="btn btn-primary add-to-cart" 
                                data-product-id="<?php echo $product['id']; ?>">
                            Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?> 