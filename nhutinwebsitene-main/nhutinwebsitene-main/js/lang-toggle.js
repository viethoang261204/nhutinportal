// Language Toggle for i18n
(function() {
    'use strict';
    
    // Wait for DOM and i18n to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const langBtn = document.getElementById('langToggle');
        
        if (langBtn && window.i18n) {
            // Add click event to toggle button
            langBtn.addEventListener('click', function() {
                const currentLang = window.i18n.getCurrentLang();
                const newLang = currentLang === 'vi' ? 'en' : 'vi';
                window.i18n.switchLanguage(newLang);
            });
        }
    });
})();
