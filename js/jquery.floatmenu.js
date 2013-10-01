(function($) {
    $.fn.topFloatMenu = function(timeout, duration, interval, count)
    {
        var cfg = {
                timeout: 200,
                duration: 300,
                interval: 500,
                count: 10
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

            if(interval) {
                cfg = $.extend({ interval: interval });
            }

            if(count) {
                cfg = $.extend({ count: count });
            }
        }

        var $menu = this;
        var scroll_y = 0;
        var origin_y = 0;
        var timeout = null;
        var interval = null;
        var height = parseInt($menu.height());
        var interval_count = 0;

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

            origin_y = $("body").scrollTop();

            timeout = setTimeout(function() {
                scroll_y = $("body").scrollTop();

                if(origin_y == scroll_y) {
                    $menu.css("top", (scroll_y - height)+"px").css("display", "block");
                    $menu.animate({ top: scroll_y }, cfg.duration);
                }
            }, cfg.timeout);
        }

        function hide_menu()
        {
            clearTimeout(timeout);
            clearInterval(interval);
            $menu.css("display", "none").clearQueue().stop().css("top", "-"+height+"px");

            interval_count = 0;
            interval = setInterval(check_menu, cfg.interval);
        }

        function check_menu()
        {
            clearTimeout(timeout);

            if(interval_count == parseInt(cfg.count)) {
                clearInterval(interval);
                interval_count = 0;
                return;
            } else {
                interval_count++;
            }

            origin_y = $("body").scrollTop();

            timeout = setTimeout(function() {
                scroll_y = $("body").scrollTop();

                if(origin_y == scroll_y) {
                    element_y = parseInt($menu.css("top"));

                    if(!$menu.is(":animated") && ($menu.is(":hidden") || (element_y - scroll_y) != 0)) {
                        float_menu();
                    }
                }
            }, cfg.timeout);
        }

        $(window).on("scroll",function(event) {
            float_menu();
        });

        $(window).on("resize", function(event) {
            float_menu();
        });

        $(window).on("load", function(event) {
            init_menu();
        });

        $(window).on("touchstart", function(event) {
            hide_menu();
        });

        if(navigator.userAgent.toLowerCase().indexOf("android") > -1) {
            $(window).on("touchmove", function(event) {
                hide_menu();
            });
        }
    }

    $.fn.bottomFloatMenu = function(timeout, duration, interval, count)
    {
        var cfg = {
                timeout: 200,
                duration: 300,
                interval: 500,
                count: 10
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

            if(interval) {
                cfg = $.extend({ interval: interval });
            }

            if(count) {
                cfg = $.extend({ count: count });
            }
        }

        var $menu = this;
        var scroll_y = 0;
        var origin_y = 0;
        var element_y = 0;
        var timeout = null;
        var interval = null;
        var height = parseInt($menu.height());
        var w_height = 0;
        var interval_count = 0;

        function init_menu()
        {
            hide_menu();

            timeout = setTimeout(function() {
                scroll_y = $("body").scrollTop();
                w_height = $(window).height();
                element_y = scroll_y + w_height;
                $menu.css("top", element_y+"px").css("display", "block");
                $menu.clearQueue().stop().animate({ top: "-="+height }, cfg.duration);
            }, cfg.timeout);
        }

        function float_menu()
        {
            hide_menu();

            origin_y = $("body").scrollTop();

            timeout = setTimeout(function() {
                scroll_y = $("body").scrollTop();

                if(origin_y == scroll_y) {
                    w_height = $(window).height();
                    element_y = scroll_y + w_height;

                    if (/iP(hone|od|ad)/.test(navigator.platform)) {
                        if(window.innerHeight - $(window).outerHeight(true) > 0)
                            element_y += (window.innerHeight - $(window).outerHeight(true));
                    }

                    $menu.height(0).css("top", element_y+"px").css("display", "block");
                    $menu.animate({
                        top: "-="+height,
                        height: "+="+height
                    }, cfg.duration);
                }
            }, cfg.timeout);
        }

        function hide_menu()
        {
            clearTimeout(timeout);
            clearInterval(interval);
            $menu.css("display", "none").css("top", (w_height + height)+"px").clearQueue().stop();

            interval_count = 0;
            interval = setInterval(check_menu, cfg.interval);
        }

        function check_menu()
        {
            clearTimeout(timeout);

            if(interval_count == parseInt(cfg.count)) {
                clearInterval(interval);
                interval_count = 0;
                return;
            } else {
                interval_count++;
            }

            origin_y = $("body").scrollTop();

            timeout = setTimeout(function() {
                scroll_y = $("body").scrollTop();

                if(origin_y == scroll_y) {
                    w_height = $(window).height();
                    element_y = parseInt($menu.css("top"));

                    var h = 0;
                    if (/iP(hone|od|ad)/.test(navigator.platform)) {
                        if(window.innerHeight - $(window).outerHeight(true) > 0)
                            h = window.innerHeight - $(window).outerHeight(true);
                    }

                    if(!$menu.is(":animated") && ($menu.is(":hidden") || element_y != (scroll_y + w_height + h - height))) {
                        float_menu();
                    }
                }
            }, cfg.timeout);
        }

        $(window).on("scroll",function(event) {
            float_menu();
        });

        $(window).on("load", function(event) {
            init_menu();
        });

        $(window).on("resize", function(event) {
            float_menu();
        });

        $(window).on("touchstart", function(event) {
            hide_menu();
        });

        if(navigator.userAgent.toLowerCase().indexOf("android") > -1) {
            $(window).on("touchmove", function(event) {
                hide_menu();
            });
        }
    }
}(jQuery));