<?php 
do_action('ss_css');
global $wpdb;
$user=wp_get_current_user(); 
//$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM wp_posts where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' and post_status='publish'", OBJECT );
$postID=get_the_ID();
$postID = $advertiser_id;
$advertiser=get_post($postID);
$meta=get_post_meta($postID);

$product_params = array( 'width' => 245, 'crop' => true );       
// IB commented
//$products=$wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_parent = '{$advertiser->ID}' and post_type='products'", OBJECT);

$products=$wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE `post_author` = '{$advertiser->post_author}' AND `post_type`='products' AND (`post_status`='publish' OR post_status='pending')", OBJECT);

//print_r($advertiser->ID);
//print_r($products);


$categories=$wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
    LEFT JOIN {$wpdb->terms} as wpt
    ON wpt.term_id=wptt.term_id
    WHERE wptt.taxonomy='ss_category' ", OBJECT);
?>

    <h1><span><?php echo $advertiser->post_title; ?></span></h1>
    <?php 
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
        } 
    ?>
    
    
<div class="ss_description_single">
       <div class="ss_description_single_left"><?php echo $meta['ss_advertiser_co_desc'][0];?></div>
       <div class="ss_description_single_right">
        <a class="visit_site_ss" href="<?php echo $meta['ss_advertiser_website'][0];?>">Visit <?php echo $advertiser->post_title;?></a>
       </div>
       <div class="ss_clear"></div>
</div>
<div class="ss_clear"></div>
<div class="flexslider">
  <ul class="slides">
  
<?php 
foreach($products as $prod):
?> 
<li class="ss_product_slide"> 
<?php
  $product_meta=get_post_meta($prod->ID);?>
   <a href="<?php echo !isset($product_meta)?'': $product_meta['ss_product_link'][0] ;?>">
   <div class="imageHolderSlide">
   <?php $image_deets = isset($product_meta['ss_product_image'][0]) ? $product_meta['ss_product_image'][0] : get_template_directory_uri() . "/images/upload-artwork.jpg"; ?>
   <?php $image =  bfi_thumb( $image_deets , $product_params ); ?>
    <img src="<?php echo $image ;?>"/>
   </div>
    </a>
    <div class="product_info_slide"> 
        <div class="leftProduct_info_slide">
        	<?php $title = !isset($product_meta)?'':get_the_title($prod->ID); ?> 
            <span class="titleProductInfoSlide"><?php echo substr($title, 0, 30).'...';  ?></span>
            <span class="subtitleProductInfoSlide"><?php echo $advertiser->post_title; ?></span>
        </div>
        <div class="rightProduct_info_slide">
            <div class="amount2">&pound;<?php echo !isset($product_meta)?'': $product_meta['ss_product_price'][0] ;?></div>
            <a href="<?php echo !isset($product_meta)?'': $product_meta['ss_product_link'][0] ;?>" class="button_ss">Buy Now</a>
        </div>
        <div class="ss_clear"></div>
     </div>
	
 
</li>  
  
<?php endforeach;?>
  </ul>
</div><!--end flexslider -->
<div class="ss_clear"></div>


<div class="ss_company_info_left">
    <!-- wpautop() - a Wordpress formatting function from formatting.php. Adds 
    paragraphs automatically, e.g. in a text widget---------------------------->
    <div class="ss_description_company"><?php echo $desc = wpautop($meta['ss_advertiser_desc'][0]); ?>
 	          
              </div>
              <div class="image_description_single">
              <?php if (isset($meta['ss_image_video'][0]))
			  {
              		if (!empty($meta['ss_image_video'][0])) {
               ?>          <img src="<?php echo $meta['ss_image_video'][0];?>" />
  
			 <?php } elseif (!empty($meta['ss_image_video_text'][0])) {  
			 			echo $meta['ss_image_video_text'][0];
			 } }?>
              </div>

              <a href="/brands-and-boutiques/" class="backToBB">Return to Brands & Boutiques</a>

</div><!--company info left -->
<div class="ss_conpany_info_right_wrapper">
<div class="ss_company_info_right">
	
        <img src="<?php echo $meta['ss_logo'][0];?>" />
        <div class="emailWeb">
            Email:
            <a href="mailto:<?php echo $meta['ss_advertiser_email'][0];?>">
                <?php echo $meta['ss_advertiser_email'][0];?>
            </a>
        </div>
        <div class="emailWeb">
            Website: 
            <a href="<?php echo $meta['ss_advertiser_website'][0];?>">
            <?php echo $meta['ss_advertiser_website'][0];?>
           </a>
        </div>
    
        <input style="display: none;" id="geocomplete" class="googleMapInput" type="text" value="<?php echo $meta['ss_advertiser_address'][0];?>" /> 
        <div class="map_canvas"></div>
            <?php $facebookUrl = $meta['ss_advertiser_facebook'][0];?>
            <?php $googleUrl = $meta['ss_advertiser_google'][0];?>
           <?php $pinterestUrl = $meta['ss_advertiser_pinterest'][0];?>
          <?php $twitterUrl = $meta['ss_advertiser_twitter'][0];?>
         <?php $instagramUrl = $meta['ss_advertiser_instragram'][0];?>

         <div class="ss_social_icons">
         	<?php if($facebookUrl != '' && $facebookUrl !== 'http://'){ ?><a href="<?php echo $facebookUrl; ?>" target="_new" class="ss_social_icon facebook"></a><?php } ?>
         	<?php if($pinterestUrl != '' && $pinterestUrl !== 'http://'){ ?><a href="<?php echo $pinterestUrl; ?>" target="_new" class="ss_social_icon pinterest"></a><?php } ?>
         	<?php if($googleUrl != '' && $googleUrl !== 'http://'){ ?><a href="<?php echo $googleUrl;?>" target="_new" class="ss_social_icon gplus"></a><?php } ?>
         	<?php if($twitterUrl != '' && $twitterUrl !== 'http://'){ ?><a href="<?php echo $twitterUrl;?>" target="_new" class="ss_social_icon twitter"></a><?php } ?>
         	<?php if($instagramUrl != '' && $instagramUrl !== 'http://'){ ?><a href="<?php echo $instagramUrl;?>" target="_new" class="ss_social_icon instagram"></a><?php } ?>
         </div>

</div>
 <a class="visit_site_ss" href="<?php echo $meta['ss_advertiser_website'][0];?>">Visit <?php echo $advertiser->post_title;?></a>
</div>


<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="<?php echo SOSENSATIONAL_URL?>/jquery.geocomplete.js"></script>
 <script>
                $(function(){
                
                var options = {
                  map: ".map_canvas",
                  location: "<?php echo $meta['ss_advertiser_address'][0];?>"     
                };
                
                $("#geocomplete").geocomplete(options);
                
              });
            </script>

<link rel="stylesheet" href="<?php echo SOSENSATIONAL_URL?>/flexslider.css" type="text/css">

<script src="<?php echo SOSENSATIONAL_URL?>/jquery.flexslider.js"></script>
<script>
  $(window).load(function() {
  $('.flexslider').flexslider({
    animation: "slide",
    animationLoop: false,
    controlNav: false,
    itemWidth: 265,
    itemMargin: 20,
    prevText: " ",
    nextText: " ",
    slideshow: false
  });
});
</script>
