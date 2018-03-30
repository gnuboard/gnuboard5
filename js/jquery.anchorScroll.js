/*!
 * jQuery.anchorScroll jQuery Plugin v1.0
 *
 * Author: Virgiliu Diaconu
 * http://www.virgiliu.com
 * Licensed under the MIT license.
 */
;
(function($, window, document, undefined) {
    'use strict';
    $.anchorScroll = function(el, options) {
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;

        base.init = function() {
            base.options = $.extend({}, $.anchorScroll.defaultOptions, options);
        };

        // On click
        base.$el.click(function(e) {
            e.preventDefault();
            if ($(e.target).closest('a').length && $(base.el.hash).length) {
                var targetPos = $(base.el.hash).offset().top - base.options.offsetTop,
                    classTo = (base.$el.data("classTo") === "this") ? base.el : base.$el.data("classTo"),
                    onScroll = base.$el.data("onScroll"),
                    scrollEnd = base.$el.data("scrollEnd");
                // Callback scroll start
                if (typeof base.options.scrollStart === 'function') {
                    base.options.scrollStart.call(el);
                }
                // Add class to element on scroll
                $(classTo).addClass(onScroll).removeClass(scrollEnd);
                // Smooth scroll
                $('html,body').animate({
                    scrollTop: targetPos
                }, base.options.scrollSpeed).promise().done(function() {
                    // On animation complete
                    $(classTo).addClass(scrollEnd).removeClass(onScroll);
                    // Callback on scroll end
                    if (typeof base.options.scrollEnd === 'function') {
                        base.options.scrollEnd.call(el);
                    }
                });
            }
        });

        // Run initializer
        base.init();
    };

    $.anchorScroll.defaultOptions = {
        scrollSpeed: 800,
        offsetTop: 0
    };

    $.fn.anchorScroll = function(options) {
        return this.each(function() {
            (new $.anchorScroll(this, options));
        });
    };

})(jQuery, window, document);