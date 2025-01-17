<?php
$pageTitle = "Privacy Policy";
require_once 'templates/header.php';
require_once 'templates/breadcrumb.php';
?>

<div class="container">
    <div class="policy-content">
        <h1>Privacy Policy</h1>
        <p>Last updated: <?php echo date('F j, Y'); ?></p>

        <section>
            <h2>1. Information We Collect</h2>
            <p>We collect information that you provide directly to us, including:</p>
            <ul>
                <li>Name and contact information</li>
                <li>Account credentials</li>
                <li>Payment information</li>
                <li>Order history</li>
            </ul>
        </section>

        <section>
            <h2>2. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process your orders</li>
                <li>Send order confirmations</li>
                <li>Provide customer support</li>
                <li>Send marketing communications (with consent)</li>
            </ul>
        </section>

        <!-- Add more sections as needed -->
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 