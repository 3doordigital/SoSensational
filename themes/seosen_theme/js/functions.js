jQuery(document).ready( function($) {
	
	$(document).scroll(function() {
		if ( $(this).scrollTop() > 0 ) {
			$('.top.page-scroll').fadeIn( 2000 )	
		} else {
			$('.top.page-scroll').fadeOut( 2000 )	
		}
	});
	
	$('a').each(function() {
	   var a = new RegExp('/' + window.location.host + '/');
	   if(!a.test(this.href)) {
		   $(this).click(function(event) {
			   event.preventDefault();
			   event.stopPropagation();
			   window.open(this.href, '_blank');
		   });
	   }
	});
	
	$('#mega-menu-item-5375').click( function(e) {
		e.preventDefault();
	});
	
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
    
    $('.shareme').sharrre({
      share: {
        facebook: true,
        twitter: true,
        linkedin: true,
        pinterest: true
      },
      buttons: {
        googlePlus: {size: 'tall', annotation:'bubble'},
        facebook: {layout: 'box_count'},
        twitter: {count: 'vertical'},
        digg: {type: 'DiggMedium'},
        delicious: {size: 'tall'},
        stumbleupon: {layout: '5'},
        linkedin: {counter: 'top'},
        pinterest: {media: 'http://sharrre.com/img/example1.png', description: $('#shareme').data('text'), layout: 'vertical'}
      },
      enableHover: false,
      enableCounter: false,
      enableTracking: true
    });
    function centerModal() {
		$(this).css('display', 'block');
		var $dialog = $(this).find(".modal-dialog");
		var offset = ($(window).height() - $dialog.height()) / 2;
		// Center modal vertically in window
		$dialog.css("margin-top", offset);
	}
	
	$('.modal').on('show.bs.modal', centerModal);
	$(window).on("resize", function () {
		$('.modal:visible').each(centerModal);
	});
    
    imagesLoaded( '.deal', function() {
        $('#hot_deals').masonry({
          gutter: 30,
          itemSelector: '.deal'
        });
    });
    
    $(".wp_aff_categories").niceScroll({ cursorcolor: '#ae984a', cursoropacitymin : '1'});
    $(".wp_aff_brands").niceScroll({ cursorcolor: '#ae984a', cursoropacitymin : '1'});
	
}); // closes ready