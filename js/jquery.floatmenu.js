(function($) {
    $.fn.floatTopMenu = function(option)
    {
        var cfg = {
                duration: 200
            };

        var $this = this;
        var height = 0;
        var scroll_y = 0;

        var methods = {
            init: function(option)
            {
                if($this.data("animated") == true)
                    return;

                $this.data("animated", true);

                if(typeof option == "object") {
                    cfg = $.extend( cfg, option);
                }

                $this.data("cfg", cfg);
                $this.css({ top: "-500px", display: "block" });

                height = parseInt($this.outerHeight());
                scroll_y = $(window).scrollTop();

                $this
                    .css("display", "none")
                    .clearQueue()
                    .stop()
                    .css("top", (scroll_y - height)+"px").css("display", "block")
                    .animate({ top: scroll_y }, cfg.duration, function() { $this.data("animated", false); });
            },
            show: function()
            {
                if($this.data("animated") == true)
                    return;

                $this.data("animated", true);

                if($this.data("cfg")) {
                    cfg = $.extend( cfg, $this.data("cfg"));
                }

                $this.css({ top: "-"+height+"px", display: "block" });

                height = parseInt($this.outerHeight());
                scroll_y = $(window).scrollTop();

                $this
                    .css("display", "none")
                    .css("top", (scroll_y - height)+"px").css("display", "block")
                    .animate({ top: scroll_y }, cfg.duration, function() { $this.data("animated", false); });
            },
            hide: function()
            {
                $this.css({ display: "none", top: "-"+height+"px" });
            }
        };

        if (methods[option])
            return methods[option].apply(this, Array.prototype.slice.call(arguments, 1));
        else
            return methods.init.apply(this, arguments);
    }

    $.fn.floatBottomMenu = function(option)
    {
        var cfg = {
                duration: 200
            };

        var $this = this;
        var height = 0;
        var scroll_y = 0;
        var w_height = 0;
        var element_y = 0;

        var methods = {
            init: function(option)
            {
                if($this.data("animated") == true)
                    return;

                $this.data("animated", true);

                if(typeof option == "object") {
                    cfg = $.extend( cfg, option);
                }

                $this.data("cfg", cfg);

                $this.css({ top: "-500px", display: "block" });

                height = parseInt($this.outerHeight());
                scroll_y = $(window).scrollTop();
                w_height = $(window).height();
                element_y = scroll_y + w_height;

                $this
                    .css("display", "none")
                    .clearQueue()
                    .stop()
                    .css({ top: element_y+"px", display: "block" })
                    .animate({ top: "-="+height }, cfg.duration, function() { $this.data("animated", false); });
            },
            show: function()
            {
                if($this.data("animated") == true)
                    return;

                $this.data("animated", true);

                if($this.data("cfg")) {
                    cfg = $.extend( cfg, $this.data("cfg"));
                }

                $this.css({ top: "-"+height+"px", display: "block" });

                height = parseInt($this.outerHeight());
                scroll_y = $(window).scrollTop();
                w_height = $(window).height();
                element_y = scroll_y + w_height;

                if (/iP(hone|od|ad)/.test(navigator.platform)) {
                    if(window.innerHeight - $(window).outerHeight(true) > 0)
                        element_y += (window.innerHeight - $(window).outerHeight(true));
                }

                $this
                    .css("display", "none")
                    .clearQueue()
                    .stop()
                    .height(0)
                    .css({top: element_y+"px", display: "block"})
                    .animate({
                        top: "-="+height,
                        height: "+="+height
                    }, cfg.duration,
                        function() {
                            $this.data("animated", false);
                        }
                    );
            },
            hide: function()
            {
                this.css({ display: "none", top: "-"+height+"px" });
            }
        };

        if (methods[option])
            return methods[option].apply(this, Array.prototype.slice.call(arguments, 1));
        else
            return methods.init.apply(this, arguments);
    }
}(jQuery));