(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();

        // Navbar logo switch (white / colored)
    // Navbar logo switch (white / colored)
    var $navbar   = $('.navbar');
    var $collapse = $('#navbarCollapse');

    function updateNavbarLogoState() {
        if (!$navbar.length) return;

        var scrolled = $(window).scrollTop() > 45; // نفس عتبة الـ sticky-top
        var menuOpen = $collapse.length && $collapse.hasClass('show');
        var isMobile = window.matchMedia("(max-width: 991.98px)").matches; // نفس الـ breakpoint في الـ CSS

        // ✅ في الموبايل دائماً نستخدم الشعار الملوّن
        // ✅ في الديسكتوب نستخدمه عند النزول أو فتح القائمة
        if (isMobile || scrolled || menuOpen) {
            $navbar.addClass('navbar-alt-logo');
        } else {
            $navbar.removeClass('navbar-alt-logo');
        }
    }




    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 45) {
            $('.navbar').addClass('sticky-top shadow-sm');
        } else {
            $('.navbar').removeClass('sticky-top shadow-sm');
        }

        // نحدّث حالة الشعار مع كل Scroll
        updateNavbarLogoState();
    });


    // Dropdown on mouse hover
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";

    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }

          updateNavbarLogoState();
    });


    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        margin: 25,
        dots: false,
        loop: true,
        center: true,
        responsive: {
            0:{
                items:1
            },
            576:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });


    // Portfolio isotope and filter
    var portfolioIsotope = $('.portfolio-container').isotope({
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows'
    });
    $('#portfolio-flters li').on('click', function () {
        $("#portfolio-flters li").removeClass('active');
        $(this).addClass('active');

        portfolioIsotope.isotope({filter: $(this).data('filter')});
    });

        // تحديث حالة الشعار عند فتح/إغلاق القائمة المنسدلة في الشاشات الصغيرة
    if ($collapse.length) {
        $collapse.on('shown.bs.collapse hidden.bs.collapse', function () {
            updateNavbarLogoState();
        });
    }

    // استدعاء مبدئي عند تحميل الصفحة
    updateNavbarLogoState();

})(jQuery);

