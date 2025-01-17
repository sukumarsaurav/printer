<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Replace deprecated FILTER_SANITIZE_STRING with htmlspecialchars
$slug = isset($_GET['slug']) ? htmlspecialchars($_GET['slug'], ENT_QUOTES, 'UTF-8') : null;
if (!$slug) {
    header('Location: /blog.php');
    exit;
}

// Fetch blog post details - Remove user fields
$stmt = $pdo->prepare("
    SELECT b.*, bc.name as category_name, bc.slug as category_slug
    FROM blog_posts b
    LEFT JOIN blog_categories bc ON b.category_id = bc.id
    WHERE b.slug = ? AND b.status = 'published'
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: /blog.php');
    exit;
}

// Fetch comments - Remove user fields
$commentStmt = $pdo->prepare("
    SELECT c.*
    FROM blog_comments c
    WHERE c.post_id = ? AND c.parent_id IS NULL
    ORDER BY c.created_at DESC
");
$commentStmt->execute([$post['id']]);
$comments = $commentStmt->fetchAll();

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Replace deprecated FILTER_SANITIZE_STRING
    $comment = isset($_POST['content']) ? htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8') : '';
    $parentId = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT);
    
    if (!empty($comment)) {
        $commentInsert = $pdo->prepare("
            INSERT INTO blog_comments (post_id, user_id, parent_id, comment, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $commentInsert->execute([$post['id'], $_SESSION['user_id'], $parentId, $comment]);
        
        // Redirect to prevent form resubmission
        header("Location: /blog-details.php?slug=" . urlencode($slug) . "#comments");
        exit;
    }
}

// Update view count
$viewStmt = $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
$viewStmt->execute([$post['id']]);

$pageTitle = $post['title'];
require_once 'templates/header.php';
?>

<div class="container">
    <article class="blog-post">
        <header class="post-header">
            <div class="post-meta">
                <span class="category">
                    <a href="/blog.php?category=<?php echo htmlspecialchars($post['category_slug']); ?>">
                        <?php echo htmlspecialchars($post['category_name']); ?>
                    </a>
                </span>
                <span class="date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
            </div>
            
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        </header>

        <?php if ($post['featured_image']): ?>
            <div class="post-image">
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
        <?php endif; ?>

        <div class="post-content">
            <?php echo $post['content']; ?>
        </div>
    </article>

    <section id="comments" class="comments-section">
        <h2>Comments (<?php echo count($comments); ?>)</h2>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="" method="POST" class="comment-form">
                <div class="form-group">
                    <label for="content">Leave a Comment</label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
        <?php else: ?>
            <p>Please <a href="/login.php">login</a> to leave a comment.</p>
        <?php endif; ?>

        <div class="comments-list">
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-meta">
                        <span class="date"><?php echo date('M j, Y', strtotime($comment['created_at'])); ?></span>
                    </div>
                    <div class="comment-content">
                        <?php echo htmlspecialchars($comment['comment']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php require_once 'templates/footer.php'; ?> 