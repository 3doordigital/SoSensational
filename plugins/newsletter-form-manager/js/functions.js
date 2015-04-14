jQuery(document).ready(function( $ ) {
    $('.cm_api_update').click( function( e ) {
		e.preventDefault();
		var data = {
            action: 'wp_news_man_cm_clients',
            apikey: $('.cm_api_key').val()
        };
		$.post( ajax_object.ajax_url, data, function( data ) {
                $('.cm_client').html( data );
            }, 'html' );
	});
});