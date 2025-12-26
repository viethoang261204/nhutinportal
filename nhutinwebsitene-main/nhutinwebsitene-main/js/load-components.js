// Load Navbar and Footer Components using jQuery
(function($) {
    'use strict';
    
    // Set active nav link based on current page
    function setActiveNavLink() {
        var currentPage = window.location.pathname.split('/').pop() || 'index.html';
        $('.navbar-nav .nav-link').each(function() {
            $(this).removeClass('active');
            if ($(this).attr('href') === currentPage) {
                $(this).addClass('active');
            }
        });
    }
    
    // Re-apply language after components loaded
    function reapplyLanguage() {
        var currentLang = localStorage.getItem('language') || 'vi';
        
        $('[data-vi][data-en]').each(function() {
            var text = $(this).attr('data-' + currentLang);
            
            if ($(this).attr('placeholder')) {
                $(this).attr('placeholder', text);
            } else if ($(this).is('option')) {
                $(this).text(text);
            } else if ($(this).is('button, a')) {
                var icon = $(this).find('i');
                if (icon.length) {
                    $(this).text(text).append(icon);
                } else {
                    $(this).text(text);
                }
            } else {
                $(this).text(text);
            }
        });
        
        // Update language button
        var langBtn = $('#langToggle');
        if (langBtn.length) {
            langBtn.html(currentLang === 'vi' ? '🇬🇧 EN' : '🇻🇳 VI');
            
            // Re-attach click event
            langBtn.off('click').on('click', function() {
                var newLang = currentLang === 'vi' ? 'en' : 'vi';
                localStorage.setItem('language', newLang);
                location.reload();
            });
        }
    }
    
    // Load components when DOM is ready
    $(document).ready(function() {
        var componentsLoaded = 0;
        var totalComponents = 2;
        
        function checkAllLoaded() {
            componentsLoaded++;
            if (componentsLoaded >= totalComponents) {
                setActiveNavLink();
                reapplyLanguage();
                
                // Re-initialize WOW.js for footer animations
                if (typeof WOW !== 'undefined') {
                    new WOW().init();
                }
                
                // Fixed navbar - thêm shadow khi scroll (giống thungxetudo)
                if (typeof jQuery !== 'undefined') {
                    $(window).on('scroll', function () {
                        if ($(this).scrollTop() > 100) {
                            $('.fixed-top').addClass('shadow-sm');
                        } else {
                            $('.fixed-top').removeClass('shadow-sm');
                        }
                    });
                }
            }
        }
        
        // Load navbar using jQuery
        if ($('#navbar-placeholder').length) {
            $('#navbar-placeholder').load('components/navbar.html', function(response, status) {
                if (status === 'error') {
                    console.error('Error loading navbar');
                }
                checkAllLoaded();
            });
        } else {
            componentsLoaded++;
        }
        
        // Load footer using jQuery
        if ($('#footer-placeholder').length) {
            $('#footer-placeholder').load('components/footer.html', function(response, status) {
                if (status === 'error') {
                    console.error('Error loading footer');
                }
                checkAllLoaded();
            });
        } else {
            componentsLoaded++;
        }
    });
})(jQuery);

