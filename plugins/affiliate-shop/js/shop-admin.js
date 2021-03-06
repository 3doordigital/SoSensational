
jQuery(function($) {
        $.xhrPool = [];
        $.xhrPool.abortAll = function() {
            $(this).each(function(i, jqXHR) {   //  cycle through list of recorded connection
                jqXHR.abort();  //  aborts connection
                $.xhrPool.splice(i, 1); //  removes from list by index
            });
        }
        $.ajaxSetup({
            beforeSend: function(jqXHR) { $.xhrPool.push(jqXHR); }, //  annd connection to list
            complete: function(jqXHR) {
                var i = $.xhrPool.indexOf(jqXHR);   //  get index for current connection completed
                if (i > -1) $.xhrPool.splice(i, 1); //  removes from list by index
            }
        });
    })

jQuery(document).ready(function($) {
    
	$('#adv_search_check').click( function(e) {
		if( $(this).is(':checked') ) {
			$('#advanced_search').show();
			$('#wp_aff_search').attr('disabled', true);	
		} else {
			$('#advanced_search').hide();
			$('#wp_aff_search').removeAttr('disabled');
		}
	});
	
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
				if( response.status == 1 ) {
					if( response.new == 1 ) {
						thislink.addClass('active');
					} else {
						thislink.removeClass('active');
					}
				}
			}, 'json');
	});

    $('.ajax_new_in').live( 'click', function( e ) {
        e.preventDefault();
        var $this = $(this);
        var data = {
            'action': 'ajax_new_in',
            'post': $(this).attr('data-item'),
            'var' : $(this).attr('data-action')
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
            console.log( response );
            if( response.status == "success" ) {
                if( response.new == 1 ) {
                    $this.addClass('active');
                } else {
                    $this.removeClass('active');
                }
            }
        }, 'json');
    });

    $('.ajax_new_in_single_product').live( 'click', function( e ) {
        e.preventDefault();
        var $this = $(this);
        var data = {
            'action': 'ajax_new_in_single_product',
            'post': $(this).attr('data-item'),
            'var' : $(this).attr('data-action')
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
            console.log( response );
            if( response.status == "success" ) {
                if( response.new == 1 ) {
                    $this.addClass('active');
                } else {
                    $this.removeClass('active');
                }
            }
        }, 'json');
    });

    $("#postbox-container-2 table.form-table :checkbox").click(function() {
        var $this = $(this);
        var checkboxClass = $this.attr('class');
        var siblings = $(":checkbox." + checkboxClass);
        var prop = $this.prop('checked');
        siblings.each(function() {
            $this = $(this);
            $this.prop('checked', prop);
        }).bind(prop);

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
	
	var ids;
	var total;
	var counter = 0;
	var loop = 1;
	var success = 0;
	var failed = 0;
	var items = [];
	var per_query;
	
	function update_product( thisID, nextID, total ) {

		console.log( thisID+' :: '+nextID+' :: '+total+' :: '+items[thisID].id+' :: '+items[thisID].prod_id+' :: '+items[thisID].url );
		if( thisID <= total ) {
			var ajax_update_product = {
				'action'	: 'ajax_update_product',
				'id'		: items[thisID].id,
				'prod_id'	: items[thisID].prod_id,
				'url'		: items[thisID].url
			};
			
			$.post(ajaxurl, ajax_update_product, function(response) {

				var nextProd = nextID + 1;
				if( thisID == total ) {
					var percent = 100;
					var full_percent = 100;
					$('#submit').removeAttr( 'disabled' );	
					$('.manual_update').html('Manual Update').removeAttr( 'disabled' );	
				} else {
					var percent = loop * per_query;
					var full_percent = percent.toFixed(1);
				}
				$('.update_percent').html( full_percent+'%' );
				$('#update_progress').css( 'width', percent+'%' );
				if( response.status == 1 ) {
					success++;
					$('.update_success').html( success );
				} else {
					failed++;
					$('.update_fail').html( failed );
				}
				loop++;
				update_product( nextID, nextProd, total );
			}, 'json' );
		}
		
	}
	$('.stop_update').live( 'click', function(e) {
		e.preventDefault();
		$.xhrPool.abortAll();
		$('.stop_update').html('Manual Update').removeClass('stop_update').addClass('manual_update');
		$('#submit').removeAttr( 'disabled' );	
	});
	$('.manual_update').live( 'click',  function(e) {
		e.preventDefault();
		$(this).html('Stop Update <i class="fa fa-circle-o-notch fa-spin"></i>').removeClass('manual_update').addClass('stop_update');
		$('#submit').attr( 'disabled', 'disabled' );
		$('.prod_update_row').show();
		
		var ajax_update_get_count_data = {
			'action'	: 'ajax_update_get_count'
		};
		var total;
		var ids;
		var counter = 0;
		$.post(ajaxurl, ajax_update_get_count_data, function(response) {
			if( response.status == 1 ) {
				total = response.total;
				ids = response.ids;
				$('.total_update').html(' of '+response.total+' products.');
				per_query = 100 / total;
				var last = total - 1;
				counter = 0;
				items = [];
				console.log( last );
				$.each( ids, function ( i, item ) {
					items[counter] = {
						'id' : item.id,
						'prod_id': item.prod_id,
						'url': item.url
					};
					counter++;
				});
				update_product( 0, 1, last ) 
			}
		}, 'json');
				
	});
	
});
