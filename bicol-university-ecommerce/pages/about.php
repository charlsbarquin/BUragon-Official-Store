<?php
$page_title = "About Us";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/about.css">
<main class="about-main">
    <section class="about-hero enhanced-hero">
        <div class="about-hero-overlay"></div>
        <div class="container">
            <h1>Welcome to BUragon</h1>
            <p class="about-tagline">Your official Bicol University merchandise store. Discover quality products, exclusive student discounts, and everything you need for campus life.</p>
            <a href="#contact" class="about-cta">Contact Us</a>
        </div>
    </section>
    <section class="about-content">
        <div class="container">
            <h2>Our Mission</h2>
            <p>At BUragon, we are dedicated to providing the Bicol University community with authentic, high-quality merchandise and essentials. Our mission is to foster school spirit, support student needs, and celebrate the vibrant culture of BU.</p>
            <h2>Why Shop With Us?</h2>
            <ul class="about-features">
                <li><i class="fas fa-certificate"></i> Officially licensed BU merchandise</li>
                <li><i class="fas fa-tags"></i> Exclusive discounts for students and alumni</li>
                <li><i class="fas fa-shipping-fast"></i> Convenient campus pickup and fast delivery</li>
                <li><i class="fas fa-hands-helping"></i> Support for campus events and organizations</li>
            </ul>
            <h2>Student Discounts & Campus Life</h2>
            <p>We believe in making BU life more affordable and enjoyable. Take advantage of our special student discounts, seasonal offers, and bundles designed for every aspect of campus living—from academics to celebrations!</p>
        </div>
    </section>
    <section class="about-faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="about-faq-list">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How do I get a student discount?</button>
                    <div class="faq-answer">Show your valid BU student ID at checkout or use your student email when registering online to unlock exclusive discounts.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Can I pick up my order on campus?</button>
                    <div class="faq-answer">Yes! Choose 'Campus Pickup' at checkout and select your preferred campus location.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Are your products official BU merchandise?</button>
                    <div class="faq-answer">Absolutely! All our products are officially licensed and approved by Bicol University.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How can I contact customer support?</button>
                    <div class="faq-answer">You can reach us via the contact form below or email us at <a href="mailto:support@buragon.com">support@buragon.com</a>.</div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="about-testimonials-list">
                <blockquote>"Great quality and fast service! Proud to wear my BU hoodie." <span>- Maria S.</span></blockquote>
                <blockquote>"The student discounts are a huge help. Highly recommended!" <span>- John D.</span></blockquote>
            </div>
        </div>
    </section>
    <section class="about-contact" id="contact">
        <div class="container">
            <h2>Contact Us</h2>
            <form class="about-contact-form" autocomplete="off">
                <div class="form-group">
                    <label for="contact-name">Name</label>
                    <input type="text" id="contact-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="contact-email">Email</label>
                    <input type="email" id="contact-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="contact-message">Message</label>
                    <textarea id="contact-message" name="message" rows="4" required></textarea>
                </div>
                <button type="submit" class="about-contact-submit">Send Message</button>
                <div class="contact-form-status" style="display:none;"></div>
            </form>
        </div>
    </section>
    <button id="backToTop" title="Back to Top" aria-label="Back to Top"><i class="fas fa-arrow-up"></i></button>
</main>
<script src="../assets/js/about.js"></script>
<?php require_once '../includes/footer.php'; ?> 