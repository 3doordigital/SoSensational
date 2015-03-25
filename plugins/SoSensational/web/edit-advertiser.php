<?php 
function check_cat($cat_to_find,$cat_list)
{
	if (!empty($cat_list)) {
	foreach ($cat_list as $this_cat)
	{
		if ($this_cat->slug == $cat_to_find)
		{ return true; } 	
	}
	}
	return false;
}

if (!is_user_logged_in()){exit();}
do_action('ss_css');
global $wpdb;
$user=wp_get_current_user(); 
$options = get_option( 'ss_settings' );
?>
<nav>
  <ul class="pager">
    <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Menu </a></li>
  </ul>
</nav>
<?php


$error_code=isset($_GET['error_code']) ? $_GET['error_code'] : "";
$success_code=isset($_GET['success_code']) ? $_GET['success_code'] : "";

if (!empty($success_code)) :
  switch($success_code)
        {
            case '1':
				$display_message = "Details Updated Successfully";
			break;
		}
?>



<div class="alert alert-success" role="alert"><? echo $display_message; ?></div>

<?php
elseif (!empty($error_code)) :
  switch($error_code)
        {
            case '1':
				$display_message = "You Have Selected Too Many Categories";
			break;
		}

?>
<div class="alert alert-danger" role="alert">
  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  <span class="sr-only">Error:</span>
	<? echo $display_message ?>
</div>
<?php
endif; 




// Old Code by Bruno!
//$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM wp_posts where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' and post_status='publish'", OBJECT );
$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );

if (empty($advertiser)) { 
	 
	$user = wp_get_current_user();
	if( in_array( "brand_role", (array) $user->roles )) {
		$post_type ="brands";
	} else {
		$post_type ="boutiques";
	}

  $post1=array(
    'post_title' => "Untitled Company",
    'post_type' => $post_type,
    'post_status' => 'draft',
    'post_author' => $user->ID,
    );

   $post_id=wp_insert_post($post1);
	
	$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );

} // end check advertiser exists


$meta=get_post_meta($advertiser[0]->ID);      
$products=$wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_parent = '{$advertiser[0]->ID}' and post_type='products'", OBJECT);
// old Code By Bruno
//$categories=$wpdb->get_results( "SELECT * FROM wp_term_taxonomy wptt 
//    LEFT JOIN wp_terms as wpt
//   ON wpt.term_id=wptt.term_id
//   WHERE wptt.taxonomy='ss_category' ", OBJECT);

$categories=$wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
    LEFT JOIN {$wpdb->terms} as wpt
   ON wpt.term_id=wptt.term_id
   WHERE wptt.taxonomy='ss_category' ", OBJECT);
    


$post_categories =  get_the_terms( $advertiser[0]->ID,'ss_category');


$attachments = get_posts( array(
        'post_type' => 'attachment',
        'posts_per_page' => -1,
        'post_parent' => $advertiser[0]->ID,
                ) );

//		print_r($attachments);
		
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$class = "post-attachment mime-" . sanitize_title( $attachment->post_mime_type );
				$thumbimg = wp_get_attachment_link( $attachment->ID, 'thumbnail-size', true );
//				echo '<li class="' . $class . ' data-design-thumbnail">' . $thumbimg . '</li>';
			}
			
		}
	
	
	
?>
        <?php //echo isset($advertiser[0]->post_type) ? $advertiser[0]->post_type : "" .' '. isset($advertiser[0]->post_title) ? $advertiser[0]->post_title : "";?>
<form action="<?php echo SOSENSATIONAL_URL?>/web/edit-advertiser-action.php" method="POST" enctype="multipart/form-data" >
    <input type="hidden" name="post_type" value="<?php echo $advertiser[0]->post_type ?>"/>
    <input type="hidden" name="post_id" value="<?php echo $advertiser[0]->ID ?>"/>
<?php
	if ($advertiser[0]->post_type == "brands")
	{
		$cats_allowed = $options['ss_categories_per_brand'];
	} else {		
		$cats_allowed = $options['ss_categories_per_boutique'];			
	}
?>

<div class="media">
  <div class="media-left media-middle">
    <a href="#">
     <img src="<?php echo ! empty($meta['ss_logo'][0]) ? $meta['ss_logo'][0] : plugin_dir_url(__FILE__) . "../img/placeholders/advertiser-logo-280x185.jpg"; ?>" />
    </a>
  </div>
  <div class="media-body">
    <h4 class="media-heading">Company Logo</h4>
       <input id="upload_logo" type="file" name="upload_logo" size="50" value="" />
  </div>
