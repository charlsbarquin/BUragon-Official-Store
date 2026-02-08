<?php
// includes/footer.php
?>

<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-about">
                <img src="<?php echo SITE_URL; ?>/assets/images/logos/bu-logo.png" alt="Bicol University Logo" class="logo-img">
                <p>The official merchandise store of Bicol University. Quality products for students, alumni, and supporters.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/products/index.php">Shop</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/about.php">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/contact.php">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/pages/faq.php">FAQs</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/shipping.php">Shipping Policy</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/returns.php">Returns & Refunds</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/size-guide.php">Size Guide</a></li>
                </ul>
            </div>
            
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> Bicol University, Legazpi City, Albay</p>
                <p><i class="fas fa-phone"></i> (052) 742-5165</p>
                <p><i class="fas fa-envelope"></i> store@bicol-u.edu.ph</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Bicol University. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
.main-footer {
    background: #222;
    color: white;
    padding: 70px 0 40px;
}
.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 48px;
    margin-bottom: 48px;
}
.footer-logo, .logo-img {
    max-width: 150px;
    margin-bottom: 24px;
}
.footer-about p {
    color: #aaa;
    margin-bottom: 28px;
}
.footer-social a {
    display: inline-block;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    text-align: center;
    line-height: 40px;
    margin-right: 10px;
    color: white;
    transition: background 0.3s;
}
.footer-social a:hover {
    background: #ff6b00;
}
.footer-links h3 {
    color: white;
    margin-bottom: 24px;
    font-size: 1.2rem;
}
.footer-links ul {
    list-style: none;
    padding: 0;
}
.footer-links li {
    margin-bottom: 14px;
}
.footer-links a {
    color: #aaa;
    transition: color 0.3s;
    text-decoration: none;
}
.footer-links a:hover {
    color: white;
}
.footer-contact p {
    display: flex;
    align-items: center;
    color: #aaa;
    margin-bottom: 20px;
}
.footer-contact i {
    margin-right: 10px;
    color: #ff6b00;
}
.footer-bottom {
    text-align: center;
    padding-top: 40px;
    margin-top: 24px;
    border-top: 1.5px solid rgba(255,255,255,0.13);
    color: #aaa;
    font-size: 1rem;
}
@media (max-width: 700px) {
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 28px;
    }
}
</style>