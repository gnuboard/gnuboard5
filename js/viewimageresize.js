(function($) {
    $.fn.viewimageresize = function(selector)
    {
        var cfg = {
                selector: "img"
            };

        if(typeof selector == "object") {
            cfg = $.extend(cfg, selector);
        } else {
            if(selector) {
                cfg = $.extend({ selector: selector });
            }
        }

        var $img = this.find(cfg.selector);
        var $this = this;

        $img.removeAttr("height")
            .css("height", "");

        function image_resize()
        {
            var width = $this.width();

            $img.each(function() {
				
				if ($(this).attr("loading") === "lazy") return; // loading="lazy" 속성이 있는 경우 건너뜀
				
                if($(this).data("width") == undefined)
                    $(this).data("width", $(this).width());

                if($(this).data("width") > width) {
                    $(this).removeAttr("width")
                           .removeAttr("height")
                           .css("width","")
                           .css("height", "");

                    if($(this).data("width") > width) {
                        $(this).css("width", "100%");
                    }
                } else {
                    $(this).attr("width", $(this).data("width"));
                }
            });
        }

        $(window).on("load", function() {
            image_resize();
        });

        $(window).on("resize", function() {
            image_resize();
        });
    }

    $.fn.viewimageresize2 = function(selector)
    {
        var cfg = {
                selector: "img"
            };

        if(typeof selector == "object") {
            cfg = $.extend(cfg, selector);
        } else {
            if(selector) {
                cfg = $.extend({ selector: selector });
            }
        }

        var $img = this.find(cfg.selector);
        var $this = this;

        function image_resize()
        {
            var width = $this.width();

            $img.each(function() {
				
				if ($(this).attr("loading") === "lazy") return; // loading="lazy" 속성이 있는 경우 건너뜀
				
                $(this).removeAttr("width")
                       .removeAttr("height")
                       .css("width","")
                       .css("height", "");

                if($(this).data("width") == undefined)
                    $(this).data("width", $(this).width());

                if($(this).data("width") > width) {
                    $(this).css("width", "100%");
                }
            });
        }

        $(window).on("resize", function() {
            image_resize();
        });

        image_resize();
    }
}(jQuery));