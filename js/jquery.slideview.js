(function($) {
    $.fn.slideSwipe = function(option)
    {
        var cfg = {
                slide_wrap: "sidx_slide",
                slide: "section",
                slide_tab: "slide_tab",
                tab_class: "slide_tab",
                slide_class: "sidx_slide",
                active_class: "slide_active",
                duration: 300
            };

        if(typeof option == "object")
            cfg = $.extend( cfg, option );

        var $this = this;
        var $wrap = this.find("#"+cfg.slide_wrap);
        var $tab = this.find("#"+cfg.slide_tab);
        var $slides = this.find(cfg.slide);

        $tab.addClass(cfg.tab_class);
        $slides.addClass(cfg.slide_class);

        var height;
        var width = $(window).width();
        var count = $slides.size();
        var idx = next = 0;

        function swipe_init()
        {
            idx = $slides.index($slides.filter("."+cfg.active_class));
            if(idx == -1)
                idx = 0;

            width = $(window).width();
            $slides.eq(idx).addClass(cfg.active_class);
            $slides.not("."+cfg.active_class).css("left", width+"px");

            height = $slides.eq(idx).height();
            $wrap.height(height);
        }

        function swipe_left()
        {
            if(check_animated())
                return;

            idx = $slides.index($slides.filter("."+cfg.active_class));
            next = (idx + 1) % count;

            $slides.not("."+cfg.active_class).css("left", width+"px");

            width = $(window).width();
            height = $slides.eq(next).height();
            $wrap.height(height);

            $slides.eq(idx).animate(
                { left: "-="+width }, cfg.duration,
                function() {
                    $slides.eq(idx).removeClass(cfg.active_class);
                }
            );

            $slides.eq(next).animate(
                { left: "-="+width }, cfg.duration,
                function() {
                    $slides.eq(next).addClass(cfg.active_class);
                }
            );
        }

        function swipe_right()
        {
            if(check_animated())
                return;

            idx = $slides.index($slides.filter("."+cfg.active_class));
            next = idx - 1

            $slides.not("."+cfg.active_class).css("left", "-"+width+"px");

            width = $(window).width();
            height = $slides.eq(next).height();
            $wrap.height(height);

            $slides.eq(idx).animate(
                { left: "+="+width }, cfg.duration,
                function() {
                    $slides.eq(idx).removeClass(cfg.active_class);
                }
            );

            $slides.eq(next).animate(
                { left: "+="+width }, cfg.duration,
                function() {
                    $slides.eq(next).addClass(cfg.active_class);
                }
            );
        }

        function check_animated()
        {
            if($slides.filter(":animated").size())
                return true;
        }

        $(window).on("load", function(e) {
            swipe_init();
        });

        $(window).on("resize", function(e) {
            swipe_init();
        });

        // swipe event
        $this
         .on("swipeleft", function(e) {
             swipe_left();
         })
         .on("swiperight", function(e) {
             swipe_right();
        });

        // scroll event enable
        $(window).on("movestart", function(e) {
            if ((e.distX > e.distY && e.distX < -e.distY) ||
            (e.distX < e.distY && e.distX > -e.distY)) {
                e.preventDefault();
            }
        });
    }
}(jQuery));