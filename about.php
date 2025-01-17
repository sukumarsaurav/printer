<?php
$pageTitle = "About Us";
require_once 'templates/header.php';
require_once 'templates/breadcrumb.php';
?>

<div class="container">
    <div class="about-content">
        <section class="hero-section">
            <h1>About Us</h1>
            <p class="lead">Your trusted destination for quality products and exceptional service.</p>
        </section>

        <section class="our-story">
            <h2>Our Story</h2>
            <div class="story-content">
                <div class="story-text">
                    <p>Founded in 2024, we started with a simple mission: to provide high-quality products at reasonable prices while delivering exceptional customer service. What began as a small online store has grown into a trusted e-commerce destination.</p>
                    <p>Our commitment to quality, customer satisfaction, and sustainable business practices has helped us build a loyal customer base and establish ourselves as a leader in the industry.</p>
                </div>
                <div class="story-image">
                    <img src="/assets/images/about/store-front.jpg" alt="Our Store" class="rounded-image">
                </div>
            </div>
        </section>

        <section class="our-values">
            <h2>Our Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <i class="fas fa-heart"></i>
                    <h3>Customer First</h3>
                    <p>We prioritize customer satisfaction in everything we do.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Quality</h3>
                    <p>We ensure all our products meet the highest quality standards.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-leaf"></i>
                    <h3>Sustainability</h3>
                    <p>We're committed to environmentally responsible practices.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-handshake"></i>
                    <h3>Integrity</h3>
                    <p>We conduct business with honesty and transparency.</p>
                </div>
            </div>
        </section>

        <section class="team-section">
            <h2>Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="/assets/images/team/member1.jpg" alt="Team Member">
                    <h3>John Doe</h3>
                    <p class="position">CEO & Founder</p>
                </div>
                <div class="team-member">
                    <img src="/assets/images/team/member2.jpg" alt="Team Member">
                    <h3>Jane Smith</h3>
                    <p class="position">Operations Manager</p>
                </div>
                <!-- Add more team members as needed -->
            </div>
        </section>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?> 