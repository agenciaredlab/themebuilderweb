/**
 * Theme Builder Pro - Frontend JavaScript
 * Handles animations, interactions, and dynamic functionality
 */

(function($) {
    'use strict';

    // ============================================
    // UTILITIES
    // ============================================

    const Utils = {
        debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        throttle(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        isInViewport(element, offset = 0) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) - offset &&
                rect.bottom >= 0 &&
                rect.left <= (window.innerWidth || document.documentElement.clientWidth) &&
                rect.right >= 0
            );
        },

        getScrollPercent() {
            const h = document.documentElement;
            const b = document.body;
            const st = 'scrollTop';
            const sh = 'scrollHeight';
            return (h[st] || b[st]) / ((h[sh] || b[sh]) - h.clientHeight) * 100;
        },

        isMobile() {
            return window.innerWidth < 768;
        },

        isTablet() {
            return window.innerWidth >= 768 && window.innerWidth < 1024;
        },

        getDevice() {
            if (this.isMobile()) return 'mobile';
            if (this.isTablet()) return 'tablet';
            return 'desktop';
        }
    };

    // ============================================
    // ANIMATION HANDLER
    // ============================================

    const AnimationHandler = {
        initialized: false,
        observer: null,
        animatedElements: new Set(),

        init() {
            if (this.initialized) return;

            // Setup Intersection Observer for scroll animations
            this.observer = new IntersectionObserver(
                (entries) => this.handleIntersection(entries),
                {
                    root: null,
                    rootMargin: '0px 0px -100px 0px',
                    threshold: 0.1
                }
            );

            // Observe all animated elements
            document.querySelectorAll('[data-animation]').forEach(el => {
                this.observer.observe(el);
            });

            this.initialized = true;
        },

        handleIntersection(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.animatedElements.has(entry.target)) {
                    this.animateElement(entry.target);
                    this.animatedElements.add(entry.target);
                }
            });
        },

        animateElement(element) {
            const animation = element.dataset.animation;
            const duration = element.dataset.animationDuration || 1000;
            const delay = element.dataset.animationDelay || 0;

            setTimeout(() => {
                element.classList.add('tbp-animated', `tbp-animation-${animation}`);
                element.style.animationDuration = `${duration}ms`;
            }, delay);
        },

        // Manual trigger for dynamic content
        refresh() {
            document.querySelectorAll('[data-animation]').forEach(el => {
                if (!this.animatedElements.has(el)) {
                    this.observer.observe(el);
                }
            });
        }
    };

    // ============================================
    // SCROLL EFFECTS
    // ============================================

    const ScrollEffects = {
        initialized: false,
        scrollElements: [],
        lastScrollY: 0,

        init() {
            if (this.initialized) return;

            this.scrollElements = Array.from(document.querySelectorAll('[data-scroll-effect]'));

            if (this.scrollElements.length > 0) {
                window.addEventListener('scroll', Utils.throttle(() => this.onScroll(), 16));
                this.onScroll(); // Initial call
            }

            this.initialized = true;
        },

        onScroll() {
            const scrollY = window.pageYOffset;
            const scrollDirection = scrollY > this.lastScrollY ? 'down' : 'up';
            this.lastScrollY = scrollY;

            this.scrollElements.forEach(el => {
                const effect = el.dataset.scrollEffect;
                const speed = parseFloat(el.dataset.scrollSpeed) || 0.5;

                switch (effect) {
                    case 'parallax':
                        this.applyParallax(el, speed);
                        break;
                    case 'fade':
                        this.applyFade(el);
                        break;
                    case 'sticky':
                        this.applySticky(el, scrollDirection);
                        break;
                    case 'scale':
                        this.applyScale(el);
                        break;
                    case 'rotate':
                        this.applyRotate(el, speed);
                        break;
                }
            });
        },

        applyParallax(element, speed) {
            const rect = element.getBoundingClientRect();
            const scrolled = window.pageYOffset;
            const offset = (scrolled - element.offsetTop) * speed;
            element.style.transform = `translateY(${offset}px)`;
        },

        applyFade(element) {
            const rect = element.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const elementTop = rect.top;
            const elementHeight = rect.height;

            let opacity = 1;
            if (elementTop < windowHeight * 0.2) {
                opacity = Math.max(0, (elementTop + elementHeight) / (windowHeight * 0.2));
            } else if (elementTop > windowHeight * 0.8) {
                opacity = Math.max(0, 1 - (elementTop - windowHeight * 0.8) / (windowHeight * 0.2));
            }

            element.style.opacity = opacity;
        },

        applySticky(element, scrollDirection) {
            const stickyStart = parseInt(element.dataset.stickyStart) || 0;
            const scrollY = window.pageYOffset;

            if (scrollY > stickyStart) {
                element.classList.add('tbp-sticky-active');
                if (scrollDirection === 'up') {
                    element.classList.remove('tbp-sticky-hidden');
                } else {
                    element.classList.add('tbp-sticky-hidden');
                }
            } else {
                element.classList.remove('tbp-sticky-active', 'tbp-sticky-hidden');
            }
        },

        applyScale(element) {
            if (!Utils.isInViewport(element)) return;

            const rect = element.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const elementCenter = rect.top + rect.height / 2;
            const screenCenter = windowHeight / 2;
            const distance = Math.abs(elementCenter - screenCenter);
            const maxDistance = windowHeight / 2;
            const scale = 1 - (distance / maxDistance) * 0.2;

            element.style.transform = `scale(${Math.max(0.8, scale)})`;
        },

        applyRotate(element, speed) {
            const scrolled = window.pageYOffset;
            const rotation = scrolled * speed;
            element.style.transform = `rotate(${rotation}deg)`;
        }
    };

    // ============================================
    // COUNTER ANIMATION
    // ============================================

    const CounterAnimation = {
        init() {
            const counters = document.querySelectorAll('.tbp-counter-number');

            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.animateCounter(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.5 }
            );

            counters.forEach(counter => observer.observe(counter));
        },

        animateCounter(element) {
            const target = parseInt(element.dataset.target) || 0;
            const duration = parseInt(element.dataset.duration) || 2000;
            const suffix = element.dataset.suffix || '';
            const prefix = element.dataset.prefix || '';
            const decimals = parseInt(element.dataset.decimals) || 0;

            const startTime = performance.now();
            const startValue = 0;

            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (ease-out-quad)
                const easeProgress = 1 - Math.pow(1 - progress, 2);

                const currentValue = startValue + (target - startValue) * easeProgress;
                element.textContent = prefix + currentValue.toFixed(decimals) + suffix;

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            };

            requestAnimationFrame(updateCounter);
        }
    };

    // ============================================
    // PROGRESS BAR ANIMATION
    // ============================================

    const ProgressBarAnimation = {
        init() {
            const progressBars = document.querySelectorAll('.tbp-progress-bar');

            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.animateProgress(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.3 }
            );

            progressBars.forEach(bar => observer.observe(bar));
        },

        animateProgress(element) {
            const fill = element.querySelector('.tbp-progress-fill');
            const value = element.dataset.value || 0;

            setTimeout(() => {
                fill.style.width = `${value}%`;
            }, 100);
        }
    };

    // ============================================
    // LIGHTBOX
    // ============================================

    const Lightbox = {
        overlay: null,
        content: null,
        currentGallery: null,
        currentIndex: 0,

        init() {
            this.createLightbox();
            this.bindEvents();
        },

        createLightbox() {
            this.overlay = document.createElement('div');
            this.overlay.className = 'tbp-lightbox-overlay';
            this.overlay.innerHTML = `
                <button class="tbp-lightbox-close">&times;</button>
                <button class="tbp-lightbox-prev">&#10094;</button>
                <button class="tbp-lightbox-next">&#10095;</button>
                <div class="tbp-lightbox-content">
                    <img class="tbp-lightbox-image" src="" alt="">
                    <div class="tbp-lightbox-caption"></div>
                </div>
                <div class="tbp-lightbox-counter"></div>
            `;
            document.body.appendChild(this.overlay);

            this.content = this.overlay.querySelector('.tbp-lightbox-content');
            this.image = this.overlay.querySelector('.tbp-lightbox-image');
            this.caption = this.overlay.querySelector('.tbp-lightbox-caption');
            this.counter = this.overlay.querySelector('.tbp-lightbox-counter');
        },

        bindEvents() {
            // Open lightbox on click
            document.querySelectorAll('[data-lightbox]').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.open(el);
                });
            });

            // Close button
            this.overlay.querySelector('.tbp-lightbox-close').addEventListener('click', () => this.close());

            // Close on overlay click
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay) this.close();
            });

            // Navigation
            this.overlay.querySelector('.tbp-lightbox-prev').addEventListener('click', () => this.prev());
            this.overlay.querySelector('.tbp-lightbox-next').addEventListener('click', () => this.next());

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (!this.overlay.classList.contains('active')) return;

                switch (e.key) {
                    case 'Escape': this.close(); break;
                    case 'ArrowLeft': this.prev(); break;
                    case 'ArrowRight': this.next(); break;
                }
            });
        },

        open(element) {
            const galleryId = element.dataset.lightbox;
            this.currentGallery = Array.from(document.querySelectorAll(`[data-lightbox="${galleryId}"]`));
            this.currentIndex = this.currentGallery.indexOf(element);

            this.showImage();
            this.overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.overlay.classList.remove('active');
            document.body.style.overflow = '';
        },

        prev() {
            this.currentIndex = (this.currentIndex - 1 + this.currentGallery.length) % this.currentGallery.length;
            this.showImage();
        },

        next() {
            this.currentIndex = (this.currentIndex + 1) % this.currentGallery.length;
            this.showImage();
        },

        showImage() {
            const element = this.currentGallery[this.currentIndex];
            const src = element.dataset.src || element.href || element.querySelector('img')?.src;
            const captionText = element.dataset.caption || element.title || '';

            this.image.src = src;
            this.caption.textContent = captionText;
            this.counter.textContent = `${this.currentIndex + 1} / ${this.currentGallery.length}`;

            // Hide nav if single image
            const nav = this.overlay.querySelectorAll('.tbp-lightbox-prev, .tbp-lightbox-next');
            nav.forEach(btn => {
                btn.style.display = this.currentGallery.length > 1 ? '' : 'none';
            });
        }
    };

    // ============================================
    // SLIDER / CAROUSEL
    // ============================================

    const Slider = {
        sliders: [],

        init() {
            document.querySelectorAll('.tbp-slider').forEach(slider => {
                this.initSlider(slider);
            });
        },

        initSlider(container) {
            const config = {
                container,
                track: container.querySelector('.tbp-slider-track'),
                slides: container.querySelectorAll('.tbp-slide'),
                prevBtn: container.querySelector('.tbp-slider-prev'),
                nextBtn: container.querySelector('.tbp-slider-next'),
                dots: container.querySelector('.tbp-slider-dots'),
                currentIndex: 0,
                autoplay: container.dataset.autoplay === 'true',
                autoplaySpeed: parseInt(container.dataset.autoplaySpeed) || 5000,
                loop: container.dataset.loop !== 'false',
                pauseOnHover: container.dataset.pauseOnHover !== 'false',
                autoplayInterval: null
            };

            if (config.slides.length === 0) return;

            // Create dots
            if (config.dots) {
                config.slides.forEach((_, i) => {
                    const dot = document.createElement('button');
                    dot.className = `tbp-slider-dot ${i === 0 ? 'active' : ''}`;
                    dot.addEventListener('click', () => this.goToSlide(config, i));
                    config.dots.appendChild(dot);
                });
            }

            // Navigation
            if (config.prevBtn) {
                config.prevBtn.addEventListener('click', () => this.prev(config));
            }
            if (config.nextBtn) {
                config.nextBtn.addEventListener('click', () => this.next(config));
            }

            // Touch/Swipe support
            let touchStartX = 0;
            let touchEndX = 0;

            container.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            container.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                if (touchStartX - touchEndX > 50) {
                    this.next(config);
                } else if (touchEndX - touchStartX > 50) {
                    this.prev(config);
                }
            }, { passive: true });

            // Autoplay
            if (config.autoplay) {
                this.startAutoplay(config);

                if (config.pauseOnHover) {
                    container.addEventListener('mouseenter', () => this.stopAutoplay(config));
                    container.addEventListener('mouseleave', () => this.startAutoplay(config));
                }
            }

            this.sliders.push(config);
        },

        goToSlide(config, index) {
            if (index < 0) {
                index = config.loop ? config.slides.length - 1 : 0;
            } else if (index >= config.slides.length) {
                index = config.loop ? 0 : config.slides.length - 1;
            }

            config.currentIndex = index;
            config.track.style.transform = `translateX(-${index * 100}%)`;

            // Update dots
            if (config.dots) {
                config.dots.querySelectorAll('.tbp-slider-dot').forEach((dot, i) => {
                    dot.classList.toggle('active', i === index);
                });
            }

            // Update slides
            config.slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
        },

        prev(config) {
            this.goToSlide(config, config.currentIndex - 1);
        },

        next(config) {
            this.goToSlide(config, config.currentIndex + 1);
        },

        startAutoplay(config) {
            if (config.autoplayInterval) return;
            config.autoplayInterval = setInterval(() => this.next(config), config.autoplaySpeed);
        },

        stopAutoplay(config) {
            if (config.autoplayInterval) {
                clearInterval(config.autoplayInterval);
                config.autoplayInterval = null;
            }
        }
    };

    // ============================================
    // TABS
    // ============================================

    const Tabs = {
        init() {
            document.querySelectorAll('.tbp-tabs').forEach(tabsContainer => {
                this.initTabs(tabsContainer);
            });
        },

        initTabs(container) {
            const tabs = container.querySelectorAll('.tbp-tab');
            const panels = container.querySelectorAll('.tbp-tab-panel');

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => {
                    // Deactivate all
                    tabs.forEach(t => t.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));

                    // Activate clicked
                    tab.classList.add('active');
                    panels[index]?.classList.add('active');
                });
            });
        }
    };

    // ============================================
    // ACCORDION
    // ============================================

    const Accordion = {
        init() {
            document.querySelectorAll('.tbp-accordion').forEach(accordion => {
                this.initAccordion(accordion);
            });
        },

        initAccordion(container) {
            const items = container.querySelectorAll('.tbp-accordion-item');
            const allowMultiple = container.dataset.multiple === 'true';

            items.forEach(item => {
                const header = item.querySelector('.tbp-accordion-header');
                const content = item.querySelector('.tbp-accordion-content');

                header.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');

                    if (!allowMultiple) {
                        items.forEach(i => {
                            i.classList.remove('active');
                            i.querySelector('.tbp-accordion-content').style.maxHeight = null;
                        });
                    }

                    if (!isActive) {
                        item.classList.add('active');
                        content.style.maxHeight = content.scrollHeight + 'px';
                    } else {
                        item.classList.remove('active');
                        content.style.maxHeight = null;
                    }
                });
            });
        }
    };

    // ============================================
    // VIDEO PLAYER
    // ============================================

    const VideoPlayer = {
        init() {
            document.querySelectorAll('.tbp-video-wrapper[data-lazy]').forEach(wrapper => {
                const thumbnail = wrapper.querySelector('.tbp-video-thumbnail');
                const playButton = wrapper.querySelector('.tbp-video-play');

                if (playButton) {
                    playButton.addEventListener('click', () => this.loadVideo(wrapper));
                }
                if (thumbnail) {
                    thumbnail.addEventListener('click', () => this.loadVideo(wrapper));
                }
            });
        },

        loadVideo(wrapper) {
            const videoType = wrapper.dataset.videoType || 'youtube';
            const videoId = wrapper.dataset.videoId;
            const autoplay = wrapper.dataset.autoplay !== 'false';

            let iframe;

            switch (videoType) {
                case 'youtube':
                    iframe = document.createElement('iframe');
                    iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=${autoplay ? 1 : 0}&rel=0`;
                    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                    break;

                case 'vimeo':
                    iframe = document.createElement('iframe');
                    iframe.src = `https://player.vimeo.com/video/${videoId}?autoplay=${autoplay ? 1 : 0}`;
                    iframe.allow = 'autoplay; fullscreen; picture-in-picture';
                    break;
            }

            if (iframe) {
                iframe.className = 'tbp-video-iframe';
                iframe.allowFullscreen = true;
                wrapper.innerHTML = '';
                wrapper.appendChild(iframe);
                wrapper.classList.add('loaded');
            }
        }
    };

    // ============================================
    // FORM HANDLER
    // ============================================

    const FormHandler = {
        init() {
            document.querySelectorAll('.tbp-form').forEach(form => {
                this.initForm(form);
            });
        },

        initForm(form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('[type="submit"]');
                const originalText = submitBtn.textContent;
                const formData = new FormData(form);

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                form.classList.add('tbp-form-loading');

                try {
                    const response = await fetch(tbpFrontend.ajaxUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-WP-Nonce': tbpFrontend.nonce
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showMessage(form, 'success', result.data.message || 'Form submitted successfully!');

                        // Reset form
                        form.reset();

                        // Redirect if specified
                        if (result.data.redirect) {
                            window.location.href = result.data.redirect;
                        }
                    } else {
                        this.showMessage(form, 'error', result.data.message || 'Something went wrong. Please try again.');
                    }
                } catch (error) {
                    console.error('Form submission error:', error);
                    this.showMessage(form, 'error', 'Network error. Please try again.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    form.classList.remove('tbp-form-loading');
                }
            });

            // Field validation
            form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('blur', () => this.validateField(field));
            });
        },

        validateField(field) {
            const wrapper = field.closest('.tbp-form-field');
            if (!wrapper) return true;

            let isValid = true;
            let message = '';

            // Required
            if (field.required && !field.value.trim()) {
                isValid = false;
                message = 'This field is required';
            }

            // Email
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                    message = 'Please enter a valid email address';
                }
            }

            // Min length
            if (field.minLength && field.value.length < field.minLength) {
                isValid = false;
                message = `Minimum ${field.minLength} characters required`;
            }

            // Update UI
            wrapper.classList.toggle('tbp-field-error', !isValid);
            let errorEl = wrapper.querySelector('.tbp-field-error-message');

            if (!isValid) {
                if (!errorEl) {
                    errorEl = document.createElement('span');
                    errorEl.className = 'tbp-field-error-message';
                    wrapper.appendChild(errorEl);
                }
                errorEl.textContent = message;
            } else if (errorEl) {
                errorEl.remove();
            }

            return isValid;
        },

        showMessage(form, type, message) {
            let messageEl = form.querySelector('.tbp-form-message');

            if (!messageEl) {
                messageEl = document.createElement('div');
                messageEl.className = 'tbp-form-message';
                form.insertBefore(messageEl, form.firstChild);
            }

            messageEl.className = `tbp-form-message tbp-form-message-${type}`;
            messageEl.textContent = message;
            messageEl.style.display = 'block';

            // Auto hide after 5 seconds
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 5000);
        }
    };

    // ============================================
    // STICKY HEADER
    // ============================================

    const StickyHeader = {
        header: null,
        placeholder: null,
        lastScrollY: 0,

        init() {
            this.header = document.querySelector('.tbp-header[data-sticky="true"]');
            if (!this.header) return;

            // Create placeholder
            this.placeholder = document.createElement('div');
            this.placeholder.className = 'tbp-header-placeholder';
            this.placeholder.style.height = '0';
            this.header.parentNode.insertBefore(this.placeholder, this.header);

            window.addEventListener('scroll', Utils.throttle(() => this.onScroll(), 16));
            window.addEventListener('resize', Utils.debounce(() => this.updateHeight(), 100));
        },

        onScroll() {
            const scrollY = window.pageYOffset;
            const headerHeight = this.header.offsetHeight;
            const scrollDirection = scrollY > this.lastScrollY ? 'down' : 'up';

            if (scrollY > headerHeight) {
                this.header.classList.add('tbp-header-sticky');
                this.placeholder.style.height = `${headerHeight}px`;

                // Hide on scroll down, show on scroll up
                if (this.header.dataset.stickyBehavior === 'smart') {
                    if (scrollDirection === 'down' && scrollY > headerHeight * 2) {
                        this.header.classList.add('tbp-header-hidden');
                    } else {
                        this.header.classList.remove('tbp-header-hidden');
                    }
                }
            } else {
                this.header.classList.remove('tbp-header-sticky', 'tbp-header-hidden');
                this.placeholder.style.height = '0';
            }

            this.lastScrollY = scrollY;
        },

        updateHeight() {
            if (this.header.classList.contains('tbp-header-sticky')) {
                this.placeholder.style.height = `${this.header.offsetHeight}px`;
            }
        }
    };

    // ============================================
    // SMOOTH SCROLL
    // ============================================

    const SmoothScroll = {
        init() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', (e) => {
                    const targetId = anchor.getAttribute('href');
                    if (targetId === '#') return;

                    const target = document.querySelector(targetId);
                    if (target) {
                        e.preventDefault();
                        this.scrollTo(target);
                    }
                });
            });
        },

        scrollTo(element, offset = 0) {
            const header = document.querySelector('.tbp-header-sticky');
            const headerHeight = header ? header.offsetHeight : 0;
            const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
            const offsetPosition = elementPosition - headerHeight - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    };

    // ============================================
    // BACK TO TOP
    // ============================================

    const BackToTop = {
        button: null,

        init() {
            this.button = document.querySelector('.tbp-back-to-top');
            if (!this.button) return;

            window.addEventListener('scroll', Utils.throttle(() => this.toggleVisibility(), 100));
            this.button.addEventListener('click', () => this.scrollToTop());
        },

        toggleVisibility() {
            const scrollY = window.pageYOffset;
            this.button.classList.toggle('visible', scrollY > 300);
        },

        scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    };

    // ============================================
    // DYNAMIC CONTENT LOADER
    // ============================================

    const DynamicContent = {
        init() {
            // Load more / Infinite scroll
            document.querySelectorAll('[data-load-more]').forEach(btn => {
                btn.addEventListener('click', () => this.loadMore(btn));
            });

            // Infinite scroll
            document.querySelectorAll('[data-infinite-scroll]').forEach(container => {
                this.initInfiniteScroll(container);
            });
        },

        async loadMore(button) {
            const container = document.querySelector(button.dataset.target);
            if (!container) return;

            const page = parseInt(button.dataset.page) || 1;
            const query = button.dataset.query;

            button.disabled = true;
            button.textContent = 'Loading...';

            try {
                const response = await fetch(tbpFrontend.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'tbp_load_more',
                        page: page + 1,
                        query: query,
                        nonce: tbpFrontend.nonce
                    })
                });

                const result = await response.json();

                if (result.success && result.data.html) {
                    container.insertAdjacentHTML('beforeend', result.data.html);
                    button.dataset.page = page + 1;

                    // Reinitialize animations for new content
                    AnimationHandler.refresh();

                    if (!result.data.hasMore) {
                        button.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Load more error:', error);
            } finally {
                button.disabled = false;
                button.textContent = 'Load More';
            }
        },

        initInfiniteScroll(container) {
            const sentinel = document.createElement('div');
            sentinel.className = 'tbp-scroll-sentinel';
            container.appendChild(sentinel);

            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    this.loadMoreContent(container);
                }
            }, { rootMargin: '100px' });

            observer.observe(sentinel);
        },

        async loadMoreContent(container) {
            // Implementation similar to loadMore
        }
    };

    // ============================================
    // INITIALIZATION
    // ============================================

    const init = () => {
        AnimationHandler.init();
        ScrollEffects.init();
        CounterAnimation.init();
        ProgressBarAnimation.init();
        Lightbox.init();
        Slider.init();
        Tabs.init();
        Accordion.init();
        VideoPlayer.init();
        FormHandler.init();
        StickyHeader.init();
        SmoothScroll.init();
        BackToTop.init();
        DynamicContent.init();

        // Trigger resize to handle initial state
        window.dispatchEvent(new Event('resize'));
    };

    // DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for external use
    window.TBP = {
        Utils,
        AnimationHandler,
        ScrollEffects,
        Lightbox,
        Slider,
        Tabs,
        Accordion,
        FormHandler,
        SmoothScroll
    };

})(jQuery);
