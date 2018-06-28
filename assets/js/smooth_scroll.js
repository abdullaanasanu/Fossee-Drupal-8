(function($) {
    $(document).ready(function() {
        $('a[href*=#]:not([href=#])').click(function() {
            if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') 
                || location.hostname == this.hostname) {
                
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
                   if (target.length) {
                     $('html,body').animate({
                         scrollTop: target.offset().top
                    }, 1000);
                    return false;
                }
            }
        });
        $(".quick").click(function() {
            $(".quick").removeClass("badge-success");
            $(this).addClass("badge-success");
            setTimeout(function() {
                $(".quick").removeClass("badge-success");
            }, 4000);
        });
        $('.quick').tooltip();
    });
})(jQuery);
