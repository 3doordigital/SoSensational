<?php
if (!is_user_logged_in()){exit();}
do_action('ss_css');
global $wpdb;
$user=wp_get_current_user(); 
$options = get_option( 'ss_settings' );


$categories=$wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
    LEFT JOIN {$wpdb->terms} as wpt
   ON wpt.term_id=wptt.term_id
   WHERE wptt.taxonomy='ss_category' ", OBJECT);

//$term_meta = get_term_by("id",$category_id,"ss_category");
//echo "<pre>";
//print_r($options);

$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );
$advertisers_type = $advertiser[0]->post_type;
$post_categories_available =  get_the_terms($advertiser[0]->ID,'ss_category');
//print_r($advertisers_type);
if ($advertisers_type == "brands")
{
	$allowed_categories = $options['ss_categories_per_brand'];
} else 
{
	$allowed_categories = $options['ss_categories_per_boutique'];	
}
//print_r($post_categories_available);
//echo "</pre>";

function show_select_for_cats($post_categories_available,$slug)
{
	//global $post_categories_available;

	$found = "";	
	$to_return = '<select ondataavilable="DisableOptions()" onchange="DisableOptions()" class="advertisers_cat form-control" name="advertiser_category_id">';
	$to_return .= '<option value="">Please Select A Category</option>';
	foreach ($post_categories_available as $pca)
	{
		//	echo $pca->slug;
		$to_return .= '<option ';
		if ($slug == $pca->term_id) { $to_return .= ' selected="selected" '; $found = 1;}
		$to_return .= ' value="' . $pca->term_id .'"';
		$to_return .= '>'. $pca->name .'</option>';  		
	}
	
	$to_return .= "</select> ";
	
	return $to_return;
}

?>
    <nav>
  <ul class="pager">
    <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Main Menu </a></li>
  </ul>
</nav>


<?
echo  '<div class="row">';

if (empty($post_categories_available)) {

	?>
    <div class="alert alert-danger" role="alert">
  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  <span class="sr-only">Error:</span>
	You have yet to selected any categories within your profile <br />
    <a href="/edit-advertiser/">Click here to edit your profile</a>  
</div>
    
    <?
	
} else {
	

foreach ($post_categories_available as $cat)
{
	$tax_to_use[] = $cat->slug;		
}
	// obtain post info


	$args = array(
    'post_type' => 'advertisers_cats',
    'showposts' => -1,
    'post_status' => array('publish','pending','draft'),
	'author' => $user->ID, 
//	   'tax_query' => array(
//      		 array(
//         	'taxonomy' => 'ss_category',
//         	'field' => 'slug',
//         	'terms' => $this_tax,
		
//        	)
//		),
	);
//print_r($args);	
  $my_query = new WP_Query($args);
    //echo "<pre>";
	//print_r(count($my_query->posts));
	if (count($my_query->posts) < $allowed_categories) {
		$cats_to_make =  $allowed_categories - count($my_query->posts) ;
	//	echo "Need to create some cats: ".$cats_to_make;
		  $post1=array(
 		   'post_title' => $advertiser[0]->post_title,
   		 	'post_type' => 'advertisers_cats',
    		'post_status' => 'pending',
    		'post_parent' => $advertiser[0]->ID,
    		'post_author' => $user->ID,
    		);
		$i = 1;
		while ($i <= $cats_to_make) {
			$i++;  // the printed value would be
   			$post_id=wp_insert_post($post1);		
   		}

		
	 // run the query again		
	  $my_query = new WP_Query($args);
		
	}
	
	
	//print_r($my_query ); 
	//echo "</pre>"; 
     
    	if($my_query->have_posts()) : while($my_query->have_posts()) : $my_query->the_post(); 
		
	//	$term_meta = get_term_by("id",$category_id,"ss_category");
		$meta=get_post_meta( get_the_ID());

		?>
        
              


	

  <div class="col-sm-12 col-md-12">
    <div class="thumbnail ss_fixheight">
    <? $this_image = get_post_meta( get_the_ID(), 'ss_advertisers_cats_image', true );
		if ($this_image == "") { $this_image = get_template_directory_uri() . "/images/upload-artwork.png"; } 
		
		?>
        
		
      <img src="<?php echo $this_image?>" alt="">
      <div class="caption wide">
     <form action="<?php echo SOSENSATIONAL_URL?>/web/advertisers-cats-action.php" method="POST" enctype="multipart/form-data" >
 <div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Post Status:</span>
 <? if (get_post_status( get_the_ID()) == "publish") { echo '<span  aria-describedby="basic-addon1"  class="form-control label-success">Published</span>'; } else { echo '<span class=" form-control label-warning">Awaiting Review</span>'; } ?>
</div>

<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Product Category:</span>
 <? 
				
				$this_terms = wp_get_post_terms(get_the_ID(), 'ss_category');
				
				echo show_select_for_cats($post_categories_available ,isset($this_terms[0]->term_id) ? $this_terms[0]->term_id : ""); 
				 ?>
                 
                 </div>
         <br/>     
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Upload Image:</span>
      <input class="form-control" id="upload_advertisers_cats_image" type="file" size="50" name="upload_advertisers_cats_image" value="" />
</div>
<br />
      <div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Description</span>
      <textarea  class="form-control" name="sosensational_options[advertisers_cats_description]" id="advertiser_address" /><?php echo isset($meta['ss_advertisers_cats_description'][0]) ? $meta['ss_advertisers_cats_description'][0] : "";?></textarea>
   </div>
<br />
      <div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Site Link</span>
          <input type="text" name="sosensational_options[advertisers_cats_link]" id="advertiser_address" class="form-control" value="<?php echo isset($meta['ss_advertisers_cats_link'][0]) ? $meta['ss_advertisers_cats_link'][0] : "";?>" /></td>
         </div>
        <br />

        <p><button type="submit" class="button_ss_small" href="#" >Save</button></p>
  
       		 <input type="hidden" name="ss_action" value="edit" />     
        	<input type="hidden" name="advertisers_cats_id" value="<?php echo get_the_ID(); ?>" />  
        	<input type="hidden" name="post_type" value="advertisers_cats"/>
        
        
 		</form>
      </div>
    </div>
  </div>


    
	<?php endwhile; else : ?>
<p>No products found</p>
creating product;



<?php endif; ?>
<?
	

echo "</div>";

?>
    <nav>
  <ul class="pager">
    <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Main Menu </a></li>
  </ul>
</nav>

<script>
//jQuery('.advertisers_cat').on('change', function() {
  //  var val = this.options[this.selectedIndex].value;
  //  jQuery('select').not(this).children('option').filter(function() {
 //      return this.value === val;
 //   }).remove();
//});

//disableOnLoad();

function disableOnLoad()
{
	   var arr=[];
      jQuery("select option:selected").each(function()
              {
                  arr.push(jQuery(this).val());
              });

    jQuery("select option").filter(function()
        {
             
              return jQuery.inArray(jQuery(this).val(),arr)>-1;
   }).attr("disabled","disabled");   



}

jQuery("select").change(function()
                   {

         //disable selected values

                   });
DisableOptions();

function DisableOptions()
{

 var myOpt = [];
    jQuery("select").each(function () {
        myOpt.push(jQuery(this).val());
    });
    jQuery("select").each(function () {
        jQuery(this).find("option").prop('hidden', false);
        var sel = jQuery(this);
        jQuery.each(myOpt, function(key, value) {
            if((value != "") && (value != sel.val())) {
                sel.find("option").filter('[value="' + value +'"]').prop('hidden', true);
            }
        });
    }); 

}



</script>


<? } ?>

