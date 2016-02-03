jQuery( document ).ready( function() {
	
	jQuery( '#sl_enable_subdomain' ).click( function(e) {
		if( jQuery(this).is(":checked") ) {
			jQuery( "#subdomain-options" ).fadeIn('fast');
		} else {
			jQuery( "#subdomain-options" ).fadeOut('fast');
		}
	} );
	
} );