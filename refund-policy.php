<?php
$pageTitle = "Refund Policy";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="policy-content">
        <h1>Refund Policy</h1>
        <p>Last updated: <?php echo date('F j, Y'); ?></p>

        <section>
            <h2>1. Return Period</h2>
            <p>You have 30 days from the date of delivery to return items for a full refund.</p>
        </section>

        <section>
            <h2>2. Return Conditions</h2>
            <ul>
                <li>Items must be unused and in original packaging</li>
                <li>All tags and labels must be attached</li>
                <li>Include original receipt or proof of purchase</li>
            </ul>
        </section>

        <!-- Add more sections as needed -->
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 