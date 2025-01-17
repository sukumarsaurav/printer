<?php
require_once 'config.php';

$errors = [];
$success = false;
$validToken = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $validToken = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        
        try {
            $stmt->execute([$hashedPassword, $token]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Password reset failed. Please try again.";
        }
    }
}

$pageTitle = "Reset Password";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="auth-form">
        <h1>Reset Password</h1>

        <?php if (!$validToken && !$success): ?>
            <div class="alert alert-danger">
                <p>Invalid or expired reset token. Please request a new password reset.</p>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <p>Password has been reset successfully. <a href="/login.php">Click here to login</a></p>
            </div>
        <?php else: ?>
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
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 