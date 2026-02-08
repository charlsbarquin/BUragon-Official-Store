<?php
$page_title = "FAQs";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/about.css">
<main class="about-main">
    <section class="about-hero enhanced-hero">
        <div class="about-hero-overlay"></div>
        <div class="container">
            <h1>Frequently Asked Questions</h1>
            <p class="about-tagline">Find answers to common questions about shopping, orders, shipping, and more.</p>
            <a href="#faqs" class="about-cta">View FAQs</a>
        </div>
    </section>
    
    <section class="about-content">
        <div class="container">
            <h2>Quick Help</h2>
            <p>We've compiled answers to the most common questions we receive about our products, ordering process, shipping, and more. If you don't find what you're looking for, please don't hesitate to contact us.</p>
            
            <h2>Popular Topics</h2>
            <ul class="about-features">
                <li><i class="fas fa-shopping-cart"></i> Ordering & Payments</li>
                <li><i class="fas fa-truck"></i> Shipping & Delivery</li>
                <li><i class="fas fa-exchange-alt"></i> Returns & Exchanges</li>
                <li><i class="fas fa-percentage"></i> Student Discounts</li>
            </ul>
        </div>
    </section>
    
    <section class="about-faq-section" id="faqs">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="about-faq-list">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How do I place an order?</button>
                    <div class="faq-answer">Browse our products, add items to your cart, and proceed to checkout. You'll need to create an account or log in to complete your purchase.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">What payment methods do you accept?</button>
                    <div class="faq-answer">We accept credit/debit cards, GCash, and bank transfers. All payments are securely processed.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How can I track my order?</button>
                    <div class="faq-answer">After your order is shipped, you'll receive a tracking number via email. You can also view your order status in your account dashboard.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How long does shipping take?</button>
                    <div class="faq-answer">Shipping within Bicol typically takes 2-5 business days. National shipping may take 5-10 business days.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Can I return or exchange an item?</button>
                    <div class="faq-answer">Yes! Please see our Returns & Refunds policy for details on how to return or exchange items.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">How do I contact customer service?</button>
                    <div class="faq-answer">You can reach us via the Contact page, email (store@bicol-u.edu.ph), or phone ((052) 742-5165).</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Do you offer student discounts?</button>
                    <div class="faq-answer">Yes, students can enjoy special discounts on select products. Check our homepage or subscribe to our newsletter for updates.</div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="about-testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="about-testimonials-list">
                <blockquote>"The FAQ section answered all my questions before I even needed to contact support. Very helpful!" <span>- Juan D.</span></blockquote>
                <blockquote>"Clear and concise answers made my shopping experience smooth and easy." <span>- Maria S.</span></blockquote>
            </div>
        </div>
    </section>
    
    <section class="about-contact" id="contact">
        <div class="container">
            <h2>Still Have Questions?</h2>
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
                    <label for="contact-message">Your Question</label>
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