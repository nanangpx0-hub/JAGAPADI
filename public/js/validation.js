/**
 * Client-side Validation Library
 * Provides real-time form validation with user-friendly feedback
 */
(function() {
    'use strict';
    
    const Validation = {
        rules: {
            required: function(value) {
                return value !== null && value !== undefined && String(value).trim() !== '';
            },
            email: function(value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(value);
            },
            minLength: function(value, min) {
                return String(value).length >= min;
            },
            maxLength: function(value, max) {
                return String(value).length <= max;
            },
            numeric: function(value) {
                return !isNaN(value) && !isNaN(parseFloat(value));
            },
            integer: function(value) {
                return Number.isInteger(Number(value));
            },
            min: function(value, min) {
                return Number(value) >= min;
            },
            max: function(value, max) {
                return Number(value) <= max;
            },
            url: function(value) {
                try {
                    new URL(value);
                    return true;
                } catch {
                    return false;
                }
            },
            date: function(value) {
                return !isNaN(Date.parse(value));
            },
            fileSize: function(file, maxSizeMB) {
                if (!file) return true;
                return file.size <= maxSizeMB * 1024 * 1024;
            },
            fileType: function(file, allowedTypes) {
                if (!file) return true;
                const types = Array.isArray(allowedTypes) ? allowedTypes : [allowedTypes];
                return types.some(type => file.type.includes(type));
            }
        },
        
        messages: {
            required: 'Field ini wajib diisi',
            email: 'Format email tidak valid',
            minLength: 'Minimal {min} karakter',
            maxLength: 'Maksimal {max} karakter',
            numeric: 'Harus berupa angka',
            integer: 'Harus berupa bilangan bulat',
            min: 'Minimal {min}',
            max: 'Maksimal {max}',
            url: 'Format URL tidak valid',
            date: 'Format tanggal tidak valid',
            fileSize: 'Ukuran file maksimal {max}MB',
            fileType: 'Tipe file tidak diizinkan'
        },
        
        /**
         * Validate field
         */
        validateField: function(field, rules) {
            const value = field.value;
            const errors = [];
            
            for (const rule in rules) {
                if (rules.hasOwnProperty(rule)) {
                    const ruleValue = rules[rule];
                    let isValid = true;
                    let message = '';
                    
                    if (rule === 'required' && !this.rules.required(value)) {
                        isValid = false;
                        message = this.messages.required;
                    } else if (rule === 'email' && value && !this.rules.email(value)) {
                        isValid = false;
                        message = this.messages.email;
                    } else if (rule === 'minLength' && value && !this.rules.minLength(value, ruleValue)) {
                        isValid = false;
                        message = this.messages.minLength.replace('{min}', ruleValue);
                    } else if (rule === 'maxLength' && value && !this.rules.maxLength(value, ruleValue)) {
                        isValid = false;
                        message = this.messages.maxLength.replace('{max}', ruleValue);
                    } else if (rule === 'numeric' && value && !this.rules.numeric(value)) {
                        isValid = false;
                        message = this.messages.numeric;
                    } else if (rule === 'integer' && value && !this.rules.integer(value)) {
                        isValid = false;
                        message = this.messages.integer;
                    } else if (rule === 'min' && value && !this.rules.min(value, ruleValue)) {
                        isValid = false;
                        message = this.messages.min.replace('{min}', ruleValue);
                    } else if (rule === 'max' && value && !this.rules.max(value, ruleValue)) {
                        isValid = false;
                        message = this.messages.max.replace('{max}', ruleValue);
                    }
                    
                    if (!isValid) {
                        errors.push(message);
                    }
                }
            }
            
            return {
                valid: errors.length === 0,
                errors: errors
            };
        },
        
        /**
         * Validate form
         */
        validateForm: function(form) {
            let isValid = true;
            const fields = form.querySelectorAll('[data-validate]');
            
            fields.forEach(field => {
                const rules = JSON.parse(field.getAttribute('data-validate') || '{}');
                const result = this.validateField(field, rules);
                
                if (!result.valid) {
                    isValid = false;
                    this.showError(field, result.errors[0]);
                } else {
                    this.clearError(field);
                }
            });
            
            return isValid;
        },
        
        /**
         * Show error message
         */
        showError: function(field, message) {
            this.clearError(field);
            
            field.classList.add('is-invalid');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        },
        
        /**
         * Clear error message
         */
        clearError: function(field) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        },
        
        /**
         * Initialize validation on form
         */
        init: function(form) {
            if (!form) return;
            
            const fields = form.querySelectorAll('[data-validate]');
            
            // Real-time validation on blur
            fields.forEach(field => {
                field.addEventListener('blur', () => {
                    const rules = JSON.parse(field.getAttribute('data-validate') || '{}');
                    const result = this.validateField(field, rules);
                    
                    if (!result.valid) {
                        this.showError(field, result.errors[0]);
                    } else {
                        this.clearError(field);
                    }
                });
                
                field.addEventListener('input', () => {
                    if (field.classList.contains('is-invalid')) {
                        const rules = JSON.parse(field.getAttribute('data-validate') || '{}');
                        const result = this.validateField(field, rules);
                        
                        if (result.valid) {
                            this.clearError(field);
                        }
                    }
                });
            });
            
            // Form submission validation
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Focus on first invalid field
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                } else {
                    form.classList.add('was-validated');
                }
            });
        }
    };
    
    // Auto-initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form[data-validate-form]');
        forms.forEach(form => {
            Validation.init(form);
        });
    });
    
    // Export to global scope
    window.Validation = Validation;
})();

