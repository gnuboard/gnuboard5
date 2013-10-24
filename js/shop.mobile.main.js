(function($) {
    $.fn.slideSwipe = function(option)
    {
        var cfg = {
                slide_wrap: "sidx_slide",
                slide: "section",
                slide_tab: "slide_tab",
                slide_class: "sidx_slide",
                active_class: "slide_active",
                tab_active: "tab_active",
                duration: 500,
                width_increase: 10
            };

        if(typeof option == "object")
            cfg = $.extend( cfg, option );

        var $this = this;
        var $wrap = this.find("#"+cfg.slide_wrap);
        var $tab = null;
        var $tabs;
        var $btns;
        var $slides = this.find(cfg.slide);
        var count = $slides.size();

        if(count < 1)
            return;

        $slides.addClass(cfg.slide_class);

        var height;
        var next_height;
        var width;
        var idx = next = 0;
        var tabw_width = 0;
        var tab_width = 0;
        var pos_left = 0;

        function tab_make()
        {
            var subj;
            var tabs = "";

            $slides.each(function() {
                subj = $(this).find("header h2").text();
                if(subj.length < 1)
                    subj = "&nbsp;";

                tabs += "<li><button type=\"button\">"+subj+"</button></li>\n";
            });

            if(tabs != "") {
                tabs = "<ul id=\""+cfg.slide_tab+"\">\n"+tabs+"</ul>";
                $wrap.before(tabs);

                $tab = $this.find("#"+cfg.slide_tab);
                $tabs = $tab.find("li");
                $btns = $tab.find("button");

                $tabs.each(function() {
                    $(this)
                        .css("width", $(this).width())
                        .data("width", $(this).width());
                });

                $btns.on("click", function() {
                    tab_click($(this));
                });
            }
        }

        function swipe_init()
        {
            if($tab == null)
                tab_make();

            idx = $slides.index($slides.filter("."+cfg.active_class));
            if(idx == -1)
                idx = 0;

            $slides.eq(idx).addClass(cfg.active_class);

            width = $wrap.width();
            tabw_width = $tab.width();
            height = $slides.eq(idx).height();
            $wrap.height(height);

            $slides.not("."+cfg.active_class).css("left", "-"+width+"px");

            set_tab_width(idx);

            if(count == 1) {
                tab_width = $tabs.eq(0).outerWidth();
                pos_left = parseInt((tabw_width - tab_width) / 2);
                $tabs.eq(0).css("left", pos_left).addClass(cfg.tab_active);
            } else if(count == 2) {
                $tabs.eq(0).css("left", 0).addClass("tab_left");
                $tabs.eq(1).css("right", 0);
                $tabs.removeClass(cfg.tab_active);
                $tabs.eq(idx).addClass(cfg.tab_active);
            } else if(count >= 3) {
                tab_position(idx);

                $slides.eq((idx - 1)).css("left", "-"+width+"px");
                $slides.eq((idx + 1) % count).css("left", width+"px");
            }
        }

        function swipe_left()
        {
            if(count < 2)
                return;

            if(check_animated())
                return;

            idx = $slides.index($slides.filter("."+cfg.active_class));
            next = (idx + 1) % count;

            height = $wrap.height();
            next_height = $slides.eq(next).height();

            if(height >= next_height)
                $wrap.height(height);
            else
                $wrap.height(next_height);

            $slides.eq(next).css("left", width+"px");
            $tabs.removeClass(cfg.tab_active);

            set_tab_width(next);

            if(count >= 3) {
                tab_position(next);
            } else {
                $tabs.eq(next).addClass(cfg.tab_active);
            }

            $slides.eq(idx).clearQueue().animate(
                { left: "-="+width }, cfg.duration,
                function() {
                    $slides.eq(idx).css("left", width+"px");
                }
            );

            $slides.eq(next).clearQueue().animate(
                { left: "-="+width }, cfg.duration,
                function() {
                    $wrap.height(next_height);
                }
            );

            $slides.eq(idx).removeClass(cfg.active_class);
            $slides.eq(next).addClass(cfg.active_class);
        }

        function swipe_right()
        {
            if(count < 2)
                return;

            if(check_animated())
                return;

            idx = $slides.index($slides.filter("."+cfg.active_class));
            next = idx - 1;
            if(next < 0)
                next = count - 1;

            height = $wrap.height();
            next_height = $slides.eq(next).height();

            if(height >= next_height)
                $wrap.height(height);
            else
                $wrap.height(next_height);

            $slides.eq(next).css("left", "-"+width+"px");
            $tabs.removeClass(cfg.tab_active);

            set_tab_width(next);

            if(count >= 3) {
                tab_position(next);
            } else {
                $tabs.eq(next).addClass(cfg.tab_active);
            }

            $slides.eq(idx).clearQueue().animate(
                { left: "+="+width }, cfg.duration,
                function() {
                    $slides.eq(idx).css("left", "-"+width+"px");
                }
            );

            $slides.eq(next).clearQueue().animate(
                { left: "+="+width }, cfg.duration,
                function() {
                    $wrap.height(next_height);
                }
            );

            $slides.eq(idx).removeClass(cfg.active_class);
            $slides.eq(next).addClass(cfg.active_class);
        }

        function set_tab_width(idx)
        {
            $tabs.each(function() {
                $(this).css("width", $(this).data("width"));
            });

            $tabs.eq(idx).css("width", "+="+cfg.width_increase);
        }

        function tab_position(idx)
        {
            $tabs.removeClass(cfg.tab_actie+" tab_listed tab_left").css("left", "-"+tabw_width+"px");

            var $tab_l = $tabs.eq(idx - 1);
            var $tab_c = $tabs.eq(idx);
            var $tab_r = $tabs.eq((idx + 1) % count);
            var w_c = $tab_c.outerWidth();
            var w_r = $tab_r.outerWidth();

            var pl = 0;
            var pc = parseInt((tabw_width - w_c) / 2);
            var pr = tabw_width - w_r;

            $tab_l.addClass("tab_listed tab_left").css("left", pl);
            $tab_c.addClass(cfg.tab_active+" tab_listed").css("left", pc);
            $tab_r.addClass("tab_listed").css("left", pr);
        }

        function check_animated()
        {
            if($slides.filter(":animated").size())
                return true;
            else
                return false;
        }

        function tab_click($el)
        {
            if(check_animated())
                return;

            if($el.parent().hasClass(cfg.tab_active))
                return;

            if($el.parent().hasClass("tab_left"))
                swipe_right();
            else
                swipe_left();
        }

        $(window).on("load resize", function(e) {
            swipe_init();
        });

        // swipe event
        this.swipe({
            swipe: function(event, direction, duration, fingerCount) {
                switch(direction) {
                    case "left":
                        swipe_left();
                        break;
                    case "right":
                        swipe_right();
                        break;
                }
            },
            threshold: 100,
            excludedElements:"button, input, select, textarea, .noSwipe",
            allowPageScroll:"vertical"
        });
    }
}(jQuery));