</div>



<br />
 
        
        

<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon2">Company Name</span>
  <input type="text" value="<?php echo isset($meta['ss_advertiser_co_name'][0]) ? $meta['ss_advertiser_co_name'][0] : "";?>" name="sosensational_options[advertiser_co_name]" class=" form-control"  value="<?php echo $meta['ss_advertiser_co_name'][0];?>" placeholder="Company Name" aria-describedby="basic-addon1">
</div>
<br />
        
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon2">Email</span>
  <input type="text" value="<?php echo isset($meta['ss_advertiser_email'][0]) ? $meta['ss_advertiser_email'][0] : "";?>" name="sosensational_options[advertiser_email]" class=" form-control"  value="<?php echo $meta['ss_advertiser_email'][0];?>" placeholder="Email Address" aria-describedby="basic-addon1">
</div>
<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Website Address:</span>
	<input type="text" name="sosensational_options[advertiser_website]" id="advertiser_website" value="<?php echo isset($meta['ss_advertiser_website'][0]) ? $meta['ss_advertiser_website'][0] : "";?>" class="form-control" aria-describedby="basic-addon1" />
</div>

<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Company Address:</span>
	<input type="text"  name="sosensational_options[advertiser_address]" id="advertiser_address"  value="<?php echo isset($meta['ss_advertiser_address'][0]) ? $meta['ss_advertiser_address'][0] : "";?>" class="form-control" aria-describedby="basic-addon1" />
</div>

<br />
<div class="input-group">
    <span class="input-group-addon input-width" id="basic-addon1">1 Line Company Description:<br /><div id="oneLineDescCounter"></div></span>
	<textarea name="sosensational_options[advertiser_co_desc]" id="advertiser_co_desc"  class="form-control" aria-describedby="basic-addon1" /><?php echo isset($meta['ss_advertiser_co_desc'][0]) ? $meta['ss_advertiser_co_desc'][0] : "";?></textarea>
   
</div>


<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Full Brand Description:<br /><div id="fullDescCounter"></div></span>
	<textarea name="sosensational_options[advertiser_desc]" id="advertiser_desc"  class="form-control" aria-describedby="basic-addon1" /><?php echo isset($meta['ss_advertiser_desc'][0]) ? $meta['ss_advertiser_desc'][0] : "";?></textarea>
</div>



<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Facebook Page:</span>
	<input type="text" name="sosensational_options[advertiser_facebook]" id="advertiser_desc"  value="<?php echo isset($meta['ss_advertiser_facebook'][0]) ? $meta['ss_advertiser_facebook'][0] : "";?>" class="form-control" aria-describedby="basic-addon1" />
</div>


<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Google Plus Page:</span>
	<input type="text"   name="sosensational_options[advertiser_google]" id="advertiser_google"  value="<?php echo isset($meta['ss_advertiser_google'][0]) ? $meta['ss_advertiser_google'][0] : "";?>" class="form-control" aria-describedby="basic-addon1" />
</div>


<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Pinterest Page:</span>
	<input type="text"   name="sosensational_options[advertiser_pinterest]" id="advertiser_pinterest" value="<?php echo isset($meta['ss_advertiser_pinterest'][0]) ? $meta['ss_advertiser_pinterest'][0] : "";?>" class="form-control" aria-describedby="basic-addon1" />
</div>
<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Twitter Page:</span>
	<input type="text"   name="sosensational_options[advertiser_twitter]" id="advertiser_twitter" value="<?php echo isset($meta['ss_advertiser_twitter'][0]) ? $meta['ss_advertiser_twitter'][0] : "";?>" class="form-control" aria-describedby="basic-addon1" />
</div>

<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Instragram Page:</span>
	<input type="text"   name="sosensational_options[advertiser_instragram]" id="advertiser_instragram" value="<?php echo isset($meta['ss_advertiser_instragram'][0]) ? $meta['ss_advertiser_instragram'][0] : "";?>" class="form-control" aria-describedby="basic-addon1" />
</div>

