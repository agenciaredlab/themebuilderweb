/**
 * Theme Builder Pro - Popup Manager
 * Handles popup display, triggers, and animations
 */

(function($) {
    'use strict';

    class PopupManager {
        constructor() {
            this.popups = new Map();
            this.activePopups = new Set();
            this.displayedPopups = new Set();
            this.scrollHandler = null;
            this.mouseleaveHandler = null;
            this.inactivityTimer = null;
            this.pageViews = this.getPageViews();

            this.init();
        }

        init() {
            // Load popup configurations
            if (typeof tbpPopups !== 'undefined' && tbpPopups.popups) {
                tbpPopups.popups.forEach(popup => {
                    this.registerPopup(popup);
                });
            }

            // Initialize triggers
            this.initTriggers();

            // Bind close events
            this.bindCloseEvents();
        }

        registerPopup(config) {
            const popup = {
                id: config.id,
                element: document.getElementById(`tbp-popup-${config.id}`),
                settings: config.settings || {},
                triggers: config.triggers || [],
                conditions: config.conditions || [],
                frequency: config.frequency || 'always',
                displayed: false
            };

            if (popup.element) {
                this.popups.set(config.id, popup);
            }
        }

        initTriggers() {
            this.popups.forEach((popup, id) => {
                popup.triggers.forEach(trigger => {
                    switch (trigger.type) {
                        case 'on_load':
                            this.initOnLoadTrigger(popup, trigger);
                            break;
                        case 'on_scroll':
                            this.initScrollTrigger(popup, trigger);
                            break;
                        case 'on_scroll_to_element':
                            this.initScrollToElementTrigger(popup, trigger);
                            break;
                        case 'on_click':
                            this.initClickTrigger(popup, trigger);
                            break;
                        case 'exit_intent':
                            this.initExitIntentTrigger(popup, trigger);
                            break;
                        case 'inactivity':
                            this.initInactivityTrigger(popup, trigger);
                            break;
                        case 'after_x_pages':
                            this.initPageViewsTrigger(popup, trigger);
                            break;
                        case 'after_x_sessions':
                            this.initSessionsTrigger(popup, trigger);
                            break;
                    }
                });
            });
        }

        // ============================================
        // TRIGGERS
        // ============================================

        initOnLoadTrigger(popup, trigger) {
            const delay = (trigger.delay || 0) * 1000;

            setTimeout(() => {
                if (this.canShowPopup(popup)) {
                    this.showPopup(popup);
                }
            }, delay);
        }

        initScrollTrigger(popup, trigger) {
            const threshold = trigger.value || 50; // Scroll percentage

            const handler = () => {
                const scrollPercent = this.getScrollPercent();
                if (scrollPercent >= threshold && this.canShowPopup(popup)) {
                    this.showPopup(popup);
                    window.removeEventListener('scroll', handler);
                }
            };

            window.addEventListener('scroll', this.throttle(handler, 100));
        }

        initScrollToElementTrigger(popup, trigger) {
            const selector = trigger.selector;
            if (!selector) return;

            const targetElement = document.querySelector(selector);
            if (!targetElement) return;

            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && this.canShowPopup(popup)) {
                    this.showPopup(popup);
                    observer.disconnect();
                }
            }, { threshold: 0.5 });

            observer.observe(targetElement);
        }

        initClickTrigger(popup, trigger) {
            const selector = trigger.selector || `.tbp-popup-trigger-${popup.id}`;

            document.querySelectorAll(selector).forEach(el => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showPopup(popup);
                });
            });
        }

        initExitIntentTrigger(popup, trigger) {
            const sensitivity = trigger.sensitivity || 20;

            const handler = (e) => {
                if (e.clientY <= sensitivity && this.canShowPopup(popup)) {
                    this.showPopup(popup);
                    document.removeEventListener('mouseleave', handler);
                }
            };

            document.addEventListener('mouseleave', handler);
        }

        initInactivityTrigger(popup, trigger) {
            const timeout = (trigger.timeout || 30) * 1000;

            const resetTimer = () => {
                clearTimeout(this.inactivityTimer);
                this.inactivityTimer = setTimeout(() => {
                    if (this.canShowPopup(popup)) {
                        this.showPopup(popup);
                    }
                }, timeout);
            };

            ['mousemove', 'keypress', 'scroll', 'click'].forEach(event => {
                document.addEventListener(event, resetTimer, { passive: true });
            });

            resetTimer();
        }

        initPageViewsTrigger(popup, trigger) {
            const requiredViews = trigger.value || 3;

            if (this.pageViews >= requiredViews && this.canShowPopup(popup)) {
                setTimeout(() => this.showPopup(popup), 1000);
            }
        }

        initSessionsTrigger(popup, trigger) {
            const requiredSessions = trigger.value || 2;
            const sessions = this.getSessions();

            if (sessions >= requiredSessions && this.canShowPopup(popup)) {
                setTimeout(() => this.showPopup(popup), 1000);
            }
        }

        // ============================================
        // POPUP DISPLAY
        // ============================================

        canShowPopup(popup) {
            // Already displayed in this session
            if (popup.displayed && popup.frequency !== 'always') {
                return false;
            }

            // Check frequency settings
            if (!this.checkFrequency(popup)) {
                return false;
            }

            // Check conditions
            if (!this.checkConditions(popup)) {
                return false;
            }

            // Maximum popups shown
            if (this.activePopups.size >= 1) {
                return false;
            }

            return true;
        }

        checkFrequency(popup) {
            const frequencyKey = `tbp_popup_${popup.id}_shown`;
            const lastShown = localStorage.getItem(frequencyKey);

            if (!lastShown) return true;

            const lastShownDate = new Date(parseInt(lastShown));
            const now = new Date();

            switch (popup.frequency) {
                case 'once':
                    return false;

                case 'once_per_session':
                    return !sessionStorage.getItem(frequencyKey);

                case 'once_per_day':
                    const oneDayAgo = new Date(now - 24 * 60 * 60 * 1000);
                    return lastShownDate < oneDayAgo;

                case 'once_per_week':
                    const oneWeekAgo = new Date(now - 7 * 24 * 60 * 60 * 1000);
                    return lastShownDate < oneWeekAgo;

                case 'once_per_month':
                    const oneMonthAgo = new Date(now - 30 * 24 * 60 * 60 * 1000);
                    return lastShownDate < oneMonthAgo;

                case 'custom':
                    const interval = (popup.settings.frequencyInterval || 1) * 24 * 60 * 60 * 1000;
                    return lastShownDate < new Date(now - interval);

                case 'always':
                default:
                    return true;
            }
        }

        checkConditions(popup) {
            if (!popup.conditions || popup.conditions.length === 0) {
                return true;
            }

            return popup.conditions.every(condition => {
                switch (condition.type) {
                    case 'logged_in':
                        return tbpPopups.isLoggedIn === condition.value;

                    case 'device':
                        return this.getDevice() === condition.value;

                    case 'referrer':
                        return document.referrer.includes(condition.value);

                    case 'url_contains':
                        return window.location.href.includes(condition.value);

                    case 'cookie':
                        return this.getCookie(condition.name) === condition.value;

                    default:
                        return true;
                }
            });
        }

        showPopup(popup) {
            if (!popup.element) return;

            popup.displayed = true;
            this.activePopups.add(popup.id);

            // Add overlay
            const overlay = document.createElement('div');
            overlay.className = 'tbp-popup-overlay';
            overlay.dataset.popupId = popup.id;
            document.body.appendChild(overlay);

            // Position popup
            const position = popup.settings.position || 'center';
            popup.element.className = `tbp-popup tbp-popup-${position}`;

            // Animation
            const animation = popup.settings.entranceAnimation || 'fade-in';
            popup.element.dataset.animation = animation;

            // Show popup
            setTimeout(() => {
                overlay.classList.add('active');
                popup.element.classList.add('active');
                popup.element.classList.add(`tbp-popup-animate-${animation}`);
            }, 10);

            // Prevent body scroll
            document.body.classList.add('tbp-popup-open');

            // Store shown timestamp
            this.recordPopupShown(popup);

            // Track analytics
            this.trackPopupEvent(popup.id, 'view');

            // Auto close
            if (popup.settings.autoClose) {
                const autoCloseDelay = (popup.settings.autoCloseDelay || 5) * 1000;
                setTimeout(() => this.closePopup(popup.id), autoCloseDelay);
            }
        }

        closePopup(popupId) {
            const popup = this.popups.get(popupId);
            if (!popup || !popup.element) return;

            const overlay = document.querySelector(`.tbp-popup-overlay[data-popup-id="${popupId}"]`);
            const exitAnimation = popup.settings.exitAnimation || 'fade-out';

            // Animate out
            popup.element.classList.remove('active');
            popup.element.classList.add(`tbp-popup-animate-${exitAnimation}`);

            if (overlay) {
                overlay.classList.remove('active');
            }

            // Remove after animation
            setTimeout(() => {
                if (overlay) {
                    overlay.remove();
                }
                popup.element.classList.remove(`tbp-popup-animate-${exitAnimation}`);
                this.activePopups.delete(popupId);

                if (this.activePopups.size === 0) {
                    document.body.classList.remove('tbp-popup-open');
                }
            }, 300);
        }

        bindCloseEvents() {
            // Close button
            document.addEventListener('click', (e) => {
                if (e.target.closest('.tbp-popup-close')) {
                    const popup = e.target.closest('.tbp-popup');
                    if (popup) {
                        const popupId = popup.id.replace('tbp-popup-', '');
                        this.closePopup(popupId);
                        this.trackPopupEvent(popupId, 'close');
                    }
                }
            });

            // Close on overlay click
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('tbp-popup-overlay')) {
                    const popupId = e.target.dataset.popupId;
                    const popup = this.popups.get(popupId);
                    if (popup && popup.settings.closeOnOverlay !== false) {
                        this.closePopup(popupId);
                        this.trackPopupEvent(popupId, 'close');
                    }
                }
            });

            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.activePopups.size > 0) {
                    const lastPopupId = Array.from(this.activePopups).pop();
                    const popup = this.popups.get(lastPopupId);
                    if (popup && popup.settings.closeOnEscape !== false) {
                        this.closePopup(lastPopupId);
                        this.trackPopupEvent(lastPopupId, 'close');
                    }
                }
            });

            // CTA clicks
            document.addEventListener('click', (e) => {
                const ctaButton = e.target.closest('.tbp-popup-cta');
                if (ctaButton) {
                    const popup = e.target.closest('.tbp-popup');
                    if (popup) {
                        const popupId = popup.id.replace('tbp-popup-', '');
                        this.trackPopupEvent(popupId, 'conversion');

                        if (ctaButton.dataset.closeOnClick !== 'false') {
                            this.closePopup(popupId);
                        }
                    }
                }
            });
        }

        // ============================================
        // ANALYTICS & TRACKING
        // ============================================

        recordPopupShown(popup) {
            const frequencyKey = `tbp_popup_${popup.id}_shown`;
            localStorage.setItem(frequencyKey, Date.now().toString());
            sessionStorage.setItem(frequencyKey, '1');
        }

        trackPopupEvent(popupId, eventType) {
            if (typeof tbpPopups === 'undefined' || !tbpPopups.trackAnalytics) {
                return;
            }

            fetch(tbpPopups.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'tbp_popup_analytics',
                    popup_id: popupId,
                    event_type: eventType,
                    nonce: tbpPopups.nonce
                })
            }).catch(console.error);
        }

        // ============================================
        // UTILITIES
        // ============================================

        getScrollPercent() {
            const h = document.documentElement;
            const b = document.body;
            const st = 'scrollTop';
            const sh = 'scrollHeight';
            return (h[st] || b[st]) / ((h[sh] || b[sh]) - h.clientHeight) * 100;
        }

        getDevice() {
            const width = window.innerWidth;
            if (width < 768) return 'mobile';
            if (width < 1024) return 'tablet';
            return 'desktop';
        }

        getPageViews() {
            const key = 'tbp_page_views';
            let views = parseInt(sessionStorage.getItem(key) || '0');
            views++;
            sessionStorage.setItem(key, views.toString());
            return views;
        }

        getSessions() {
            const key = 'tbp_sessions';
            let sessions = parseInt(localStorage.getItem(key) || '0');

            if (!sessionStorage.getItem('tbp_session_active')) {
                sessions++;
                localStorage.setItem(key, sessions.toString());
                sessionStorage.setItem('tbp_session_active', '1');
            }

            return sessions;
        }

        getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        throttle(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    }

    // ============================================
    // POPUP BUILDER UI (for frontend editing)
    // ============================================

    class PopupEditor {
        constructor(popupId) {
            this.popupId = popupId;
            this.popup = document.getElementById(`tbp-popup-${popupId}`);
            this.isDragging = false;
            this.currentHandle = null;

            if (this.popup && tbpPopups?.isPreview) {
                this.initEditor();
            }
        }

        initEditor() {
            this.addResizeHandles();
            this.addDragHandle();
            this.bindEvents();
        }

        addResizeHandles() {
            const handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
            handles.forEach(position => {
                const handle = document.createElement('div');
                handle.className = `tbp-popup-resize-handle tbp-popup-resize-${position}`;
                handle.dataset.position = position;
                this.popup.appendChild(handle);
            });
        }

        addDragHandle() {
            const dragHandle = document.createElement('div');
            dragHandle.className = 'tbp-popup-drag-handle';
            dragHandle.innerHTML = '⋮⋮';
            this.popup.insertBefore(dragHandle, this.popup.firstChild);
        }

        bindEvents() {
            // Resize
            this.popup.querySelectorAll('.tbp-popup-resize-handle').forEach(handle => {
                handle.addEventListener('mousedown', (e) => this.startResize(e, handle));
            });

            // Drag
            const dragHandle = this.popup.querySelector('.tbp-popup-drag-handle');
            if (dragHandle) {
                dragHandle.addEventListener('mousedown', (e) => this.startDrag(e));
            }

            document.addEventListener('mousemove', (e) => this.onMouseMove(e));
            document.addEventListener('mouseup', () => this.stopInteraction());
        }

        startResize(e, handle) {
            e.preventDefault();
            this.isDragging = true;
            this.currentHandle = handle.dataset.position;
            this.startX = e.clientX;
            this.startY = e.clientY;
            this.startWidth = this.popup.offsetWidth;
            this.startHeight = this.popup.offsetHeight;
            this.popup.classList.add('tbp-popup-resizing');
        }

        startDrag(e) {
            e.preventDefault();
            this.isDragging = true;
            this.currentHandle = 'drag';
            this.startX = e.clientX;
            this.startY = e.clientY;
            const rect = this.popup.getBoundingClientRect();
            this.startLeft = rect.left;
            this.startTop = rect.top;
            this.popup.classList.add('tbp-popup-dragging');
        }

        onMouseMove(e) {
            if (!this.isDragging) return;

            const deltaX = e.clientX - this.startX;
            const deltaY = e.clientY - this.startY;

            if (this.currentHandle === 'drag') {
                this.popup.style.left = `${this.startLeft + deltaX}px`;
                this.popup.style.top = `${this.startTop + deltaY}px`;
                this.popup.style.transform = 'none';
            } else {
                this.handleResize(deltaX, deltaY);
            }
        }

        handleResize(deltaX, deltaY) {
            let newWidth = this.startWidth;
            let newHeight = this.startHeight;

            if (this.currentHandle.includes('e')) {
                newWidth = this.startWidth + deltaX;
            }
            if (this.currentHandle.includes('w')) {
                newWidth = this.startWidth - deltaX;
            }
            if (this.currentHandle.includes('s')) {
                newHeight = this.startHeight + deltaY;
            }
            if (this.currentHandle.includes('n')) {
                newHeight = this.startHeight - deltaY;
            }

            // Apply constraints
            newWidth = Math.max(300, Math.min(newWidth, window.innerWidth - 40));
            newHeight = Math.max(200, Math.min(newHeight, window.innerHeight - 40));

            this.popup.style.width = `${newWidth}px`;
            this.popup.style.height = `${newHeight}px`;
        }

        stopInteraction() {
            this.isDragging = false;
            this.currentHandle = null;
            this.popup.classList.remove('tbp-popup-resizing', 'tbp-popup-dragging');
        }
    }

    // ============================================
    // INITIALIZATION
    // ============================================

    // Initialize popup manager
    const popupManager = new PopupManager();

    // Expose globally
    window.TBPPopups = {
        manager: popupManager,
        open: (id) => {
            const popup = popupManager.popups.get(id);
            if (popup) {
                popupManager.showPopup(popup);
            }
        },
        close: (id) => popupManager.closePopup(id),
        closeAll: () => {
            popupManager.activePopups.forEach(id => popupManager.closePopup(id));
        }
    };

})(jQuery);
