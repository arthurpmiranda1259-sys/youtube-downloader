// Landing Page - Renato Araújo
// Performance e Interatividade Otimizada

(function() {
    'use strict';

    // Smooth scroll para links internos
    const smoothScroll = () => {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                e.preventDefault();
                const target = document.querySelector(targetId);
                
                if (target) {
                    const navHeight = document.querySelector('.navbar').offsetHeight;
                    const targetPosition = target.offsetTop - navHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    };

    // Navbar background ao scroll
    const handleNavbarScroll = () => {
        const navbar = document.querySelector('.navbar');
        let ticking = false;

        const updateNavbar = () => {
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(10, 10, 10, 0.98)';
                navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.3)';
            } else {
                navbar.style.background = 'rgba(10, 10, 10, 0.95)';
                navbar.style.boxShadow = 'none';
            }
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateNavbar);
                ticking = true;
            }
        }, { passive: true });
    };

    // Intersection Observer para animações de entrada
    const setupIntersectionObserver = () => {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target); // Remove observação após animação
                }
            });
        }, observerOptions);

        // Elementos para animar
        const animatedElements = document.querySelectorAll('.stat-box, .about-image, .video-container');
        
        animatedElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    };

    // Lazy loading para vídeos (fallback adicional)
    const setupLazyVideo = () => {
        const video = document.querySelector('.video-wrapper video');
        if (!video) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    video.preload = 'auto';
                    observer.unobserve(video);
                }
            });
        }, { rootMargin: '200px' });

        observer.observe(video);
    };

    // Controle do vídeo com overlay
    const setupVideoPlayer = () => {
        const videoWrapper = document.getElementById('videoWrapper');
        const video = document.getElementById('mainVideo');
        const overlay = document.getElementById('videoOverlay');

        if (!videoWrapper || !video || !overlay) return;

        // Click no overlay ou wrapper para iniciar vídeo
        const playVideo = () => {
            video.play();
            overlay.classList.add('hidden');
            video.setAttribute('controls', 'controls');
        };

        videoWrapper.addEventListener('click', function(e) {
            if (!video.paused) return; // Se já está tocando, deixa os controles nativos funcionarem
            playVideo();
        });

        overlay.addEventListener('click', playVideo);

        // Quando o vídeo pausar, mostra overlay novamente (opcional)
        video.addEventListener('pause', function() {
            if (video.currentTime > 0 && video.currentTime < video.duration) {
                // Não mostra overlay se pausou no meio (deixa controles normais)
                // overlay.classList.remove('hidden');
            }
        });

        // Quando o vídeo terminar
        video.addEventListener('ended', function() {
            overlay.classList.remove('hidden');
            video.removeAttribute('controls');
        });
    };

    // Tracking de eventos (GTM/Analytics ready)
    const trackEvent = (category, action, label) => {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                'event_category': category,
                'event_label': label
            });
        }
        
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                'event': 'custom_event',
                'category': category,
                'action': action,
                'label': label
            });
        }
    };

    // Rastreamento de cliques nos CTAs
    const setupCTATracking = () => {
        document.querySelectorAll('.cta-button, .whatsapp-float').forEach(button => {
            button.addEventListener('click', function() {
                const buttonText = this.textContent.trim() || 'WhatsApp Float';
                trackEvent('CTA', 'click', buttonText);
            });
        });
    };

    // Performance: Preload de imagens críticas
    const preloadCriticalImages = () => {
        const criticalImages = [
            'assets/images/IMG_0753.webp'
        ];

        criticalImages.forEach(src => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = src;
            document.head.appendChild(link);
        });
    };

    // Inicialização com verificação de DOM carregado
    const init = () => {
        smoothScroll();
        handleNavbarScroll();
        setupIntersectionObserver();
        setupLazyVideo();
        setupVideoPlayer();
        setupCTATracking();
        
        // Log de inicialização (apenas em dev)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('✅ Landing Page iniciada com sucesso');
        }
    };

    // Aguardar DOM carregar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Preload de imagens críticas
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', preloadCriticalImages);
    } else {
        preloadCriticalImages();
    }

})();