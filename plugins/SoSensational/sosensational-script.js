jQuery(document).ready(function() {

	jQuery('#upload_image_video_button').click(function() {
	    uploadID = jQuery(this).prev('input'); /*grab the specific input*/

	 formfield = jQuery('#upload_image_video').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
 
	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
    	uploadID.val(imgurl); /*assign the value to the input*/
	 tb_remove();
	}
		jQuery('#upload_logo_button').click(function() {
			    uploadID = jQuery(this).prev('input'); /*grab the specific input*/

	 formfield = jQuery('#upload_logo').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
 
	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
    uploadID.val(imgurl); /*assign the value to the input*/
	 tb_remove();
	}
	jQuery('#upload_product_image_button').click(function() {
		    uploadID = jQuery(this).prev('input'); /*grab the specific input*/

	 formfield = jQuery('#upload_product_image').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});
 
	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
    uploadID.val(imgurl); /*assign the value to the input*/
	 tb_remove();
	}
	
	
	///////////////////////////////////////////////////
	
	/*
	
	///////////////////////////////////////////////////
	///// COMMENTED OUT BY DAN TAYLOR 04/03/2015 //////
	///////////////////////////////////////////////////
	
	// The number of the next page to load (/page/x/).
	var pageNum = parseInt(pbd_alp.startPage) + 1;
	
	// The maximum number of pages the current query can return.
	var max = parseInt(pbd_alp.maxPages);
	//var max =10;
	// The link of the next page of posts.
	var nextLink = pbd_alp.nextLink;
	
	
	
	/**
	 * Replace the traditional navigation with our own,
	 * but only if there is at least one page of new posts to load.
	 */
	 
	/*
	
	///////////////////////////////////////////////////
	///// COMMENTED OUT BY DAN TAYLOR 04/03/2015 //////
	///////////////////////////////////////////////////
	if(pageNum <= max) {
		// Insert the "More Posts" link.
		jQuery('#infiniteScroll')
			.append('<div class="pbd-alp-placeholder-'+ pageNum +'"></div>')
			.append('<p id="pbd-alp-load-posts"><a href="#">LOAD MORE</a></p>');
			
		// Remove the traditional navigation.
		jQuery('.navigation').remove();
	}
	
	
	/**
	 * Load new posts when the link is clicked.
	 */
	
	/*jQuery('#pbd-alp-load-posts a').click(function() {
	
		// Are there more posts to load?
		if(pageNum <= max) {
		
			// Show that we're working.
			jQuery(this).text('LOADING');
			
			jQuery('.pbd-alp-placeholder-'+ pageNum).load(nextLink + ' .post',
				function() {
					// Update page number and nextLink.
					pageNum++;
					nextLink = nextLink.replace(/\?p_num\=[0-9]?/, '/?p_num\='+ pageNum);
					
					// Add a new placeholder, for when user clicks again.
					jQuery('#pbd-alp-load-posts')
						.before('<div class="pbd-alp-placeholder-'+ pageNum +'"></div>')
					
					// Update the button message.
					if(pageNum <= max) {
						jQuery('#pbd-alp-load-posts a').text('LOAD MORE');
					} else {
						jQuery('#pbd-alp-load-posts').text('');
					}
				}
			);
		} else {
			jQuery('#pbd-alp-load-posts a').append('.');
		}	
		
		return false;
	});*/
	
	
	
	
	
	
	
	
	
	
	
	////////////////////////////////////////////////////
});