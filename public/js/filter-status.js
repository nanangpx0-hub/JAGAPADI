/**
 * Filter Status Enhancement - HOVER EFFECTS DISABLED
 * Untuk halaman laporan/index.php
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const filterButtons = document.querySelectorAll('.btn-filter');
    
    if (filterButtons.length === 0) {
        console.log('Filter buttons not found');
        return;
    }
    
    console.log('Filter Status Enhancement initialized with', filterButtons.length, 'buttons');
    
    filterButtons.forEach(function(button) {
        // Click handler untuk filter
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const filter = this.getAttribute('data-filter');
            
            // Hapus class active dari semua button
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-pressed', 'false');
            });
            
            // Tambah class active ke button yang diklik
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            
            // Update URL dengan filter parameter
            const url = new URL(window.location.href);
            if (filter === 'semua') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', filter);
            }
            
            // Redirect ke URL baru
            window.location.href = url.toString();
        });
        
        // MOUSE DOWN EFFECT DISABLED - No more hover effects
        // button.addEventListener('mousedown', function(e) {
        //     if (e.button !== 0) return;
        //     if (!this.classList.contains('active')) {
        //         this.style.transform = 'translateY(1px)';
        //         this.style.boxShadow = 'inset 0 2px 4px rgba(0, 0, 0, 0.15)';
        //     }
        // });
        
        // button.addEventListener('mouseup', function() {
        //     if (!this.classList.contains('active')) {
        //         this.style.transform = '';
        //         this.style.boxShadow = '';
        //     }
        // });
        
        // button.addEventListener('mouseleave', function() {
        //     if (!this.classList.contains('active')) {
        //         this.style.transform = '';
        //         this.style.boxShadow = '';
        //     }
        // });
        
        // TOUCH EVENTS DISABLED - No mobile hover effects
        // button.addEventListener('touchstart', function(e) {
        //     if (!this.classList.contains('active')) {
        //         this.style.transform = 'translateY(1px)';
        //         this.style.boxShadow = 'inset 0 2px 4px rgba(0, 0, 0, 0.15)';
        //     }
        // });
        
        // button.addEventListener('touchend', function() {
        //     if (!this.classList.contains('active')) {
        //         this.style.transform = '';
        //         this.style.boxShadow = '';
        //     }
        // });
        
        // button.addEventListener('touchcancel', function() {
        //     if (!this.classList.contains('active')) {
        //         this.style.transform = '';
        //         this.style.boxShadow = '';
        //     }
        // });
    });
    
    // Set initial active state berdasarkan URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const currentStatus = urlParams.get('status') || 'semua';
    
    filterButtons.forEach(function(button) {
        const buttonFilter = button.getAttribute('data-filter');
        if (buttonFilter === currentStatus) {
            button.classList.add('active');
            button.setAttribute('aria-pressed', 'true');
        }
    });
});
