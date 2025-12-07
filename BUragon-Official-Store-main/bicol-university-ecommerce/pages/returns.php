<?php
$page_title = "Returns & Refunds";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/about.css">
<main class="about-main">
    <section class="about-hero enhanced-hero">
        <div class="about-hero-overlay"></div>
        <div class="container">
            <h1>Returns & Refunds</h1>
            <p class="about-tagline">Your official Bicol University merchandise store. Learn about our hassle-free return policy and refund process.</p>
            <a href="#returns-info" class="about-cta">View Policy</a>
        </div>
    </section>
    <section class="about-content" id="returns-info">
        <div class="container">
            <h2>Our Return Policy</h2>
            <p>At BUragon, we stand behind the quality of our products. If you're not completely satisfied, we'll make it right with our straightforward return process.</p>
            <h2>Key Features</h2>
            <ul class="about-features">
                <li><i class="fas fa-calendar-check"></i> 7-day return window from delivery date</li>
                <li><i class="fas fa-box-open"></i> Items must be unused with original tags</li>
                <li><i class="fas fa-money-bill-wave"></i> Full refunds for eligible returns</li>
                <li><i class="fas fa-exchange-alt"></i> Easy exchanges for available items</li>
            </ul>
            <h2>Refund Process</h2>
            <p>Once we receive your return, we'll inspect it and process your refund within 5-7 business days. Refunds are issued to your original payment method.</p>
        </div>
    </section>
    <section class="about-faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="about-faq-list">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How do I initiate a return?</button>
                    <div class="faq-answer">Contact our support team with your order number and reason for return. We'll email you return instructions within 24 hours.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">What items cannot be returned?</button>
                    <div class="faq-answer">Personalized items, clearance merchandise, and items without original packaging cannot be returned.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Who pays for return shipping?</button>
                    <div class="faq-answer">Customers are responsible for return shipping costs unless the return is due to our error or defective product.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How long do refunds take?</button>
                    <div class="faq-answer">Refunds typically appear in your account within 5-7 business days after we process your return.</div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="about-testimonials-list">
                <blockquote>"The return process was so easy! Got my refund within a week." <span>- Maria S.</span></blockquote>
                <blockquote>"Customer service helped me exchange my shirt for the right size quickly." <span>- John D.</span></blockquote>
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