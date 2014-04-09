(function($) {
    $.fn.fancyList = function(element, clear)
    {
        var cfg = {
                element: "li",
                clear: "clear"
            };

        if(typeof element == "object")
            cfg = $.extend( cfg, element );
        else {
            if(element)
                cfg = $.extend( cfg, { element: element } );
            if(clear)
                cfg = $.extend( cfg, { clear: clear } );
        }

        var $element = this.find(cfg.element);
        var $this = this;

        if($element.size() < 1)
            return;

        function item_arrange()
        {
            var $el = $element.filter(":first");
            var padding = 0;
            if($el.data("padding-right") == undefined) {
                padding = parseInt($el.css("padding-right"));
                $el.data("padding-right", padding);
            }
            else
                padding = $el.data("padding-right");

            $element.css("padding-left", 0).css("padding-right", padding);
            $element.filter("."+cfg.clear).removeClass(cfg.clear);

            var wrap_width = parseInt($this.width());
            var item_width = parseInt($el.outerWidth());
            var line_count = parseInt((wrap_width + padding) / item_width);

            if(line_count == 0)
                return;

            var space = parseInt(wrap_width % item_width);

            if((space + padding) < item_width) {
                space = wrap_width - ((item_width - padding) * line_count);
                var new_padding = parseInt(space / (line_count * 2));

                if(new_padding > padding)
                    $element.css("padding-left", new_padding+"px").css("padding-right", new_padding);
            }

            $element.filter(":nth-child("+line_count+"n)").css("padding-right", 0);
            $element.filter(":nth-child("+line_count+"n+1)").addClass(cfg.clear);
        }

        item_arrange();

        $(window).resize(function() {
            item_arrange();
        });
    }
}(jQuery));