<br />
<fieldset>
<div class="media">
  <div class="media-left media-middle">
    <a href="#">
     <img src="<?php echo ! empty($meta['ss_image_video'][0]) ? $meta['ss_image_video'][0] : plugin_dir_url(__FILE__) . "../img/placeholders/promo-image-735x380.jpg";?>" />
    </a>
  </div>
  <div class="media-body">
    <h4 class="media-heading">Promo Image</h4>
      <input onchange="disableVideoBoxes()" id="upload_image_video" type="file" size="50" name="upload_image_video" value="" />
      <input type="submit" name="delete_video_image" value="Delete Image" />
  </div>
</div>

<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Youtube Embedded Code:</span>
	<textarea onchange="disableVideoBoxes()" name="sosensational_options[image_video_text]" id="image_video_text"  class="form-control" aria-describedby="basic-addon1" /><?php echo isset($meta['ss_image_video_text'][0]) ? $meta['ss_image_video_text'][0] : "";?></textarea>
</div>
</div>
<br /><br />
<div class="clearfix"></div>

<br /><br /><hr />
<div class="clearfix"></div>









   
        <h3>Please choose up to <?php echo $cats_allowed ?> categories</h3>
       
<div id="num_checked"></div>
       <?php $count =0; ?>
   
        <?php foreach($categories as $category):

		$count++;
        if($category->parent==0):
			if ($count > 1) { echo "</div>"; }
		?>
        <br />
<div class="clearfix"></div>
         <div class="row"><h3><?php echo $category->name; ?></h3>
          <?php  foreach($categories as $subcategory):
					//echo $subcategory->slug;
					$selected = ""; 
					 if (check_cat($subcategory->slug,$post_categories)) {
						 
						$selected = "checked";
					 }
					
                    if($subcategory->parent==$category->term_id){?>
                   
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-addon">
                        <input type="checkbox" onchange="countChecked()" name="advertiser_category[]" value="<?php echo $subcategory->term_id;?>" <?php echo $selected ?>>
    </span>
      <input type="text" class="form-control" value="<?php echo $subcategory->name;?>" aria-label="...">
    </div><!-- /input-group -->
  </div><!-- /.col-lg-6 -->
  
  

  
                        
                  <?php
                    }
                endforeach;
            endif;
        ?>
    <?php endforeach;?>
    
     </div>
 <hr />
    </p>
  <button type="submit" class="button_ss_small btn ">Update Your Details</button>

    </form>
    <br /><br />
    <nav>
  <ul class="pager">
    <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Menu </a></li>
  </ul>
</nav>


<script>
var countChecked = function() {
var n = jQuery( "input:checked" ).length;
//jQuery( "#num_checked" ).text( "number of categories chosen " + n + (n === 1 ? " is" : " are") + " checked!" );

jQuery( "#num_checked" ).text( "number of categories chosen -" + n );
if (n >= <?php echo $cats_allowed ?>) { 
		
  jQuery(':checkbox:not(:checked)').prop('disabled', true);  
  } else  {
  jQuery(':checkbox:not(:checked)').prop('disabled', false); 
 }
};
countChecked();

function disableVideoBoxes()
{
		
	var image_video_text = jQuery("#image_video_text").val();
	var image_video = jQuery("#upload_image_video").val();

	if (image_video == "")
	{
		var image_video = "<?php  echo isset($meta['ss_image_video'][0]) ? $meta['ss_image_video'][0] : "" ?>"
	}

	if ((image_video_text == "") && (image_video ==""))
	{
		// Ennable both boxes
	  jQuery('#upload_image_video').prop('disabled', false); 
	  jQuery('#image_video_text').prop('disabled', false); 

	}
	if ((image_video_text != "") && (image_video ==""))
	{
		// Disable image video
	
	  jQuery('#upload_image_video').prop('disabled', true); 

	} 
	if ((image_video_text == "") && (image_video != ""))
	{
		// Disable video
  
	  jQuery('#image_video_text').prop('disabled', true); 

	}
}

disableVideoBoxes();

jQuery( "#upload_image_video_text" ).on( "change", disableVideoBoxes );
jQuery( "#upload_image_video" ).on( "change", disableVideoBoxes );

//jQuery('#advertiser_co_desc').keyup(function () {
//
//  var max = 235;
//  var len = jQuery(this).val().length;
//  if (len >= max) {
//    jQuery('#charNum').text('You have reached the limit');
//  } else {
//    var char = max - len;
//    jQuery('#charNum').text(char + ' characters left');
//  }
//});

// Form inside a form

</script>