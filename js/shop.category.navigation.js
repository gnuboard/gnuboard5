jQuery(function($){

	$.fn.shop_select_to_html = function(option) {
		
		var defaults = {
			menu_down_icon : '<i class="fa fa-chevron-circle-down" aria-hidden="true"></i>'
		}

		if (typeof option == 'string') {
		} else if(typeof option == 'object'){
			defaults = $.extend({}, defaults, option);
		}

		// Hide native select
		this.hide();

		this.each(function() {
			var $select = $(this);

			if (!$select.next().hasClass('shop_select_to_html')) {
				create_html_select($select);
			}
		});

		function create_html_select($select) {

			$select.after($('<div></div>')
			.addClass('shop_select_to_html')
			.addClass($select.attr('class') || '')
			.addClass($select.attr('disabled') ? 'disabled' : '')
			// .attr('tabindex', $select.attr('disabled') ? null : '0')
			.html('<span class="category_title current"></span><div class="menulist"></div>')
			);

			var $dropdown = $select.next(),
				$options = $select.find('option'),
				$selected = $select.find('option:selected'),
				list_next_num = 8,
				menuhtmls = [];

			$dropdown.find('.current').html($selected.data('display') || $selected.text()+defaults.menu_down_icon);
			
			var $ul_el = $('<ul></ul>'),
				options_length = $options.length;

			if( options_length > list_next_num ){
				$ul_el.addClass("wide");
			}

			$options.each(function(i) {
				var $option = $(this),
					display = $option.data('display'),
					data_url = $(this).attr("data-url");

				$ul_el.append($('<li></li>')
					.attr('data-value', $option.val())
					.attr('data-display', (display || null))
					.addClass('option' +
					($option.is(':selected') ? ' selected' : '') +
					($option.is(':disabled') ? ' disabled' : ''))
					.html('<a href="'+data_url+'">'+$option.text()+'</a>')
				);

				if( i && (i % list_next_num === 0) ){
					menuhtmls.push($ul_el[0].outerHTML);
					$ul_el = $('<ul></ul>').addClass("wide left-border");
				}

				if( i == ($options.length - 1) ){
					menuhtmls.push($ul_el[0].outerHTML);
				}
			});

			$dropdown.find('.menulist').html(menuhtmls.join(''));

		}

		return this;
	};
});