import './bootstrap';

// Reveal / Unreveal on scroll
document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('[data-reveal], [data-reveal-stagger]');

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                } else {
                    entry.target.classList.remove('revealed');
                }
            });
        },
        { threshold: 0.15 }
    );

    elements.forEach((el) => observer.observe(el));
});
