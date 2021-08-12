(function($) {
    $.fn.swipeSlide = function(option)
    {
        var cfg = {
                slides: ".swipe-wrap > div",
                buttons: ".mli_btn > button",
                tabOffset: 10,
                startSlide: 0,
                auto: 0,
                continuous: true,
                disableScroll: false,
                stopPropagation: false,
                transitionEnd: function(index, element) {
                    set_height(index);
                    idx = index;
                }
            };

        if(typeof option == "object")
            cfg = $.extend( cfg, option );

        var $wrap = this;
        var $slides = this.find(""+cfg.slides+"");
        var $btns;

        var idx = cfg.startSlide;
        var count = $slides.length;
        var width, height;
        var tab = null;

        if(count < 1)
            return;

        function tab_make()
        {
            if(count < 2)
                return;

            if(tab == null) {
                tab = "<div class=\"mli_btn\"><button type=\"button\" class=\"mli_pre\">이전 리스트</button><button type=\"button\" class=\"mli_next\">다음 리스트</button></div>";

                $slides.each(function() {
                    $(this).find("header").append(tab);
                });

                $btns = $wrap.find(""+cfg.buttons+"");
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
            if(tab == null)
                tab_make();
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

        if(count > 0 && mySwipe) {
            $btns.on("click", function() {
                if($(this).hasClass("mli_next")) {
                    idx = (idx + 1) % count;
                } else {
                    idx = (idx - 1);
                    if(idx == -1)
                        idx = count - 1;
                }

                mySwipe.slide(idx);
            });
        }
    }
}(jQuery));

(function($) {
    $.fn.bannerSlide = function(option)
    {
        var cfg = {
                wrap: ".slide-wrap",
                slides: ".slide-wrap > li",
                buttons: ".silde_btn > button",
                btnActive: "bn_sl",
                startSlide: 0,
                auto: 0,
                continuous: true,
                disableScroll: false,
                stopPropagation: false,
                callback: function(index, element) {
                  button_change(index);
                },
                transitionEnd: function(index, element) {
                    idx = index;
                }
            };

        if(typeof option == "object")
            cfg = $.extend( cfg, option );

        var $wrap = this.find(""+cfg.wrap+"");
        var $slides = this.find(""+cfg.slides+"");
        var $btns = this.find(""+cfg.buttons+"");

        var idx = cfg.startSlide;
        var count = $slides.length;
        var width, outerW;

        if(count < 1)
            return;

        function button_change(idx)
        {
            if(count < 2)
                return;

            $btns.removeClass(cfg.btnActive)
                 .eq(idx).addClass(cfg.btnActive);
        }

        window.bnSwipe = Swipe(this[0], {
            startSlide: cfg.startSlide,
            auto: cfg.auto,
            continuous: cfg.continuous,
            disableScroll: cfg.disableScroll,
            stopPropagation: cfg.stopPropagation,
            callback: cfg.callback,
            transitionEnd: cfg.transitionEnd
        });

        if(count > 0 && bnSwipe) {
            $btns.on("click", function() {
                if($(this).hasClass(""+cfg.btnActive+""))
                    return false;

                idx = $btns.index($(this));
                bnSwipe.slide(idx);
            });
        }
    }
}(jQuery));