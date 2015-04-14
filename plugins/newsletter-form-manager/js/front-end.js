jQuery(document).ready(function( $ ) {
    $('.newsform_send').submit( function( e ) {
		e.preventDefault();
		alert($(this).children('input[type=email]').val());
		var data = {
            action: 'wp_news_man_form_submit',
            email: $(this).children('input[type=email]').val()
        };
		$.post( ajax_object.ajax_url, data, function( data ) {
                $('.cm_client').html( data );
            }, 'html' );
	});
});