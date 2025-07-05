// Mobile menu functionality
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const mobileOverlay = document.getElementById('mobileOverlay');

function toggleMobileMenu() {
    sidebar.classList.toggle('mobile-visible');
    mobileOverlay.classList.toggle('active');
    document.body.style.overflow = sidebar.classList.contains('mobile-visible') ? 'hidden' : '';
}

function closeMobileMenu() {
    sidebar.classList.remove('mobile-visible');
    mobileOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

mobileMenuBtn.addEventListener('click', toggleMobileMenu);
mobileOverlay.addEventListener('click', closeMobileMenu);

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeMobileMenu();
    }
});

// Navigation item click handling
const navItems = document.querySelectorAll('.nav-item');
navItems.forEach(item => {
    item.addEventListener('click', function(e) {
        // e.preventDefault();
        
        // Remove active class from all items
        navItems.forEach(nav => nav.classList.remove('active'));
        
        // Add active class to clicked item
        this.classList.add('active');
        
        // Close mobile menu if open
        if (window.innerWidth <= 768) {
            closeMobileMenu();
        }
    });
});

// Smooth scrolling for sidebar on mobile
let isScrolling = false;
sidebar.addEventListener('scroll', function() {
    if (!isScrolling) {
        window.requestAnimationFrame(function() {
            isScrolling = false;
        });
        isScrolling = true;
    }
});