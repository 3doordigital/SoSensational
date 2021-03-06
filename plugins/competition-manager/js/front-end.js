jQuery(document).ready( function($) {
    $('.show_comp').click( function(event) {
        event.preventDefault();
        $(this).hide();
        $('#wp_comp_form').show(); 
    });
	
	$('[data-toggle="tooltip"]').tooltip();
    	
    $('#comp_form').submit( function( event ) {
        event.preventDefault();
		var errors = 0;
		var data = $( this ).serialize();
		$('#submit_answer').html('Submitting...').prop('disabled', true);
		
		
		if( errors == 0 ) {
			
			$.post( ajax_object.ajax_url, data, function( data ) {
				console.log(data);
				 if( data.status == 1 ) {
					 //$('#comp_form').html('<p>Thank you for your entry.</p>'); 
					 window.location.assign( data.redirect );
				 } else if( data.status == 0 ) {
					 //$('#comp_form').html('<p>You have already entered this competition. Please check back later for other chances to win.</p>'); 
					 window.location.assign( data.redirect );
				 } else if( data.status == 3 ) {
					 //$('#comp_form').html('<p>You have already entered this competition. Please check back later for other chances to win.</p>'); 
					 //window.location.assign( data.redirect );
					 $('#submit_answer').html('Enter Now').prop('disabled', false).before('<p>Captcha failed, please try again.</p>');
				 }
			}, 'json' );
		}
		
    });
});