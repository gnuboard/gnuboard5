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
                duration: 500
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
        var li_left = 0;
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
            tab_width = $tabs.eq(0).width();
            height = $slides.eq(idx).height();
            $wrap.height(height);

            $slides.not("."+cfg.active_class).css("left", "-"+width+"px");

            if(count < 3) {
                pos_left = 0;
                li_left = parseInt((tabw_width - (tab_width * count)) / (count + 1));

                $tabs.each(function(index) {
                    pos_left += (li_left + (tab_width * index));
                    $(this).css("left", pos_left);
                });

                $tabs.removeClass(cfg.tab_actie);
                $tabs.eq(idx).addClass(cfg.tab_active);
            } else {
                li_left = parseInt((tabw_width - (tab_width * 3)) / 2);
                pos_left = tab_width + li_left;
                pos_right = tabw_width - tab_width;

                $tabs.removeClass(cfg.tab_actie+" tab_listed").css("left", "-"+tab_width+"px");

                $tabs.eq(idx - 1).addClass("tab_listed").css("left", "0px");
                $tabs.eq(idx).addClass(cfg.tab_active+" tab_listed").css("left", pos_left+"px");
                $tabs.eq((idx + 1) % count).addClass("tab_listed").css("left", pos_right+"px");

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

            $slides.eq(idx).clearQueue().animate(
                { left: "-="+width }, cfg.duration,
                function() {
                    $slides.eq(idx).css("left", width+"px");
                }
            );

            $slides.eq(next).clearQueue().animate(
                { left: "-="+width }, cfg.duration,
                function() {
                    if(count >= 3) {
                        $tabs.removeClass("tab_listed").css("left", "-"+tab_width+"px");

                        $tabs.eq(next - 1).addClass("tab_listed").css("left", "0px");
                        $tabs.eq(next).addClass("tab_listed").css("left", pos_left+"px");
                        $tabs.eq((next + 1) % count).addClass("tab_listed").css("left", pos_right+"px");
                    }

                    $wrap.height(next_height);
                    $tabs.eq(next).addClass(cfg.tab_active);
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

            $slides.eq(idx).clearQueue().animate(
                { left: "+="+width }, cfg.duration,
                function() {
                    $slides.eq(idx).css("left", "-"+width+"px");
                }
            );

            $slides.eq(next).clearQueue().animate(
                { left: "+="+width }, cfg.duration,
                function() {
                    if(count >= 3) {
                        $tabs.removeClass("tab_listed").css("left", "-"+tab_width+"px");

                        $tabs.eq(next - 1).addClass("tab_listed").css("left", "0px");
                        $tabs.eq(next).addClass("tab_listed").css("left", pos_left+"px");
                        $tabs.eq((next + 1) % count).addClass("tab_listed").css("left", pos_right+"px");
                    }

                    $wrap.height(next_height);
                    $tabs.eq(next).addClass(cfg.tab_active);
                }
            );

            $slides.eq(idx).removeClass(cfg.active_class);
            $slides.eq(next).addClass(cfg.active_class);
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

            idx = $slides.index($slides.filter("."+cfg.active_class));

            var idx_pos = parseInt($tabs.eq(idx).css("left"));
            var btn_pos = parseInt($el.parent().css("left"));

            if(idx_pos > btn_pos)
                swipe_right();
            else(idx_pos < btn_pos)
                swipe_left();
        }

        $(window).on("load", function(e) {
            swipe_init();
        });

        $(window).on("resize", function(e) {
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
            excludedElements:".noSwipe",
            allowPageScroll:"vertical"
        });
    }
}(jQuery));