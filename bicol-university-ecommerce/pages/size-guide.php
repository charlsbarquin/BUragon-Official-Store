<?php
$page_title = "Size Guide";
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/about.css">
<main class="about-main">
    <section class="about-hero enhanced-hero">
        <div class="about-hero-overlay"></div>
        <div class="container">
            <h1>Size Guide</h1>
            <p class="about-tagline">Your official Bicol University merchandise store. Find the perfect fit for all your BU apparel.</p>
            <a href="#size-charts" class="about-cta">View Charts</a>
        </div>
    </section>
    <section class="about-content" id="size-charts">
        <div class="container">
            <h2>Our Sizing Guide</h2>
            <p>At BUragon, we want to ensure you get the perfect fit for your BU merchandise. Use these size charts to find your ideal size for our apparel.</p>
            
            <h2>Unisex T-Shirts & Hoodies</h2>
            <div style="overflow-x:auto; margin-bottom: 32px;">
                <table class="size-guide-table" style="width:100%; min-width: 340px; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Size</th>
                            <th>Chest (inches)</th>
                            <th>Length (inches)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>XS</td><td>34</td><td>25</td></tr>
                        <tr><td>S</td><td>36</td><td>26</td></tr>
                        <tr><td>M</td><td>38</td><td>27</td></tr>
                        <tr><td>L</td><td>40</td><td>28</td></tr>
                        <tr><td>XL</td><td>42</td><td>29</td></tr>
                        <tr><td>2XL</td><td>44</td><td>30</td></tr>
                    </tbody>
                </table>
            </div>

            <h2>How to Measure</h2>
            <ul class="about-features">
                <li><i class="fas fa-ruler"></i> Chest: Measure around the fullest part of your chest</li>
                <li><i class="fas fa-ruler-vertical"></i> Length: Measure from the highest point of your shoulder to the bottom hem</li>
                <li><i class="fas fa-tshirt"></i> Compare your measurements to our size chart</li>
                <li><i class="fas fa-question-circle"></i> Still unsure? Contact us for personalized sizing help</li>
            </ul>
        </div>
    </section>
    <section class="about-faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="about-faq-list">
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">What if I'm between sizes?</button>
                    <div class="faq-answer">We recommend sizing up for a more relaxed fit, especially for hoodies and sweatshirts. For fitted styles, choose your usual size.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Do your shirts shrink after washing?</button>
                    <div class="faq-answer">Our shirts are pre-shrunk, but we recommend washing in cold water and air drying to maintain size and shape.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Can I exchange if the size doesn't fit?</button>
                    <div class="faq-answer">Yes! We offer free size exchanges within 14 days of delivery. Items must be unworn and in original condition.</div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">Do you offer plus sizes?</button>
                    <div class="faq-answer">Currently we offer up to 2XL, but we're working to expand our size range. Contact us for specific requests.</div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="about-testimonials-list">
                <blockquote>"The size chart was spot on! My hoodie fits perfectly." <span>- Maria S.</span></blockquote>
                <blockquote>"Customer service helped me choose the right size - very helpful!" <span>- John D.</span></blockquote>
            </div>
        </div>
    </section>
    <section class="about-contact" id="contact">
        <div class="container">
            <h2>Need Sizing Help?</h2>
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
                    <label for="contact-message">Your Sizing Question</label>
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