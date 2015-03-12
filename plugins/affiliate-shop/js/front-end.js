jQuery(document).ready(function($) {
	
	if ( $( ".wp_aff_categories .current-cat" ).length ) {
		var cat_scroll = $('.wp_aff_categories .current-cat').position();
		$('.wp_aff_categories').animate({ scrollTop: cat_scroll.top-25 }, 'slow');
	}
	if ( $( ".wp_aff_brands .current-cat" ).length ) {
		var cat_scroll = $('.wp_aff_brands .current-cat').position();
		$('.wp_aff_brands').animate({ scrollTop: cat_scroll.top-25 }, 'slow');
	}
	
    $('.wp_aff_cat_link break').live( 'click' , function(event) {
        var thislink = $(this);
        //event.preventDefault();
        var data = {
			'action': 'change_faceted_category',
			'cat': $(this).attr('rel')
		};
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajax_object.ajax_url, data, function(response) {
			thislink.after(response);
		});
    });
    
	$('.wp_aff_colours_select').change( function( event ) {
		$('#wp_aff_colour_filter').submit();
	});
	
	$('.wp_aff_sizes_select').change( function( event ) {
		$('#wp_aff_size_filter').submit();
	});
	
	$('.brand_check').change( function( event ) {
		$('#wp_aff_brand_filter').submit();
		console.log('in');
	});
	
	$(document).on( 'click', '.remove', function( event ) {
		event.preventDefault();
		var thislink = $(this);
		var data = {
			'action' : 'remove_facted_element',
			'type' : thislink.attr('data-type'),
			'redirect': window.location.href	
		}
		$.post(ajax_object.ajax_url, data, function(response) {
			window.location.assign(response);
		});
	});
	
    if( typeof( minPrice ) == 'undefined' )
        minPrice = 0;
    
    if( typeof( maxPrice ) == 'undefined' )
        maxPrice = 0;
    
    $( "#slider-range" ).slider({
      range: true,
      min: minPrice,
      max: maxPrice,
      values: valuesPrice,
      slide: function( event, ui ) {
        $( "#amount" ).val( "£" + ui.values[ 0 ] + " - £" + ui.values[ 1 ] );
		$('#price-min').val(ui.values[ 0 ]);
		$('#price-max').val(ui.values[ 1 ]);
      }
    });
    
    $( "#amount" ).val( "£" + $( "#slider-range" ).slider( "values", 0 ) +
      " - £" + $( "#slider-range" ).slider( "values", 1 ) );
    
	$('.colour_filter a').click( function( event ) {
		event.preventDefault();
		var id = $(this).attr('data-id');
		if( $('.hide_check[data-id='+id+']').prop( 'checked') == true ) {
			$('.hide_check[data-id='+id+']').prop('checked', false);
		} else {
			$('.hide_check[data-id='+id+']').prop('checked', true);
		}
		$('#wp_aff_colour_filter').submit();
	});
});