/**
 * REVEXA Sistemas - Main JavaScript
 * Smooth scroll, animations, and interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all components
    initNavbar();
    initSmoothScroll();
    initPortfolioFilter();
    initBackToTop();
    initScrollAnimations();
    initFormValidation();
    initPhoneMask();
    initTiltEffect();
    initCounters();
});

/**
 * Navbar functionality
 */
function initNavbar() {
    const navbar = document.getElementById('navbar');
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-link');

    // Scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        // Update active nav link based on scroll position
        updateActiveNavLink();
    });

    // Mobile menu toggle
    navToggle.addEventListener('click', () => {
        navToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close menu on link click
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    // Close menu on click outside
    document.addEventListener('click', (e) => {
        if (!navbar.contains(e.target) && navMenu.classList.contains('active')) {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
        }
    });
}

/**
 * Update active navigation link based on scroll position
 */
function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let currentSection = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 150;
        const sectionHeight = section.offsetHeight;
        
        if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
            currentSection = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${currentSection}`) {
            link.classList.add('active');
        }
    });
}

/**
 * Smooth scroll for anchor links
 */
function initSmoothScroll() {
    const anchors = document.querySelectorAll('a[href^="#"]');
    
    anchors.forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80;
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Portfolio filter functionality
 */
function initPortfolioFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const filter = btn.getAttribute('data-filter');
            
            // Filter items
            portfolioItems.forEach(item => {
                const category = item.getAttribute('data-category');
                
                if (filter === 'all' || category === filter) {
                    item.classList.remove('hidden');
                    item.style.animation = 'fadeInUp 0.5s ease forwards';
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    });
}

/**
 * Back to top button
 */
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });
    
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Scroll Animations (Intersection Observer)
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Elements to animate
    const elementsToAnimate = document.querySelectorAll('.section-title, .section-subtitle, .about-card, .service-card, .differential-card, .portfolio-item, .contact-card');
    
    elementsToAnimate.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
    
    // Add CSS class for animation
    const style = document.createElement('style');
    style.innerHTML = `
        .animate-in {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
    `;
    document.head.appendChild(style);
}

/**
 * Form validation
 */
function initFormValidation() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const message = document.getElementById('message');
            
            let isValid = true;
            
            // Validate name
            if (!name.value.trim()) {
                showInputError(name, 'Por favor, informe seu nome');
                isValid = false;
            } else {
                clearInputError(name);
            }
            
            // Validate email
            if (!email.value.trim()) {
                showInputError(email, 'Por favor, informe seu e-mail');
                isValid = false;
            } else if (!isValidEmail(email.value)) {
                showInputError(email, 'Por favor, informe um e-mail válido');
                isValid = false;
            } else {
                clearInputError(email);
            }
            
            // Validate message
            if (!message.value.trim()) {
                showInputError(message, 'Por favor, escreva sua mensagem');
                isValid = false;
            } else {
                clearInputError(message);
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Show input error message
 */
function showInputError(input, message) {
    clearInputError(input);
    
    input.style.borderColor = '#dc2626';
    
    const errorElement = document.createElement('span');
    errorElement.className = 'input-error';
    errorElement.style.cssText = 'color: #dc2626; font-size: 13px; margin-top: 6px; display: block;';
    errorElement.textContent = message;
    
    input.parentNode.appendChild(errorElement);
}

/**
 * Clear input error message
 */
function clearInputError(input) {
    input.style.borderColor = '';
    
    const existingError = input.parentNode.querySelector('.input-error');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Phone number mask
 */
function initPhoneMask() {
    const phoneInput = document.getElementById('phone');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = `(${value}`;
                } else if (value.length <= 7) {
                    value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
                } else {
                    value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
                }
            }
            
            e.target.value = value;
        });
    }
}

/**
 * Portfolio modal (optional - for future implementation)
 */
function openPortfolioModal(projectId) {
    // Placeholder for modal functionality
    console.log('Opening project:', projectId);
}

// Add click listeners to portfolio buttons
document.querySelectorAll('.portfolio-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const projectId = btn.getAttribute('data-project');
        openPortfolioModal(projectId);
    });
});

/**
 * 3D Tilt Effect for Hero Cards
 */
function initTiltEffect() {
    const heroContainer = document.querySelector('.hero');
    const cards = document.querySelectorAll('.glass-card');
    
    if (!heroContainer || cards.length === 0) return;
    
    heroContainer.addEventListener('mousemove', (e) => {
        const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
        const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
        
        cards.forEach(card => {
            card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });
    });
    
    // Reset on mouse leave
    heroContainer.addEventListener('mouseleave', () => {
        cards.forEach(card => {
            card.style.transform = 'rotateY(0deg) rotateX(0deg)';
            card.style.transition = 'all 0.5s ease';
        });
    });
    
    // Remove transition on mouse enter to make movement smooth
    heroContainer.addEventListener('mouseenter', () => {
        cards.forEach(card => {
            card.style.transition = 'none';
        });
    });
}

/**
 * Number Counters Animation
 */
function initCounters() {
    const stats = document.querySelectorAll('.stat-number');
    
    if (stats.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const countTo = parseInt(target.innerText.replace('+', ''));
                
                let count = 0;
                const duration = 2000; // 2 seconds
                const increment = countTo / (duration / 16); // 60fps
                
                const timer = setInterval(() => {
                    count += increment;
                    if (count >= countTo) {
                        target.innerText = countTo + '+';
                        clearInterval(timer);
                    } else {
                        target.innerText = Math.floor(count) + '+';
                    }
                }, 16);
                
                observer.unobserve(target);
            }
        });
    }, { threshold: 0.5 });
    
    stats.forEach(stat => observer.observe(stat));
}
