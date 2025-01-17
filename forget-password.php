<?php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $errors[] = "Email is required";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
            $stmt->execute([$token, $expiry, $email]);

            // Send email (implement your email sending logic here)
            $resetLink = "http://yourdomain.com/reset-password.php?token=" . $token;
            // mail($email, "Password Reset", "Click here to reset your password: " . $resetLink);

            $success = true;
        } else {
            $errors[] = "Email not found";
        }
    }
}

$pageTitle = "Forgot Password";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="auth-form">
        <h1>Forgot Password</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <p>If an account exists with this email, you will receive password reset instructions.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>

        <p class="mt-3"><a href="/login.php">Back to Login</a></p>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 