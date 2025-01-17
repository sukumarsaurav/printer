<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Get pagination parameters
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
$perPage = 6;
// Replace deprecated FILTER_SANITIZE_STRING with htmlspecialchars
$category = isset($_GET['category']) ? htmlspecialchars($_GET['category'], ENT_QUOTES, 'UTF-8') : null;

// Build base query - Remove user fields if they don't exist in your database
$query = "
    SELECT b.*, bc.name as category_name, bc.slug as category_slug,
           (SELECT COUNT(*) FROM blog_comments WHERE post_id = b.id) as comment_count
    FROM blog_posts b
    LEFT JOIN blog_categories bc ON b.category_id = bc.id
    WHERE b.status = 'published'
";
$params = [];

// Add category filter
if ($category) {
    $query .= " AND bc.slug = ?";
    $params[] = $category;
}

// Add sorting
$query .= " ORDER BY b.created_at DESC";

// Get total count for pagination
$countQuery = "
    SELECT COUNT(DISTINCT b.id)
    FROM blog_posts b
    LEFT JOIN blog_categories bc ON b.category_id = bc.id
    WHERE b.status = 'published'
";

if ($category) {
    $countQuery .= " AND bc.slug = ?";
}

// Replace the count statement with the new query
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($category ? [$category] : []);
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $perPage);

// Add pagination
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = ($page - 1) * $perPage;

// Execute main query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Fetch categories for sidebar
$categoryStmt = $pdo->query("
    SELECT bc.*, COUNT(b.id) as post_count 
    FROM blog_categories bc
    LEFT JOIN blog_posts b ON bc.id = b.category_id AND b.status = 'published'
    GROUP BY bc.id
    ORDER BY bc.name
");
$categories = $categoryStmt->fetchAll();

$pageTitle = "Blog";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="blog-layout">
        <main class="blog-main">
            <h1>Latest Blog Posts</h1>
            
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <p>No blog posts found.</p>
                </div>
            <?php else: ?>
                <div class="blog-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="blog-card">
                            <?php if ($post['featured_image']): ?>
                                <div class="blog-image">
                                    <a href="/blog-details.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">
                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>">
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span class="category"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                    <span class="date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                
                                <h2>
                                    <a href="/blog-details.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h2>
                                
                                <p class="excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                
                                <div class="blog-footer">
                                    <span class="comments"><?php echo $post['comment_count']; ?> Comments</span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" 
                               class="<?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <aside class="blog-sidebar">
            <div class="sidebar-widget">
                <h3>Categories</h3>
                <ul class="category-list">
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="?category=<?php echo htmlspecialchars($cat['slug']); ?>"
                               class="<?php echo $category === $cat['slug'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                                <span class="count">(<?php echo $cat['post_count']; ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 