(function($) {
    $.fn.topFloatMenu = function(timeout, duration)
    {
        var cfg = {
                timeout: 300,
                duration: 300
            };

        if(typeof timeout == "object") {
            cfg = $.extend( cfg, timeout);
        } else {
            if(timeout) {
                cfg = $.extend({ timeout: timeout });
            }

            if(duration) {
                cfg = $.extend({ duration: duration });
            }
        }

        var $menu = this;
        var scroll_y = 0;
        var timeout = null;
        var height = parseInt($menu.height());
        var move_timeout = null;

        function init_menu()
        {
            hide_menu();

            timeout = setTimeout(function() {
                $menu.css("top", (scroll_y - height)+"px").css("display", "block");
                $menu.animate({ top: scroll_y }, cfg.duration);

                return;
            }, cfg.timeout);
        }

        function float_menu()
        {
            hide_menu();

            timeout = setTimeout(function() {
                scroll_y = parseInt(document.body.scrollTop);
                $menu.css("top", (scroll_y - height)+"px").css("display", "block");
                $menu.animate({ top: scroll_y }, cfg.duration);

                return;
            }, cfg.timeout);
        }

        function hide_menu()
        {
            clearTimeout(timeout);
            $menu.clearQueue().stop().hide().css("top", "-"+height+"px");
        }

        $(window).on("scroll",function(event) {
            float_menu();
        });

        $(window).on("resize", function(event) {
            $(window).trigger("scroll");
        });

        $(window).on("load", function(event) {
            init_menu();
        });

        $(window).on("touchstart", function(event) {
            hide_menu();
        });

        if(navigator.userAgent.toLowerCase().indexOf("android") > -1) {
            $(window).on("touchend", function(event) {
                $(window).trigger("scroll");
            });
        }
    }

    $.fn.bottomFloatMenu = function(timeout, duration)
    {
        var cfg = {
                timeout: 300,
                duration: 300
            };

        if(typeof timeout == "object") {
            cfg = $.extend( cfg, timeout);
        } else {
            if(timeout) {
                cfg = $.extend({ timeout: timeout });
            }

            if(duration) {
                cfg = $.extend({ duration: duration });
            }
        }

        var $menu = this;
        var scroll_y = 0;
        var move_y = 0;
        var element_y = 0;
        var top_pos = 0;
        var timeout = null;
        var height = parseInt($menu.height());
        var w_height = 0;

        function init_menu()
        {
            hide_menu();

            timeout = setTimeout(function() {
                scroll_y = parseInt(document.body.scrollTop);
                w_height = $(window).height();
                element_y = scroll_y + w_height;
                $menu.css("top", element_y+"px").css("display", "block");
                $menu.clearQueue().stop().animate({ top: "-="+height }, cfg.duration);
            }, cfg.timeout);
        }

        function float_menu()
        {
            hide_menu();

            w_height = $(window).height();
            scroll_y = parseInt(document.body.scrollTop);
            element_y = scroll_y + w_height;

            if (/iP(hone|od|ad)/.test(navigator.platform)) {
                if(window.innerHeight - $(window).outerHeight(true) > 0)
                    element_y += (window.innerHeight - $(window).outerHeight(true));
            }

            timeout = setTimeout(function() {
                $menu.height(0).css("top", element_y+"px").css("display", "block");
                $menu.animate({
                    top: "-="+height,
                    height: "+="+height
                }, cfg.duration);
            }, cfg.timeout);
        }

        function hide_menu()
        {
            clearTimeout(timeout);
            $menu.css("top", (w_height + height)+"px").clearQueue().stop().css("display", "none");
        }

        $(window).on("scroll",function(event) {
            float_menu();
        });

        $(window).on("load", function(event) {
            init_menu();
        });

        $(window).on("resize", function(event) {
            $(window).trigger("scroll");
        });

        $(window).on("touchstart", function(event) {
            hide_menu();
        });

        if(navigator.userAgent.toLowerCase().indexOf("android") > -1) {
            $(window).on("touchend", function(event) {
                $(window).trigger("scroll");
            });
        }
    }
}(jQuery));