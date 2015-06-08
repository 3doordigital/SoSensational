jQuery(document).ready(function($) {
	
	var ids;
	var total;
	var counter = 0;
	var loop = 1;
	var success = 0;
	var failed = 0;
	var items = [];
	var per_query;
	
	function update_merchant_feed( thisID, nextID, total ) {
		console.log( thisID+' :: '+nextID+' :: '+total+' :: '+items[thisID].ID+' :: '+items[thisID].aff );
		if( thisID <= total ) {
			var update_feed_data2 = {
				'action'	: 'update_merchant_feed',
				'ID'		: items[thisID].ID,
				'aff'		: items[thisID].aff
			};
			$.post(ajaxurl, update_feed_data2, function(response) {
				var nextFeed = nextID + 1;
				if( thisID == total ) {
					var percent = 100;
					var full_percent = 100;
				} else {
					var percent = loop * per_query;
					var full_percent = percent.toFixed(1);
				}
				console.log( response );
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
				update_merchant_feed( nextID, nextFeed, total );
			}, 'json'); 	
		}
	}
	
	$('.manual_feed_update').click( function( e ) {
		e.preventDefault();
		var update_feed_data = {
				'action'	: 'get_api_merchants'
			};
		$.post(ajaxurl, update_feed_data, function(response) {
			if( response.status == 1 ) {
				$('.prod_update_row').show();
				total = response.total;
				ids = response.items;
				$('.total_update').html(' of '+response.total+' merchants.');
				console.log( 'Total posts: '+response.total );
				per_query = 100 / total;
				var last = response.total - 1;
				counter = 0;
				items = [];
				$.each( ids, function ( i, item ) {
					items[counter] = {
						ID : item.ID,
						aff : item.aff
					};
					counter++;
				});
				update_merchant_feed( 0, 1, last ) 
			}
		}, 'json');	
	});
} );// JavaScript Document