jQuery(document).ready(function( $ ) {
    $('.newsform_send').submit( function( e ) {
		var thisform = $(this);
		var data2 = $(this).serializeArray();
		console.log(data2);
		e.preventDefault();
		//var email = data2[0].value;
		var data = {
            action: 'wp_news_man_form_submit',
            email: email
        };
		$.post( ajax_object.ajax_url, data, function( response ) {
                if( response.status == 1 ) {
					thisform.html( '<p>You have been sucessfully subscribed. Thank you.</p>' );
				} 
				if( response.status == 0 ) {
					thisform.html( '<p>You have already subscribed to our newsletter. Thank you.</p>' );	
				}
            }, 'json' );
	});
});