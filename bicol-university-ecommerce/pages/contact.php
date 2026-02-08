<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
$pdo = getDbConnection();

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || $email === '' || $message === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message, source) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$name, $email, $message, 'contact'])) {
            $success = true;
        } else {
            $error = 'Failed to send your message. Please try again.';
        }
    }
}
?>
<main>
    <section class="hero-section" style="background: linear-gradient(90deg, #fafdff 60%, #f5f7fa 100%); padding: 48px 0 24px 0; text-align: center;">
        <h1 style="font-size: 2.4rem; color: var(--primary); font-weight: 800; margin-bottom: 10px;">Contact Us</h1>
        <p style="color: var(--dark-text); font-size: 1.15rem;">We'd love to hear from you! Reach out for questions, feedback, or support.</p>
    </section>
    <div class="contact-container">
        <?php if ($success): ?>
            <div class="contact-success">Thank you for your message! We will get back to you soon.</div>
        <?php elseif ($error): ?>
            <div class="contact-error" style="background:#ffeaea;color:#dc3545;border-radius:6px;padding:10px 14px;margin-bottom:10px;text-align:center;"> <?php echo htmlspecialchars($error); ?> </div>
        <?php endif; ?>
        <form class="contact-form" method="post" action="" aria-label="Contact form">
            <label class="contact-label" for="name">Your Name</label>
            <input class="contact-input" type="text" id="name" name="name" required autocomplete="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            <label class="contact-label" for="email">Your Email</label>
            <input class="contact-input" type="email" id="email" name="email" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <label class="contact-label" for="message">Message</label>
            <textarea class="contact-textarea" id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            <div style="background: #fafdff; color: var(--primary); border-left: 4px solid var(--secondary); border-radius: 6px; padding: 10px 16px; margin-bottom: 10px; font-size: 0.98rem;">
                <i class="fas fa-info-circle" style="color: var(--secondary);"></i> We typically respond within 1-2 business days.
            </div>
            <button class="contact-btn" type="submit" aria-label="Send message">Send Message</button>
        </form>
        <div class="contact-info" style="margin-top: 40px;">
            <h3 style="color: var(--secondary); font-size: 1.2rem; margin-bottom: 10px;"><i class="fas fa-map-marker-alt"></i> Our Office</h3>
            <p><i class="fas fa-map-marker-alt"></i> Bicol University, Legazpi City, Albay</p>
            <p><i class="fas fa-phone"></i> (052) 742-5165</p>
            <p><i class="fas fa-envelope"></i> <a href="mailto:store@bicol-u.edu.ph" style="color: var(--primary); text-decoration: underline;">store@bicol-u.edu.ph</a></p>
        </div>
    </div>
</main>
<?php require_once '../includes/footer.php'; ?> 