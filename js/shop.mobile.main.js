(function($) {
    $.fn.swipeSlide = function(option)
    {
        var cfg = {
                slides: ".swipe-wrap > div",
                header: "header h2",
                tabWrap: "slide_tab",
                tabActive: "tab_active",
                tabOffset: 10,
                startSlide: 0,
                auto: 0,
                continuous: true,
                disableScroll: false,
                stopPropagation: false,
                callback: function(index, element) {
                  tab_change(index);
                },
                transitionEnd: function(index, element) {
                    set_height(index);
                    idx = index;
                }
            };

        if(typeof option == "object")
            cfg = $.extend( cfg, option );

        var $wrap = this;
        var $slides = this.find(""+cfg.slides+"");
        var $tab = null;
        var $tabs;
        var $btns;

        var idx = cfg.startSlide;
        var count = $slides.size();
        var width, height;
        var tabw_width = 0;
        var tab_width = 0;
        var pos_left = 0;

        if(count < 1)
            return;

        function tab_make()
        {
            var subj;
            var tabs = "";

            $slides.each(function() {
                subj = $(this).find(""+cfg.header+"").text();
                if(subj.length < 1)
                    subj = "&nbsp;";

                tabs += "<li><button type=\"button\">"+subj+"</button></li>\n";
            });

            if(tabs != "") {
                tabs = "<ul id=\""+cfg.tabWrap+"\">\n"+tabs+"</ul>";
                $wrap.before(tabs);

                $tab = $("#"+cfg.tabWrap);
                $tabs = $tab.find("li");
                $btns = $tab.find("button");

                $tabs.each(function() {
                    $(this)
                        .css("width", $(this).width())
                        .data("width", $(this).width());
                });

                $btns.off("click");
            }
        }

        function set_tab_width(idx)
        {
            $tabs.each(function() {
                $(this).css("width", $(this).data("width"));
            });

            $tabs.eq(idx).css("width", "+="+cfg.tabOffset);
        }

        function tab_position(idx)
        {
            $tabs.removeClass(""+cfg.tabActive+" tab_listed tab_left").css("left", "-"+tabw_width+"px");

            var $tab_l = $tabs.eq(idx - 1);
            var $tab_c = $tabs.eq(idx);
            var $tab_r = $tabs.eq((idx + 1) % count);
            var w_c = $tab_c.outerWidth();
            var w_r = $tab_r.outerWidth();

            var pl = 0;
            var pc = parseInt((tabw_width - w_c) / 2);
            var pr = tabw_width - w_r;

            $tab_l.addClass("tab_listed").css("left", pl);
            $tab_c.addClass(""+cfg.tabActive+" tab_listed").css("left", pc);
            $tab_r.addClass("tab_listed").css("left", pr);
        }

        function tab_change(idx)
        {
            if(count < 2)
                return;

            set_height(idx);
            set_tab_width(idx);

            if(count == 2) {
                $tabs.eq(0).css("left", 0);
                $tabs.eq(1).css("right", 0);
                $tabs.removeClass(""+cfg.tabActive+"");
                $tabs.eq(idx).addClass(""+cfg.tabActive+"");
            } else if(count >= 3) {
                tab_position(idx);
            }
        }

        function set_height(idx)
        {
            var offset = $wrap.outerHeight() - $wrap.height();

            height = $slides.eq(idx).height() + offset;
            $wrap.height(height);
        }

        function init()
        {
            if($tab == null)
                tab_make();

            width = $wrap.width();
            tabw_width = $tab.width();

            set_tab_width(idx);

            if(count == 1) {
                tab_width = $tabs.eq(0).outerWidth();
                pos_left = parseInt((tabw_width - tab_width) / 2);
                $tabs.eq(0).css("left", pos_left).addClass(""+cfg.tabActive+"");
            } else if(count == 2) {
                $tabs.eq(0).css("left", 0);
                $tabs.eq(1).css("right", 0);
                $tabs.removeClass(""+cfg.tabActive+"");
                $tabs.eq(idx).addClass(""+cfg.tabActive+"");
            } else if(count >= 3) {
                tab_position(idx);
            }
        }

        init();

        window.mySwipe = Swipe(this[0], {
            startSlide: cfg.startSlide,
            auto: cfg.auto,
            continuous: cfg.continuous,
            disableScroll: cfg.disableScroll,
            stopPropagation: cfg.stopPropagation,
            callback: cfg.callback,
            transitionEnd: cfg.transitionEnd
        });

        $(window).on("resize", function() {
            $("#"+cfg.tabWrap).remove();
            $tab = null;

            init();
        });

        if(count > 0 && mySwipe) {
            $btns.on("click", function() {
                if($(this).parent().hasClass(""+cfg.tabActive+""))
                    return false;

                idx = $btns.index($(this));
                mySwipe.slide(idx);
            });
        }
    }
}(jQuery));