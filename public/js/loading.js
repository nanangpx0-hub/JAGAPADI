/**
 * Loading Indicator Library
 * Provides loading indicators for async operations
 */
(function() {
    'use strict';
    
    const Loading = {
        /**
         * Show loading overlay
         */
        show: function(message = 'Memproses...') {
            // Remove existing overlay if any
            this.hide();
            
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div class="loading-message mt-3">${message}</div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        },
        
        /**
         * Hide loading overlay
         */
        hide: function() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.remove();
            }
            
            // Restore body scroll
            document.body.style.overflow = '';
        },
        
        /**
         * Show loading on button
         */
        showButton: function(button, text = null) {
            if (!button) return;
            
            button.disabled = true;
            button.dataset.originalText = button.textContent || button.innerHTML;
            
            if (text) {
                if (button.tagName === 'BUTTON') {
                    button.textContent = text;
                } else {
                    button.innerHTML = text;
                }
            } else {
                const spinner = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>';
                if (button.tagName === 'BUTTON') {
                    button.innerHTML = spinner + (button.dataset.originalText || 'Memproses...');
                } else {
                    button.innerHTML = spinner + (button.dataset.originalText || 'Memproses...');
                }
            }
        },
        
        /**
         * Hide loading on button
         */
        hideButton: function(button) {
            if (!button) return;
            
            button.disabled = false;
            
            if (button.dataset.originalText) {
                if (button.tagName === 'BUTTON') {
                    button.textContent = button.dataset.originalText;
                } else {
                    button.innerHTML = button.dataset.originalText;
                }
                delete button.dataset.originalText;
            }
        },
        
        /**
         * Wrap async function with loading indicator
         */
        wrap: async function(fn, message = 'Memproses...') {
            this.show(message);
            
            try {
                const result = await fn();
                return result;
            } finally {
                this.hide();
            }
        },
        
        /**
         * Show loading for AJAX request
         */
        ajax: function(options = {}) {
            const originalSuccess = options.success;
            const originalError = options.error;
            const originalComplete = options.complete;
            const message = options.loadingMessage || 'Memproses...';
            
            this.show(message);
            
            options.success = (data) => {
                this.hide();
                if (originalSuccess) originalSuccess(data);
            };
            
            options.error = (xhr, status, error) => {
                this.hide();
                if (originalError) originalError(xhr, status, error);
            };
            
            options.complete = () => {
                this.hide();
                if (originalComplete) originalComplete();
            };
            
            return $.ajax(options);
        }
    };
    
    // Auto-handle form submissions
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submissions
        document.addEventListener('submit', function(e) {
            const form = e.target;
            
            // Skip if form has data-no-loading attribute
            if (form.hasAttribute('data-no-loading')) {
                return;
            }
            
            // Show loading on submit button
            const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitButton) {
                Loading.showButton(submitButton);
            } else {
                Loading.show('Mengirim data...');
            }
        });
        
        // Handle AJAX links
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[data-ajax]');
            if (link) {
                e.preventDefault();
                Loading.show('Memuat...');
                
                // Perform AJAX request
                fetch(link.href, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Loading.hide();
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                })
                .catch(error => {
                    Loading.hide();
                    console.error('Error:', error);
                });
            }
        });
    });
    
    // Export to global scope
    window.Loading = Loading;
})();

