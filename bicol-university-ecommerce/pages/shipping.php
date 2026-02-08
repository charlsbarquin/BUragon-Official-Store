<?php
$page_title = "Shipping Policy";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/about.css">
<main class="about-main">
    <section class="about-hero enhanced-hero">
        <div class="about-hero-overlay"></div>
        <div class="container">
            <h1>Shipping Policy</h1>
            <p class="about-tagline">Your official Bicol University merchandise store. Discover our shipping options, delivery times, and tracking information.</p>
            <a href="#shipping-info" class="about-cta">View Details</a>
        </div>
    </section>
    <section class="about-content" id="shipping-info">
        <div class="container">
            <h2>Our Shipping Process</h2>
            <p>At BUragon, we are dedicated to providing fast and reliable shipping services to the Bicol University community. Our mission is to get your orders to you as quickly and efficiently as possible.</p>
            <h2>Shipping Options</h2>
            <ul class="about-features">
                <li><i class="fas fa-shipping-fast"></i> Standard Shipping: 5-7 business days</li>
                <li><i class="fas fa-rocket"></i> Express Shipping: 2-3 business days</li>
                <li><i class="fas fa-store"></i> Campus Pickup: Available at all BU campuses</li>
                <li><i class="fas fa-calendar-check"></i> Same-day processing for orders before 12pm</li>
            </ul>
            <h2>Delivery Areas & Times</h2>
            <p>We offer nationwide shipping across the Philippines with special rates for Bicol Region residents. Most orders within Bicol arrive within 2-3 business days after processing.</p>
        </div>
    </section>
    <section class="about-faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="about-faq-list">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How long does shipping take?</button>
                    <div class="faq-answer">Standard shipping takes 5-7 business days. Express shipping takes 2-3 business days. Processing time is 1-2 business days.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Do you offer free shipping?</button>
                    <div class="faq-answer">We offer free standard shipping for orders over ₱1,500 within Bicol Region. Check our promotions for current offers.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Can I track my order?</button>
                    <div class="faq-answer">Yes! You'll receive a tracking number via email once your order ships. You can also track it in your account dashboard.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">What if I'm not available to receive my package?</button>
                    <div class="faq-answer">Our courier will attempt delivery 3 times. After that, the package will be held at their nearest hub for pickup.</div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="about-testimonials-list">
                <blockquote>"Received my order faster than expected! The tracking updates were very helpful." <span>- Maria S.</span></blockquote>
                <blockquote>"Campus pickup was so convenient. Saved me shipping fees too!" <span>- John D.</span></blockquote>
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