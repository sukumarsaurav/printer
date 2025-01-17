<?php
require_once 'config.php';

// Get filter parameters
$category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);
$minPrice = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'newest';
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
$perPage = 12;

// Build base query
$query = "
    SELECT DISTINCT p.*, pi.image_path, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE 1=1
";
$params = [];

// Add filters
if ($category) {
    $query .= " AND c.slug = ?";
    $params[] = $category;
}

if ($minPrice !== false && $minPrice !== null) {
    $query .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice !== false && $maxPrice !== null) {
    $query .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'popularity':
        $query = str_replace(
            "SELECT DISTINCT p.*, pi.image_path, c.name as category_name, c.slug as category_slug",
            "SELECT DISTINCT p.*, pi.image_path, c.name as category_name, c.slug as category_slug, 
            COALESCE(p.views, 0) as view_count,
            COALESCE((SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.id), 0) * 10 + COALESCE(p.views, 0) as popularity_score",
            $query
        );
        $query .= " ORDER BY popularity_score DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.created_at DESC";
}

// Get total count for pagination
$countQuery = preg_replace('/SELECT DISTINCT p\.\*\, pi\.image_path\, c\.name as category_name\, c\.slug as category_slug/', 'SELECT COUNT(DISTINCT p.id)', $query);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Add pagination
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = ($page - 1) * $perPage;

// Execute main query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch all categories for filter
$categoryStmt = $pdo->query("SELECT name, slug FROM categories ORDER BY name");
$categories = $categoryStmt->fetchAll();

$pageTitle = "Shop Our Products";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="shop-layout">
        <!-- Filters Sidebar -->
        <aside class="filters">
            <h2>Filters</h2>
            <form action="" method="GET" id="filter-form">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <div class="checkbox-group">
                        <input type="radio" name="category" value="" id="cat-all"
                            <?php echo !$category ? 'checked' : ''; ?>>
                        <label for="cat-all">All Categories</label>
                    </div>
                    <?php foreach ($categories as $cat): ?>
                        <div class="checkbox-group">
                            <input type="radio" 
                                   name="category" 
                                   value="<?php echo htmlspecialchars($cat['slug']); ?>"
                                   id="cat-<?php echo htmlspecialchars($cat['slug']); ?>"
                                   <?php echo $category === $cat['slug'] ? 'checked' : ''; ?>>
                            <label for="cat-<?php echo htmlspecialchars($cat['slug']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="filter-section">
                    <h3>Price Range</h3>
                    <div class="price-inputs">
                        <input type="number" 
                               name="min_price" 
                               placeholder="Min" 
                               value="<?php echo $minPrice ?? ''; ?>"
                               min="0">
                        <span>to</span>
                        <input type="number" 
                               name="max_price" 
                               placeholder="Max" 
                               value="<?php echo $maxPrice ?? ''; ?>"
                               min="0">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="/shop.php" class="btn btn-secondary">Clear Filters</a>
            </form>
        </aside>

        <!-- Products Grid -->
        <main class="products-section">
            <div class="products-header">
                <p class="product-count"><?php echo $totalProducts; ?> Products found</p>
                <div class="sort-section">
                    <select name="sort" class="sort-select" onchange="location = this.value;">
                        <option value="?sort=newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="?sort=price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="?sort=price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="?sort=popularity" <?php echo $sort === 'popularity' ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="no-products">
                    <p>No products found matching your criteria.</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="/product-details.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                    <?php if ($product['image_path']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <img src="/assets/images/placeholder.jpg" alt="Product image placeholder">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="product-info">
                                <h3>
                                    <a href="/product-details.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <p class="category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                <button class="btn btn-primary add-to-cart" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $sort ? '&sort=' . urlencode($sort) : ''; ?>" 
                               class="<?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        
        fetch('/api/cart-update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart!');
                // Optionally update cart count in header
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to add product to cart');
        });
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 