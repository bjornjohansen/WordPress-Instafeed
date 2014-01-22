(function($){

	var supports_localStorage = supports_localStorage_check();

	function supports_localStorage_check() {
		try {
			return 'localStorage' in window && window['localStorage'] !== null;
		} catch (e) {
			return false;
		}
	}

	var update_localStorage = function( key, val ) {
		if ( ! supports_localStorage ) {
			return;
		}

		localStorage.setItem( key + '_ts', ( (new Date).getTime() / 1000 ) );
		localStorage.setItem( key , val );
	}

	var update_view = function( html, $el ) {
		$el.append( html );
	}

	var get_fresh = function ( req_data, $el ) {
		$.ajax({
			"url": wp_instafeed.ajaxurl,
			"data": req_data,
			"success": function( ret_data ) {
				update_view( ret_data, $el );
				update_localStorage( JSON.stringify( req_data ), ret_data );
			},
			"dataType": "html"
		});
	}

	$('.widget_wp_if_widget').each(function() {

		var $el = $( this );

		var req_data = {
			action: 'wp_instafeed_widgetcontent',
			widget: $el.attr('id')
		};

		if ( supports_localStorage ) {

			var key = JSON.stringify( req_data );

			var ls_ts = localStorage.getItem( key + '_ts' );

			if ( ls_ts && ( (new Date).getTime() / 1000 ) - ls_ts < wp_instafeed.client_cachetime ) {
				var ret_data = localStorage.getItem( key );
				update_view( ret_data, $el );
			} else {
				get_fresh( req_data, $el );
			}

		} else {
			get_fresh( req_data, $el );
		}

	});
})(jQuery);