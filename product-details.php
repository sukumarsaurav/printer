<?php
require_once 'config.php';

$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_STRING);
if (!$slug) {
    header('Location: /shop.php');
    exit;
}

// Fetch product details with primary image
$stmt = $pdo->prepare("
    SELECT p.*, pi.image_path, c.name as category_name 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ?
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /shop.php');
    exit;
}

// Fetch all product images
$imageStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
$imageStmt->execute([$product['id']]);
$productImages = $imageStmt->fetchAll();

// Fetch related products
$relatedStmt = $pdo->prepare("
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.category_id = ? AND p.id != ?
    LIMIT 4
");
$relatedStmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $relatedStmt->fetchAll();

// Update view count
$viewStmt = $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
$viewStmt->execute([$product['id']]);

$pageTitle = $product['name'];
require_once 'templates/header.php';

if ($product) {
    // Increment view counter
    $updateViews = $pdo->prepare("
        UPDATE products 
        SET views = COALESCE(views, 0) + 1 
        WHERE id = ?
    ");
    $updateViews->execute([$product['id']]);
}
?>

<div class="container">
    <div class="product-details">
        <div class="product-gallery">
            <div class="main-image">
                <img src="<?php echo htmlspecialchars($product['image_path'] ?? '/assets/images/placeholder.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     id="main-product-image">
            </div>
            <?php if (count($productImages) > 1): ?>
                <div class="thumbnail-gallery">
                    <?php foreach ($productImages as $image): ?>
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="Product thumbnail"
                             onclick="updateMainImage(this.src)"
                             class="thumbnail">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <nav class="breadcrumb">
                <a href="/shop.php">Shop</a> &gt; 
                <a href="/shop.php?category=<?php echo htmlspecialchars($product['category_id']); ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a> &gt; 
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>

            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
            
            <div class="stock-status">
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="in-stock">In Stock (<?php echo $product['stock_quantity']; ?>)</span>
                <?php else: ?>
                    <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </div>

            <div class="description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>

            <?php if ($product['stock_quantity'] > 0): ?>
                <form class="add-to-cart-form">
                    <div class="quantity-input">
                        <label for="quantity">Quantity:</label>
                        <input type="number" 
                               id="quantity" 
                               name="quantity" 
                               value="1" 
                               min="1" 
                               max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <button type="submit" 
                            class="btn btn-primary add-to-cart" 
                            data-product-id="<?php echo $product['id']; ?>">
                        Add to Cart
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($relatedProducts)): ?>
        <section class="related-products">
            <h2>Related Products</h2>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $related): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($related['image_path'] ?? '/assets/images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3>
                                <a href="/product-details.php?slug=<?php echo htmlspecialchars($related['slug']); ?>">
                                    <?php echo htmlspecialchars($related['name']); ?>
                                </a>
                            </h3>
                            <p class="price">$<?php echo number_format($related['price'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
function updateMainImage(src) {
    document.getElementById('main-product-image').src = src;
}

document.querySelector('.add-to-cart-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const quantity = document.getElementById('quantity').value;
    const productId = this.querySelector('.add-to-cart').dataset.productId;
    
    fetch('/api/cart-update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to cart!');
            // Update cart count in header if needed
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add product to cart');
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 