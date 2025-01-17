<?php
$pageTitle = "Frequently Asked Questions";
require_once 'templates/header.php';
?>

<div class="container">
    <div class="faq-content">
        <h1>Frequently Asked Questions</h1>

        <div class="faq-section">
            <div class="faq-item">
                <h3>How long does shipping take?</h3>
                <p>Standard shipping typically takes 3-5 business days within the continental US.</p>
            </div>

            <div class="faq-item">
                <h3>What payment methods do you accept?</h3>
                <p>We accept all major credit cards, PayPal, and bank transfers.</p>
            </div>

            <div class="faq-item">
                <h3>Can I modify or cancel my order?</h3>
                <p>Orders can be modified or cancelled within 1 hour of placement.</p>
            </div>

            <!-- Add more FAQ items as needed -->
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.faq-item h3').forEach(header => {
    header.addEventListener('click', () => {
        header.parentElement.classList.toggle('active');
    });
});
</script>

<?php require_once 'templates/footer.php'; ?> 