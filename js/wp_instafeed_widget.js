(function($){
	$('.widget_wp_if_widget').each(function() {
		var data = {
			action: 'wp_instafeed_widgetcontent',
			widget: $(this).attr('id')
		};

		var $el = $( this );

		$.ajax({
			"url": wp_instafeed.ajaxurl,
			"data": data,
			"success": function( data ) {
				$el.html( data );
			},
			"dataType": "html"
		});
	});
})(jQuery);