/**
 * Advanced Mobile Enhancements for JAGAPADI
 * Provides progressive mobile features and optimizations
 */

(function () {
    'use strict';

    // Mobile Detection and Device Info
    const MobileDetector = {
        isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
        isIOS: /iPad|iPhone|iPod/.test(navigator.userAgent),
        isAndroid: /Android/.test(navigator.userAgent),
        isTouchDevice: 'ontouchstart' in window || navigator.maxTouchPoints > 0,

        getDeviceInfo() {
            return {
                isMobile: this.isMobile,
                isIOS: this.isIOS,
                isAndroid: this.isAndroid,
                isTouchDevice: this.isTouchDevice,
                screenWidth: window.screen.width,
                screenHeight: window.screen.height,
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                devicePixelRatio: window.devicePixelRatio || 1,
                orientation: screen.orientation ? screen.orientation.type :
                    (window.innerWidth > window.innerHeight ? 'landscape' : 'portrait')
            };
        }
    };

    // Enhanced Touch Interactions
    const TouchEnhancements = {
        init() {
            if (!MobileDetector.isTouchDevice) return;

            this.addTouchFeedback();
            this.optimizeScrolling();
            this.preventZoom();
            this.addSwipeGestures();
        },

        addTouchFeedback() {
            // Add visual feedback for touch interactions
            document.addEventListener('touchstart', function (e) {
                const target = e.target.closest('.btn, .nav-link, .card, .table-responsive tr');
                if (target && !target.classList.contains('no-touch-feedback')) {
                    target.style.transform = 'scale(0.98)';
                    target.style.transition = 'transform 0.1s ease';
                }
            });

            document.addEventListener('touchend', function (e) {
                const target = e.target.closest('.btn, .nav-link, .card, .table-responsive tr');
                if (target && !target.classList.contains('no-touch-feedback')) {
                    setTimeout(() => {
                        target.style.transform = 'scale(1)';
                    }, 100);
                }
            });
        },

        optimizeScrolling() {
            // Enable momentum scrolling on iOS
            const scrollElements = document.querySelectorAll('.table-responsive, .content-wrapper, .sidebar');
            scrollElements.forEach(element => {
                element.style.webkitOverflowScrolling = 'touch';
            });
        },

        preventZoom() {
            // Prevent double-tap zoom on buttons and form elements
            // But exclude form controls to allow proper interaction
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function (e) {
                // Skip prevention for form elements (checkboxes, inputs, etc.)
                const target = e.target;
                if (target && target.matches && target.matches('input, select, textarea, label, button, [type="checkbox"], .checkbox-item, #checkAll')) {
                    return;
                }

                // Also skip if the target is within a form control
                if (target && target.closest && target.closest('input, select, textarea, label, button')) {
                    return;
                }

                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    e.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
        },

        addSwipeGestures() {
            let startX, startY, endX, endY;

            document.addEventListener('touchstart', function (e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            });

            document.addEventListener('touchend', function (e) {
                if (!startX || !startY) return;

                endX = e.changedTouches[0].clientX;
                endY = e.changedTouches[0].clientY;

                const diffX = startX - endX;
                const diffY = startY - endY;

                // Horizontal swipe detection
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        // Swipe left - could close sidebar
                        if (window.innerWidth < 768) {
                            const sidebar = document.querySelector('.main-sidebar');
                            if (sidebar && sidebar.classList.contains('sidebar-open')) {
                                document.querySelector('[data-widget="pushmenu"]')?.click();
                            }
                        }
                    } else {
                        // Swipe right - could open sidebar
                        if (window.innerWidth < 768) {
                            const sidebar = document.querySelector('.main-sidebar');
                            if (sidebar && !sidebar.classList.contains('sidebar-open')) {
                                document.querySelector('[data-widget="pushmenu"]')?.click();
                            }
                        }
                    }
                }

                startX = startY = endX = endY = null;
            });
        }
    };

    // Enhanced Mobile Sidebar Optimizer for 400x926 and other resolutions
    const MobileSidebarOptimizer = {
        init() {
            this.optimizeSidebar();
            this.addSidebarGestures();
            this.handleOrientationChange();
            this.addKeyboardSupport();
            this.monitorSidebarState();
        },

        optimizeSidebar() {
            const viewport = {
                width: window.innerWidth,
                height: window.innerHeight
            };

            // Specific handling for 400x926 resolution
            if (viewport.width === 400 && viewport.height === 926) {
                document.body.classList.add('mobile-sidebar-400x926');
                this.optimize400x926();
            } else if (viewport.width <= 400) {
                document.body.classList.add('mobile-sidebar-mode');
            }

            this.addOverlayHandler();
            this.ensureProperZIndex();
        },

        optimize400x926() {
            console.log('Optimizing for 400x926 resolution');

            // Ensure proper AdminLTE classes
            const body = document.body;
            if (!body.classList.contains('sidebar-mini')) {
                body.classList.add('sidebar-mini');
            }
            if (!body.classList.contains('layout-fixed')) {
                body.classList.add('layout-fixed');
            }

            // Add specific CSS class for 400x926
            body.classList.add('resolution-400x926');

            // Optimize sidebar width for this resolution
            const sidebar = document.querySelector('.main-sidebar');
            if (sidebar) {
                sidebar.style.setProperty('--sidebar-width', '280px');
            }

            // Ensure content wrapper behaves correctly
            const contentWrapper = document.querySelector('.content-wrapper');
            if (contentWrapper) {
                contentWrapper.style.marginLeft = '0';
                contentWrapper.style.width = '100%';
            }

            // Add debugging info
            this.logSidebarState('400x926 optimization applied');
        },

        addOverlayHandler() {
            // Remove existing overlay handler to prevent duplicates
            document.removeEventListener('click', this.overlayClickHandler);

            // Add new overlay handler
            this.overlayClickHandler = (e) => {
                if (document.body.classList.contains('sidebar-open')) {
                    const sidebar = document.querySelector('.main-sidebar');
                    const toggleBtn = document.querySelector('[data-widget="pushmenu"]');

                    // Check if click is outside sidebar and toggle button
                    if (sidebar && !sidebar.contains(e.target) &&
                        toggleBtn && !toggleBtn.contains(e.target) &&
                        !e.target.closest('.main-sidebar') &&
                        !e.target.closest('[data-widget="pushmenu"]')) {

                        // Close sidebar
                        toggleBtn.click();
                        this.logSidebarState('Sidebar closed via overlay click');
                    }
                }
            };

            document.addEventListener('click', this.overlayClickHandler);
        },

        addSidebarGestures() {
            let startX = 0;
            let startY = 0;
            let isGesturing = false;

            document.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isGesturing = false;
            });

            document.addEventListener('touchmove', (e) => {
                if (!isGesturing) {
                    const currentX = e.touches[0].clientX;
                    const diffX = currentX - startX;

                    // Start gesture if moving horizontally from left edge
                    if (startX < 50 && Math.abs(diffX) > 10) {
                        isGesturing = true;
                    }
                }
            });

            document.addEventListener('touchend', (e) => {
                if (!isGesturing) return;

                const endX = e.changedTouches[0].clientX;
                const endY = e.changedTouches[0].clientY;
                const diffX = endX - startX;
                const diffY = endY - startY;

                // Only process horizontal swipes
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    const toggleBtn = document.querySelector('[data-widget="pushmenu"]');

                    if (diffX > 0 && startX < 50) {
                        // Swipe right from left edge - open sidebar
                        if (!document.body.classList.contains('sidebar-open') && toggleBtn) {
                            toggleBtn.click();
                            this.logSidebarState('Sidebar opened via swipe gesture');
                        }
                    } else if (diffX < 0 && document.body.classList.contains('sidebar-open')) {
                        // Swipe left - close sidebar
                        if (toggleBtn) {
                            toggleBtn.click();
                            this.logSidebarState('Sidebar closed via swipe gesture');
                        }
                    }
                }

                isGesturing = false;
            });
        },

        addKeyboardSupport() {
            // ESC key to close sidebar
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
                    const toggleBtn = document.querySelector('[data-widget="pushmenu"]');
                    if (toggleBtn) {
                        toggleBtn.click();
                        this.logSidebarState('Sidebar closed via ESC key');
                    }
                }
            });

            // Alt+M to toggle sidebar
            document.addEventListener('keydown', (e) => {
                if (e.altKey && e.key === 'm') {
                    e.preventDefault();
                    const toggleBtn = document.querySelector('[data-widget="pushmenu"]');
                    if (toggleBtn) {
                        toggleBtn.click();
                        this.logSidebarState('Sidebar toggled via Alt+M');
                    }
                }
            });
        },

        ensureProperZIndex() {
            // Ensure proper z-index layering
            const sidebar = document.querySelector('.main-sidebar');
            const navbar = document.querySelector('.main-header');

            if (sidebar) {
                sidebar.style.zIndex = '1040';
            }
            if (navbar) {
                navbar.style.zIndex = '1041';
            }
        },

        monitorSidebarState() {
            // Monitor sidebar state changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const target = mutation.target;
                        if (target === document.body) {
                            const isOpen = target.classList.contains('sidebar-open');
                            this.handleSidebarStateChange(isOpen);
                        }
                    }
                });
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });
        },

        handleSidebarStateChange(isOpen) {
            // Handle sidebar state changes
            if (isOpen) {
                // Sidebar opened
                document.body.style.overflow = 'hidden'; // Prevent body scroll
                this.logSidebarState('Sidebar opened - body scroll disabled');
            } else {
                // Sidebar closed
                document.body.style.overflow = ''; // Restore body scroll
                this.logSidebarState('Sidebar closed - body scroll restored');
            }

            // Dispatch custom event
            const event = new CustomEvent('sidebarToggle', {
                detail: { isOpen: isOpen }
            });
            document.dispatchEvent(event);
        },

        handleOrientationChange() {
            window.addEventListener('orientationchange', () => {
                setTimeout(() => {
                    this.optimizeSidebar();
                    this.logSidebarState('Orientation changed - sidebar re-optimized');
                }, 300);
            });

            // Also handle resize events
            window.addEventListener('resize', () => {
                clearTimeout(this.resizeTimer);
                this.resizeTimer = setTimeout(() => {
                    this.optimizeSidebar();
                }, 300);
            });
        },

        logSidebarState(action) {
            if (console && console.log) {
                const viewport = `${window.innerWidth}x${window.innerHeight}`;
                const isOpen = document.body.classList.contains('sidebar-open');
                const classes = Array.from(document.body.classList).join(' ');

                console.log(`[MobileSidebar] ${action}`, {
                    viewport: viewport,
                    isOpen: isOpen,
                    bodyClasses: classes,
                    timestamp: new Date().toISOString()
                });
            }
        },

        // Public methods
        getState() {
            return {
                isOpen: document.body.classList.contains('sidebar-open'),
                viewport: { width: window.innerWidth, height: window.innerHeight },
                is400x926: window.innerWidth === 400 && window.innerHeight === 926,
                bodyClasses: Array.from(document.body.classList)
            };
        },

        closeSidebar() {
            if (document.body.classList.contains('sidebar-open')) {
                const toggleBtn = document.querySelector('[data-widget="pushmenu"]');
                if (toggleBtn) {
                    toggleBtn.click();
                    this.logSidebarState('Sidebar force closed');
                }
            }
        },

        openSidebar() {
            if (!document.body.classList.contains('sidebar-open')) {
                const toggleBtn = document.querySelector('[data-widget="pushmenu"]');
                if (toggleBtn) {
                    toggleBtn.click();
                    this.logSidebarState('Sidebar force opened');
                }
            }
        }
    };

    // Mobile Table Enhancements
    const MobileTableOptimizer = {
        init() {
            this.optimizeTables();
            this.addTableControls();
            this.enableTableStacking();
        },

        optimizeTables() {
            const tables = document.querySelectorAll('.table-responsive');
            tables.forEach(tableContainer => {
                const table = tableContainer.querySelector('table');
                if (!table) return;

                // Add mobile-friendly classes
                table.classList.add('mobile-optimized');

                // Optimize button groups in tables
                const buttonGroups = table.querySelectorAll('td:last-child');
                buttonGroups.forEach(cell => {
                    const buttons = cell.querySelectorAll('.btn');
                    if (buttons.length > 2) {
                        cell.classList.add('mobile-button-stack');
                    }
                });
            });
        },

        addTableControls() {
            if (window.innerWidth >= 768) return;

            const tables = document.querySelectorAll('.table-responsive');
            tables.forEach(tableContainer => {
                // Add scroll indicators
                const scrollIndicator = document.createElement('div');
                scrollIndicator.className = 'mobile-scroll-indicator';
                scrollIndicator.innerHTML = '<i class="fas fa-arrows-alt-h"></i> Geser untuk melihat lebih banyak';
                tableContainer.parentNode.insertBefore(scrollIndicator, tableContainer);

                // Add scroll position indicator
                tableContainer.addEventListener('scroll', function () {
                    const scrollPercentage = (this.scrollLeft / (this.scrollWidth - this.clientWidth)) * 100;
                    scrollIndicator.style.opacity = scrollPercentage > 5 ? '0.5' : '1';
                });
            });
        },

        enableTableStacking() {
            if (window.innerWidth >= 576) return;

            // Add toggle for table stacking
            const tables = document.querySelectorAll('.table-responsive table');
            tables.forEach(table => {
                const toggleButton = document.createElement('button');
                toggleButton.className = 'btn btn-sm btn-outline-primary mobile-table-toggle mb-2';
                toggleButton.innerHTML = '<i class="fas fa-list"></i> Mode Kartu';
                toggleButton.onclick = () => this.toggleTableMode(table, toggleButton);

                table.parentNode.parentNode.insertBefore(toggleButton, table.parentNode);
            });
        },

        toggleTableMode(table, button) {
            table.classList.toggle('table-stack-mobile');
            const isStacked = table.classList.contains('table-stack-mobile');
            button.innerHTML = isStacked ?
                '<i class="fas fa-table"></i> Mode Tabel' :
                '<i class="fas fa-list"></i> Mode Kartu';
        }
    };

    // Performance Optimizations
    const PerformanceOptimizer = {
        init() {
            this.lazyLoadImages();
            this.optimizeAnimations();
            this.addLoadingStates();
            this.cacheResources();
        },

        lazyLoadImages() {
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        },

        optimizeAnimations() {
            // Reduce animations on low-end devices
            if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
                document.documentElement.style.setProperty('--animation-duration', '0.1s');
            }

            // Respect user's motion preferences
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.style.setProperty('--animation-duration', '0s');
            }
        },

        addLoadingStates() {
            // Add loading states for form submissions
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function () {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    }
                });
            });
        },

        cacheResources() {
            // Preload critical resources with correct BASE_URL
            // Find a local stylesheet (not from CDN) to extract base URL
            const stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
            let baseUrl = '';

            // Try to find a local stylesheet to get the base URL
            for (const sheet of stylesheets) {
                const href = sheet.href || '';
                // Skip CDN URLs and only use local URLs
                if (href.includes('localhost') || href.includes(window.location.hostname)) {
                    const match = href.match(/^(https?:\/\/[^\/]+\/[^\/]+)/);
                    if (match) {
                        baseUrl = match[1];
                        break;
                    }
                }
            }

            // Fallback: construct from window.location if no local stylesheet found
            if (!baseUrl) {
                const pathParts = window.location.pathname.split('/');
                // Get the first path segment (e.g., 'jagapadi')
                const appPath = pathParts[1] || '';
                if (appPath) {
                    baseUrl = window.location.origin + '/' + appPath;
                } else {
                    // Can't determine base URL, skip preloading
                    console.log('[MobileEnhancements] Could not determine base URL, skipping resource preload');
                    return;
                }
            }

            const criticalResources = [
                `${baseUrl}/public/css/responsive.css`,
                `${baseUrl}/public/js/mobile-enhancements.js`
            ];

            criticalResources.forEach(resource => {
                // Check if resource exists before preloading
                fetch(resource, { method: 'HEAD' })
                    .then(response => {
                        if (response.ok) {
                            const link = document.createElement('link');
                            link.rel = 'prefetch';
                            link.href = resource;
                            document.head.appendChild(link);
                        }
                    })
                    .catch(() => {
                        // Silently ignore preload errors - these are optimization only
                    });
            });
        }
    };

    // Mobile Form Enhancements
    const MobileFormOptimizer = {
        init() {
            this.optimizeInputs();
            this.addVirtualKeyboardSupport();
            this.improveValidation();
        },

        optimizeInputs() {
            // Optimize input types for mobile keyboards
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                // Add appropriate input modes
                if (input.type === 'email') {
                    input.inputMode = 'email';
                } else if (input.type === 'tel') {
                    input.inputMode = 'tel';
                } else if (input.type === 'number') {
                    input.inputMode = 'numeric';
                }

                // Add autocomplete attributes
                if (input.name === 'email') {
                    input.autocomplete = 'email';
                } else if (input.name === 'phone') {
                    input.autocomplete = 'tel';
                }
            });
        },

        addVirtualKeyboardSupport() {
            // Handle virtual keyboard appearance
            if (MobileDetector.isMobile) {
                const viewport = document.querySelector('meta[name="viewport"]');
                let originalContent = viewport.content;

                document.addEventListener('focusin', function (e) {
                    if (e.target.matches('input, textarea, select')) {
                        // Adjust viewport when keyboard appears
                        viewport.content = originalContent + ', user-scalable=yes';

                        // Scroll element into view
                        setTimeout(() => {
                            e.target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 300);
                    }
                });

                document.addEventListener('focusout', function () {
                    // Restore original viewport
                    viewport.content = originalContent;
                });
            }
        },

        improveValidation() {
            // Add real-time validation feedback
            const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function () {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });

                input.addEventListener('input', function () {
                    if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
            });
        }
    };

    // Accessibility Enhancements
    const AccessibilityEnhancer = {
        init() {
            this.addSkipLinks();
            this.improveFocusManagement();
            this.addARIALabels();
        },

        addSkipLinks() {
            const skipLink = document.createElement('a');
            skipLink.href = '#main-content';
            skipLink.className = 'skip-link sr-only sr-only-focusable';
            skipLink.textContent = 'Skip to main content';
            document.body.insertBefore(skipLink, document.body.firstChild);

            // Add main content landmark
            const contentWrapper = document.querySelector('.content-wrapper');
            if (contentWrapper) {
                contentWrapper.id = 'main-content';
                contentWrapper.setAttribute('role', 'main');
            }
        },

        improveFocusManagement() {
            // Improve focus visibility
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Tab') {
                    document.body.classList.add('keyboard-navigation');
                }
            });

            document.addEventListener('mousedown', function () {
                document.body.classList.remove('keyboard-navigation');
            });
        },

        addARIALabels() {
            // Add ARIA labels to buttons without text
            const iconButtons = document.querySelectorAll('button:not([aria-label]) i.fas');
            iconButtons.forEach(icon => {
                const button = icon.closest('button');
                if (button && !button.textContent.trim()) {
                    const iconClass = Array.from(icon.classList).find(cls => cls.startsWith('fa-'));
                    if (iconClass) {
                        const label = this.getARIALabelForIcon(iconClass);
                        button.setAttribute('aria-label', label);
                    }
                }
            });
        },

        getARIALabelForIcon(iconClass) {
            const iconLabels = {
                'fa-eye': 'Lihat detail',
                'fa-edit': 'Edit',
                'fa-trash': 'Hapus',
                'fa-check': 'Verifikasi',
                'fa-times': 'Tolak',
                'fa-plus': 'Tambah',
                'fa-download': 'Unduh',
                'fa-upload': 'Unggah',
                'fa-search': 'Cari',
                'fa-filter': 'Filter',
                'fa-bars': 'Menu'
            };
            return iconLabels[iconClass] || 'Aksi';
        }
    };

    // Progressive Web App Features
    const PWAEnhancer = {
        init() {
            this.addInstallPrompt();
            this.handleOfflineState();
            this.addPullToRefresh();
        },

        addInstallPrompt() {
            let deferredPrompt;

            window.addEventListener('beforeinstallprompt', (e) => {
                // Don't prevent default immediately - let the browser handle it first
                deferredPrompt = e;

                // Show install button after a delay
                setTimeout(() => {
                    e.preventDefault();
                    const installBtn = document.createElement('button');
                    installBtn.className = 'btn btn-success btn-sm position-fixed install-app-btn';
                    installBtn.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; border-radius: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
                    installBtn.innerHTML = '<i class="fas fa-mobile-alt"></i> Install JAGAPADI';
                    installBtn.onclick = () => this.showInstallPrompt(deferredPrompt, installBtn);
                    document.body.appendChild(installBtn);
                }, 3000); // Show after 3 seconds
            });
        },

        showInstallPrompt(deferredPrompt, installBtn) {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        installBtn.remove();
                    }
                    deferredPrompt = null;
                });
            }
        },

        handleOfflineState() {
            window.addEventListener('online', () => {
                this.showConnectionStatus('Koneksi tersambung', 'success');
            });

            window.addEventListener('offline', () => {
                this.showConnectionStatus('Tidak ada koneksi internet', 'warning');
            });
        },

        showConnectionStatus(message, type) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 250px;';
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'wifi' : 'exclamation-triangle'}"></i> ${message}`;
            document.body.appendChild(toast);

            setTimeout(() => toast.remove(), 3000);
        },

        addPullToRefresh() {
            if (!MobileDetector.isMobile) return;

            let startY = 0;
            let currentY = 0;
            let pullDistance = 0;
            const threshold = 100;

            document.addEventListener('touchstart', (e) => {
                if (window.scrollY === 0) {
                    startY = e.touches[0].clientY;
                }
            });

            document.addEventListener('touchmove', (e) => {
                if (startY && window.scrollY === 0) {
                    currentY = e.touches[0].clientY;
                    pullDistance = currentY - startY;

                    if (pullDistance > 0) {
                        e.preventDefault();
                        // Visual feedback for pull to refresh
                        document.body.style.transform = `translateY(${Math.min(pullDistance / 3, 50)}px)`;
                    }
                }
            });

            document.addEventListener('touchend', () => {
                if (pullDistance > threshold) {
                    // Trigger refresh
                    window.location.reload();
                }

                // Reset
                document.body.style.transform = '';
                startY = currentY = pullDistance = 0;
            });
        }
    };

    // Enhanced Sidebar Overlay Manager
    const SidebarOverlayManager = {
        overlay: null,

        init() {
            this.createOverlay();
            this.attachEventListeners();
        },

        createOverlay() {
            // Check if overlay already exists
            if (document.getElementById('sidebar-overlay')) return;

            this.overlay = document.createElement('div');
            this.overlay.id = 'sidebar-overlay';
            this.overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1035;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
                pointer-events: none;
            `;
            document.body.appendChild(this.overlay);
        },

        attachEventListeners() {
            // Listen for sidebar state changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        this.handleSidebarChange();
                    }
                });
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });

            // Click on overlay to close sidebar
            if (this.overlay) {
                this.overlay.addEventListener('click', () => {
                    this.closeSidebar();
                });
            }
        },

        handleSidebarChange() {
            const isOpen = document.body.classList.contains('sidebar-open');

            if (this.overlay) {
                if (isOpen && window.innerWidth < 992) {
                    this.overlay.style.opacity = '1';
                    this.overlay.style.visibility = 'visible';
                    this.overlay.style.pointerEvents = 'auto';
                } else {
                    this.overlay.style.opacity = '0';
                    this.overlay.style.visibility = 'hidden';
                    this.overlay.style.pointerEvents = 'none';
                }
            }
        },

        closeSidebar() {
            const toggleBtn = document.querySelector('[data-widget="pushmenu"]');
            if (toggleBtn && document.body.classList.contains('sidebar-open')) {
                toggleBtn.click();
            }
        }
    };

    // Table Scroll Hint Manager
    const TableScrollHintManager = {
        init() {
            this.updateHintVisibility();
            this.attachScrollListeners();
        },

        updateHintVisibility() {
            const hints = document.querySelectorAll('.table-scroll-hint');
            hints.forEach(hint => {
                const tableContainer = hint.nextElementSibling;
                if (tableContainer && tableContainer.classList.contains('table-responsive')) {
                    // Check if table needs scrolling
                    const table = tableContainer.querySelector('table');
                    if (table && table.offsetWidth <= tableContainer.offsetWidth) {
                        hint.style.display = 'none';
                    }
                }
            });
        },

        attachScrollListeners() {
            const tableContainers = document.querySelectorAll('.table-responsive');
            tableContainers.forEach(container => {
                container.addEventListener('scroll', () => {
                    const hint = container.previousElementSibling;
                    if (hint && hint.classList.contains('table-scroll-hint')) {
                        // Fade out hint after user scrolls
                        if (container.scrollLeft > 10) {
                            hint.style.opacity = '0.5';
                        } else {
                            hint.style.opacity = '1';
                        }
                    }
                });
            });
        }
    };

    // Mobile Sidebar Fallback for POCO 7 and similar devices
    const MobileSidebarFallback = {
        init() {
            if (window.innerWidth <= 767) {
                this.addFallbackHandler();
                this.addOverlayClickHandler();
                this.ensureSidebarClasses();
            }

            window.addEventListener('resize', () => {
                if (window.innerWidth <= 767) {
                    this.ensureSidebarClasses();
                }
            });
        },

        addFallbackHandler() {
            const burgerBtn = document.querySelector('[data-widget="pushmenu"]');
            if (!burgerBtn) {
                console.log('[MobileSidebarFallback] Burger button not found');
                return;
            }

            // Add our own click handler as fallback
            burgerBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Toggle sidebar-open class on body
                document.body.classList.toggle('sidebar-open');

                // Also toggle sidebar-collapse for AdminLTE compatibility
                if (document.body.classList.contains('sidebar-open')) {
                    document.body.classList.remove('sidebar-collapse');
                } else {
                    document.body.classList.add('sidebar-collapse');
                }

                console.log('[MobileSidebarFallback] Sidebar toggled:',
                    document.body.classList.contains('sidebar-open') ? 'open' : 'closed');
            });

            // Also add touch event for better mobile response
            burgerBtn.addEventListener('touchend', (e) => {
                // Let the click handler handle it
            }, { passive: true });

            console.log('[MobileSidebarFallback] Fallback handler attached');
        },

        addOverlayClickHandler() {
            // Close sidebar when clicking outside
            document.addEventListener('click', (e) => {
                if (window.innerWidth > 767) return;
                if (!document.body.classList.contains('sidebar-open')) return;

                const sidebar = document.querySelector('.main-sidebar');
                const burgerBtn = document.querySelector('[data-widget="pushmenu"]');

                // Check if click is outside sidebar and burger button
                if (sidebar && !sidebar.contains(e.target) &&
                    burgerBtn && !burgerBtn.contains(e.target)) {

                    document.body.classList.remove('sidebar-open');
                    document.body.classList.add('sidebar-collapse');
                    console.log('[MobileSidebarFallback] Sidebar closed via outside click');
                }
            });
        },

        ensureSidebarClasses() {
            // Ensure proper initial state on mobile
            if (window.innerWidth <= 767) {
                // Make sure sidebar is closed by default on mobile
                if (!document.body.classList.contains('sidebar-open')) {
                    document.body.classList.add('sidebar-collapse');
                }

                // Ensure sidebar is positioned correctly
                const sidebar = document.querySelector('.main-sidebar');
                if (sidebar) {
                    sidebar.style.position = 'fixed';
                    sidebar.style.top = '0';
                    sidebar.style.left = '0';
                    sidebar.style.zIndex = '1050';
                }
            }
        },

        // Public method to manually open sidebar
        open() {
            document.body.classList.add('sidebar-open');
            document.body.classList.remove('sidebar-collapse');
        },

        // Public method to manually close sidebar
        close() {
            document.body.classList.remove('sidebar-open');
            document.body.classList.add('sidebar-collapse');
        },

        // Public method to toggle sidebar
        toggle() {
            document.body.classList.toggle('sidebar-open');
            if (document.body.classList.contains('sidebar-open')) {
                document.body.classList.remove('sidebar-collapse');
            } else {
                document.body.classList.add('sidebar-collapse');
            }
        }
    };

    // Initialize all enhancements when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Initializing mobile enhancements...', MobileDetector.getDeviceInfo());

        TouchEnhancements.init();
        MobileSidebarOptimizer.init();
        MobileTableOptimizer.init();
        PerformanceOptimizer.init();
        MobileFormOptimizer.init();
        AccessibilityEnhancer.init();
        PWAEnhancer.init();

        // New enhanced managers
        SidebarOverlayManager.init();
        TableScrollHintManager.init();

        // POCO 7 and similar device fallback
        MobileSidebarFallback.init();

        console.log('Mobile enhancements initialized successfully');
    });

    // Expose utilities globally
    window.JAGAPADI_Mobile = {
        detector: MobileDetector,
        touch: TouchEnhancements,
        sidebar: MobileSidebarOptimizer,
        table: MobileTableOptimizer,
        performance: PerformanceOptimizer,
        forms: MobileFormOptimizer,
        accessibility: AccessibilityEnhancer,
        pwa: PWAEnhancer,
        overlay: SidebarOverlayManager,
        scrollHint: TableScrollHintManager,
        sidebarFallback: MobileSidebarFallback
    };

})();