/**
 * Mobile Navigation Drawer
 * Handles hamburger menu toggle and mobile navigation
 */

(function() {
    'use strict';

    // DOM elements
    let mobileMenuBtn = null;
    let mobileNavOverlay = null;
    let mobileNavDrawer = null;
    let mobileNavClose = null;

    /**
     * Initialize mobile navigation
     */
    function init() {
        // Get DOM elements
        mobileMenuBtn = document.getElementById('mobileMenuBtn');
        mobileNavOverlay = document.getElementById('mobileNavOverlay');
        mobileNavDrawer = document.getElementById('mobileNavDrawer');
        mobileNavClose = document.getElementById('mobileNavClose');

        if (!mobileMenuBtn || !mobileNavOverlay || !mobileNavDrawer) {
            // console.warn('Mobile navigation elements not found');
            return;
        }

        // Add event listeners
        mobileMenuBtn.addEventListener('click', toggleMobileNav);
        mobileNavOverlay.addEventListener('click', closeMobileNav);

        if (mobileNavClose) {
            mobileNavClose.addEventListener('click', closeMobileNav);
        }

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isNavOpen()) {
                closeMobileNav();
            }
        });

        // Close on navigation link click
        const navLinks = mobileNavDrawer.querySelectorAll('.mobile-nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Small delay to allow navigation to start
                setTimeout(closeMobileNav, 150);
            });
        });
    }

    /**
     * Toggle mobile navigation open/closed
     */
    function toggleMobileNav() {
        if (isNavOpen()) {
            closeMobileNav();
        } else {
            openMobileNav();
        }
    }

    /**
     * Open mobile navigation
     */
    function openMobileNav() {
        mobileMenuBtn.classList.add('active');
        mobileNavOverlay.classList.add('active');
        mobileNavDrawer.classList.add('active');
        document.body.classList.add('mobile-nav-open');

        // Trap focus within drawer
        trapFocus(mobileNavDrawer);
    }

    /**
     * Close mobile navigation
     */
    function closeMobileNav() {
        mobileMenuBtn.classList.remove('active');
        mobileNavOverlay.classList.remove('active');
        mobileNavDrawer.classList.remove('active');
        document.body.classList.remove('mobile-nav-open');

        // Return focus to menu button
        mobileMenuBtn.focus();
    }

    /**
     * Check if navigation is open
     */
    function isNavOpen() {
        return mobileNavDrawer.classList.contains('active');
    }

    /**
     * Trap focus within an element (accessibility)
     */
    function trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        // Focus first element
        firstFocusable.focus();

        // Handle tab key
        element.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) { // Shift + Tab
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else { // Tab
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });
    }

    /**
     * Handle window resize - close menu if desktop
     */
    function handleResize() {
        if (window.innerWidth > 768 && isNavOpen()) {
            closeMobileNav();
        }
    }

    // Debounce resize handler
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 250);
    });

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
