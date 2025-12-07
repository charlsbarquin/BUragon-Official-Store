// About Us Page JavaScript

// FAQ toggle
function setupFAQ() {
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = btn.closest('.faq-item');
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', !expanded);
            item.classList.toggle('open', !expanded);
        });
    });
}

// Back to Top button
function setupBackToTop() {
    const btn = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            btn.classList.add('show');
        } else {
            btn.classList.remove('show');
        }
    });
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// Contact form AJAX
function setupContactForm() {
    const form = document.querySelector('.about-contact-form');
    if (!form) return;
    const status = form.querySelector('.contact-form-status');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        status.style.display = 'block';
        status.textContent = 'Sending...';
        status.style.color = '#003366';
        const data = new URLSearchParams(new FormData(form));
        fetch('../api/contact.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: data,
        })
        .then(res => res.json())
        .then(response => {
            status.textContent = response.message;
            status.style.color = response.success ? '#28a745' : '#dc3545';
            if (response.success) form.reset();
        })
        .catch(() => {
            status.textContent = 'Sorry, there was an error. Please try again later.';
            status.style.color = '#dc3545';
        });
    });
}

// Animate sections on scroll
function setupSectionAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    document.querySelectorAll('.about-content, .about-faq-section, .about-testimonials, .about-contact').forEach(section => {
        observer.observe(section);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupFAQ();
    setupBackToTop();
    setupContactForm();
    setupSectionAnimations();
}); 