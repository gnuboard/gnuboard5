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
                duration: 300
            };

        if(typeof option == "object")
            cfg = $.extend( cfg, option );

        var $this = this;
        var $wrap = this.find("#"+cfg.slide_wrap);
        var $tab = null;
        var $tabs;
        var $slides = this.find(cfg.slide);

        $slides.addClass(cfg.slide_class);

        var height;
        var width = $(window).width();
        var count = $slides.size();
        var idx = next = 0;
        var tabw_width = 0;
        var tabs_count = 0;
        var tab_width = 0;
        var li_left = 0;
        var pos_left = 0;

        function tab_make()
        {
            if(count < 1)
                return;

            idx = $slides.index($slides.filter("."+cfg.active_class));
            if(idx == -1)
                idx = 0;

            var subj;
            var tabs = "";

            if($tab == null) {
                $slides.each(function() {
                    subj = $(this).find("header h2").text();
                    if(subj.length < 1)
                        subj = "&nbsp;";

                    tabs += "<li>"+subj+"</li>\n";
                });

                if(tabs != "") {
                    tabs = "<ul id=\""+cfg.slide_tab+"\">\n"+tabs+"</ul>";
                    $wrap.before(tabs);

                    $tab = $this.find("#"+cfg.slide_tab);
                    $tabs = $tab.find("li");
                }

                tabs_count = $tabs.size();
            }

            tabw_width = $tab.width();
            tab_width = $tabs.eq(idx).width();

            if(tabs_count < 1) {
                $tab.remove();
                return;
            }

            if(tabs_count < 3) {
                li_left = parseInt((tabw_width - (tab_width * tabs_count)) / (tabs_count + 1));

                $tabs.each(function(index) {
                    pos_left += (li_left + (tab_width * index));
                    $(this).css("left", pos_left);
                });

                $tabs.eq(idx).addClass(cfg.tab_active);
            } else {
                li_left = parseInt((tabw_width - (tab_width * 3)) / 2);
                pos_left = tab_width + li_left;
                pos_right = tabw_width - tab_width;

                $tabs.css("display", "none").removeClass("tab_listed");

                $tabs.eq(idx - 1).addClass("tab_listed").css({left: "0px", display: "block"});
                $tabs.eq(idx).addClass("tab_listed").css({left: pos_left+"px", display: "block"}).addClass(cfg.tab_active);
                $tabs.eq((idx + 1) % count).addClass("tab_listed").css({left: pos_right+"px", display: "block"});

                $tabs.not(".tab_listed").css("left", "-"+tab_width+"px");
            }
        }

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

                    if(count >= 3) {
                        $tabs.removeClass("tab_listed").css("display", "none");

                        $tabs.eq(next - 1).addClass("tab_listed").css({left: "0px", display: "block"});
                        $tabs.eq(next).addClass("tab_listed").css({left: pos_left+"px", display: "block"});
                        $tabs.eq((next + 1) % count).addClass("tab_listed").css({left: pos_right+"px", display: "block"});

                        $tabs.not(".tab_listed").css("left", tabw_width+"px");
                    }

                    $tabs.removeClass(cfg.tab_active);
                    $tabs.eq(next).addClass(cfg.tab_active);
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

                    if(count >= 3) {
                        $tabs.css("display", "none").removeClass("tab_listed");

                        $tabs.eq(next - 1).addClass("tab_listed").css({left: "0px", display: "block"});
                        $tabs.eq(next).addClass("tab_listed").css({left: pos_left+"px", display: "block"});
                        $tabs.eq((next + 1) % count).addClass("tab_listed").css({left: pos_right+"px", display: "block"});

                        $tabs.not(".tab_listed").css("left", "-"+tab_width+"px");
                    }

                    $tabs.removeClass(cfg.tab_active);
                    $tabs.eq(next).addClass(cfg.tab_active);
                }
            );
        }

        function check_animated()
        {
            if($slides.filter(":animated").size())
                return true;
        }

        $(window).on("load", function(e) {
            tab_make();
            swipe_init();
        });

        $(window).on("resize", function(e) {
            tab_make();
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