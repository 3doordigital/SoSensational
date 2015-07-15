jQuery(document).ready( function($) {
	imagesLoaded( '.fadebox', function() {
			   $('.fadebox').css('visibility', 'visible');
			   jQuery('.fadebox').addClass("hideme").viewportChecker({
				classToAdd: 'showme animated fadeIn',
				classToRemove: 'hideme',
				offset: 100,
				repeat: false, 
				callbackFunction: function(elem, action){ $('.showme .row').fadeIn(1500); $('.showme .whitebar, .showme .blackbar').fadeIn(1500); },
				scrollHorizontal: false 
			   });
	   });
	$('.showme .row').fadeIn(5000);
	$('.fadebox').hover(
		function() {
			$(this).find('img').animate({ opacity: 0.7 }, 700);
		},
		function() {
			$(this).find('img').animate({ opacity: 1 }, 700);
		});
	
	$('#homenewsletter button').hover(function() {
		$('#homenewsletter input').addClass('newshover');
	}, function() {
		$('#homenewsletter input').removeClass('newshover');
	});
}); // closes ready