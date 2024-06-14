jQuery(function($){
    $(document).ready(function(){
        
		$(".owl-carousel-wrap .sv_member").off('click').off('focusin');
		
		if ($.fn.tooltipster) {
			var $member_menu_tip = $('.owl-carousel-wrap .sv_member').tooltipster({
					theme: 'tooltipster-sir',
					trigger: 'custom',
					contentAsHTML: true,
					interactive: true,
					distance: 5,
					position:'bottom',
					coord: {
						left: 20,
						bottom: 0
					},
					triggerOpen: {
						click: true,
						tap: true    // For touch device
					},
					triggerClose: {
						click: true,
						scroll: true,
						tap: true
					},
					functionBefore: function(instance, helper) {
						var content = $(helper.origin).parent().find('.sv')[0].outerHTML;
						instance.content(content);
					},
					functionReady : function(instance, helper) {
						$(helper.tooltip).find(".tooltipster-arrow").css({left:16});
					},
					functionPosition: function(instance, helper, position){
						position.coord.left += 7;
						return position;
					}
			});
		}

        var carousels = [],
            is_loop = true;

        function owl_show_page(event){

            if (event.item) {
                var count = event.item.count,
                    item_index = event.item.index,
                    index = 1;

                if( is_loop ){
                    index = ( 1 + ( event.property.value - Math.ceil( event.item.count / 2 ) ) % event.item.count || 0 ) || 1;
                } else {
                    index = event.item.index ? event.item.index + 1 : 1;
                }

                var str = "<b>"+index+"</b>/"+count;

                $(event.target).next(".lt_page").find(".page_print").html(str);
            }
        }

        $(".lt.owl-carousel-wrap").each(function(index, value) {

            var $this = $(this),
                item_loop_c = ($this.children('.latest-sel').find(".item").length > 1) ? 1 : 0 ;
            
            carousels['sel' + index] = $this.children('.latest-sel').addClass("owl-carousel").owlCarousel({
                items:1,
                loop: is_loop && item_loop_c,
                center:true,
                autoHeight:true,
                dots:false,
                onChanged:function(event){
                    owl_show_page(event);
                },
            });
			
			carousels['sel' + index].on('changed.owl.carousel', function(event) {
				
				if ($.fn.tooltipster) {
					var instances = $.tooltipster.instances();
					$.each(instances, function(i, instance){
						instance.close();
					});
				}
			});

            $this.on("click", ".lt_page_next", function(e) {
                e.preventDefault();
                carousels['sel' + index].trigger('next.owl.carousel');
            });

            $this.on("click", ".lt_page_prev", function(e) {
                e.preventDefault();
                carousels['sel' + index].trigger('prev.owl.carousel');
            });

        });     // each
    });
});