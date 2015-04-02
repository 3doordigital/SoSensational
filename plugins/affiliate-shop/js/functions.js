jQuery(document).ready(function($) {
    
	$('.drop_cats').live( 'click' , function( e ) {
		e.preventDefault();
		if( $(this).parent().hasClass('open') ) {
			$(this).children().first().addClass('fa-plus-square-o').removeClass('fa-minus-square-o');
			$(this).parent().removeClass('open');
		} else {
			$(this).children().first().removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
			$(this).parent().addClass('open');
		}
	});
	
    /*$('.searchList').keyup( function(event) {
        //console.log($(this).val());
        var search = $(this).val();
        var id = $(this).attr('rel');
        var list = $('#'+id);
        $('#'+id+' li').css('display', 'block');
        list.each(function(){
            $(this).find('li label').each(function(){
                //console.log($(this));
                //console.log($(this).text());
                var label = $(this).text();
                var re = new RegExp(search, 'gi');
                var match = label.match(re);
                if( match ) {
                    //console.log(label.match(search));
                } else {
                    $(this).parent().css('display', 'none'); 
                    //console.log(label.match(search));
                }
                console.log('Search: '+search+' || Label: '+label+' || Match: '+match);
            });
        });
    });
    */
	
	$('.searchList').keyup( function(event) {
		var searchTerms = $(this).val();
		var ul = $(this).attr('rel');
		$('#'+ul+' .children').show();
		$('#'+ul+' li').each(function() {
		  var hasMatch = searchTerms.length == 0 || $(this).text().toLowerCase().indexOf(searchTerms.toLowerCase()) > 0;
		  $(this).toggle(hasMatch);
		});
	});
	
    $('.add_product_confirm').click(function(event) {
        if($(this).prop('checked')) {
           $('#add_products_submit').removeAttr('disabled'); 
        } else {
           $('#add_products_submit').attr('disabled', 'disabled');                            
        }
    });
    
	// Uploading files
	var file_frame;
	
	jQuery('#upload_image_button').live('click', function( event ){
	
	event.preventDefault();
	
	// If the media frame already exists, reopen it.
	if ( file_frame ) {
	  file_frame.open();
	  return;
	}
	
	// Create the media frame.
	file_frame = wp.media.frames.file_frame = wp.media({
	  title: jQuery( this ).data( 'uploader_title' ),
	  button: {
		text: jQuery( this ).data( 'uploader_button_text' ),
	  },
	  multiple: false  // Set to true to allow multiple files to be selected
	});
	
	// When an image is selected, run a callback.
	file_frame.on( 'select', function() {
	  // We set multiple to false so only get one image from the uploader
	  attachment = file_frame.state().get('selection').first().toJSON();
	
	  // Do something with attachment.id and/or attachment.url here
	  jQuery('#upload_image_button').before('<div style="padding: 2px; padding-bottom: 0; border: solid 2px #ddd; background-color: #fff; display: inline-block;"><img src="'+attachment.url+'" style="width: 150px; height: auto;"></div><br>' );
	  $('#product_image').val( attachment.url );
	});
	
	// Finally, open the modal
	file_frame.open();
	});
	
	$('.prod_filter').on('change', function(){
		var val = $(this).val();
		var type = $(this).attr('id');
		if( val != '' ){
			document.location.href = 'admin.php?page=affiliate-shop/products&prod_'+type+'='+val;    
		}
	});
	
	$('.delete a').on( 'click', function(e) {
		//e.preventDefault();
		if( confirm( 'Are you sure?' ) ) {
			return;
		} else {
			return false;	
		}
	});
	$('#doaction').on( 'click', function(e) {
		if( $('#bulk-action-selector-top').val() == 'delete' ) {
			if( confirm( 'Are you sure?' ) ) {
				return;
			} else {
				return false;	
			}	
		}
	});
});