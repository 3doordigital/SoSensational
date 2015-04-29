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
	
	$('.ajax_sticker').live( 'click', function( e ) {
			e.preventDefault();
			var thislink = $(this);
			var data = {
				'action': 'ajax_update_sticker',
				'post': $(this).attr('data-item'),
				'var' : $(this).attr('data-action')
			};
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function(response) {
				console.log( response );
				if( response.status == 1 ) {
					if( response.new == 1 ) {
						thislink.addClass('active');
					} else {
						thislink.removeClass('active');
					}
				}
			}, 'json');
	});
	
	$('.remove-product').live( 'click', function( e ) {
		e.preventDefault();
		
		var thislink = $(this);
		var prod = thislink.attr('rel');
		
		if( $( '#product-skip-'+prod ).val() == 0 ) {
			thislink.html('Restore Product');
			thislink.parent().parent().children('.inside').hide();
			$( '#product-skip-'+prod ).val(1);
		} else {
			thislink.html('Remove Product');
			thislink.parent().parent().children('.inside').show();
			$( '#product-skip-'+prod ).val(0)
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
	
	$('.prod_filter').on('change', function( e ){
		e.preventDefault();
		var thislink = $(this);
		var data = {
			'action'	: 'admin_product_filter',
			'val'		: thislink.val(),
			'type' 		: thislink.attr('id'),
			'referrer'	: document.location.href
		};
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {
			console.log( response );
			if( response.status == 1 ) {
				var url = decodeURIComponent( response.url );
				document.location.href = url; 
			}
		}, 'json');
		
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
	
	function update_product( id, aff, title ) {
		
		id = typeof id !== 'undefined' ? id : null;
		aff = typeof aff !== 'undefined' ? aff : null;
		title = typeof title !== 'undefined' ? title : null;
		
		var ajax_update_product = {
			'action'	: 'ajax_update_product',
			'id'		: id,
			'aff'		: aff,
			'title'		: title
		};
		
		$.post(ajaxurl, ajax_update_product, function(response) {
			console.log( response );
			if( response.status == 1 ) {
				return true;	
			} else {
				return false;	
			}
		});
		
	}
	
	$('.manual_update').click( function(e) {
		e.preventDefault();
		$(this).html('<i class="fa fa-circle-o-notch fa-spin"></i>').attr( 'disabled', 'disabled' );
		$('#submit').attr( 'disabled', 'disabled' );
		$('.prod_update_row').show();
		
		var ajax_update_get_count_data = {
			'action'	: 'ajax_update_get_count'
		};
		var total;
		var ids;
		
		$.post(ajaxurl, ajax_update_get_count_data, function(response) {
			if( response.status == 1 ) {
				total = response.total;
				ids = response.ids;
				
				var per_query = 100 / total;
		
				$.each( ids, function ( i, item ) {
					percent = per_query * i;
					update_product( ids[i].prod_id, null, ids[i].title );
					$('#update_progress').css( 'width', percent+'%' );	
				});
				
			}
		}, 'json');
				
	});
	
});
