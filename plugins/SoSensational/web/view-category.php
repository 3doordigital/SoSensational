<?php 
do_action('ss_css');
//require(ABSPATH . 'wp-load.php');
$cat_params = array( 'width' => 367, 'height' => 240, 'crop' => true ); 

global $wpdb;
$category_id = isset($ss_sub_cat_id) ? $ss_sub_cat_id : "";

if(!empty($ss_cat_id)):

	$category_id = preg_replace('/[^-a-zA-Z0-9_]/', '', $ss_cat_id);

	$category=$wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
    LEFT JOIN {$wpdb->terms} as wpt
    ON wpt.term_id=wptt.term_id
    WHERE wptt.taxonomy='ss_category' 
    AND wpt.term_id='{$category_id}'", OBJECT);
    $mainChildren = get_term_children($category_id, get_query_var('taxonomy'));

	$term_meta = get_option( "taxonomy_$category_id" );

	
?>

    <h1><span><?php echo $category[0]->name;?></span></h1>
    <?php 
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
        } 
    ?>
    
    
    
<div class="category_ss_title_under">
    <span class="left_ss"><?php echo $category[0]->description;?></span>
    <p class="ss_description category_ss">
        <b class="ss_trigger_dropdown">Brands & Boutique</b>
        <span class="dropdown_ss_bb">
            <a class="showBBA" href="#">Show All</a>
            <a class="showBBA" href="?p_type=brands">Just Brands</a>
            <a class="showBBA" href="?p_type=boutiques">Just Boutiques</a>
        </span>
    </p>
    <div class="ss_clear"></div> 
</div>
<?php
$categories=$wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
    LEFT JOIN {$wpdb->terms} as wpt
    ON wpt.term_id=wptt.term_id
    WHERE wptt.taxonomy='ss_category' ", OBJECT);     


?>
  <div class="row">
        <?php 
        $counterCategories = 1;   
        $counterColor = 1;
        foreach($categories as $category):
        /* moramo linkat kategorije s pageom za tu kategoriju, tamo ce se ispisivat butici i brendovi. 
        samo stavis link na taj page i dodas ?ss_cat_id=$category->term_id
        */
            if($category->parent==$category_id):?>

                 <div class="col-md-8 fadebox showme animated fadeIn" style="visibility: visible;">
                    <?php $children = get_term_children($category->term_id, get_query_var('taxonomy')); // get children 
                          $term_meta = get_option( "taxonomy_$category->term_id" );
                          
?>
                        <a href="<?php echo get_site_url().'/brands-and-boutiques/'. $ss_cat . '/' . $category->slug.'/'; ?>" class="aHolderImgSS">
                            <img  src="<?php echo $term_meta['ss_cat_image']; ?>" class="img-responsive" />
                              <div class="<?php if($counterColor % 2): echo 'whitebar ss_whitebar'; else: echo 'blackbar ss_blackbar'; endif; ?>" style="display:block">
                                        <h2><span> <?php echo $category->name;?></span></h2>
                                </div>
                         </a>
                </div>
<?php  $counterColor++; $counterCategories++;  if($counterCategories == 4): $counterCategories=1; endif; ?>
  <?php if($counterCategories == 1): echo '</div><div class="row margintop">';  endif; ?>
          <?php 
            endif;
        endforeach; ?>

 <div class="ss_clear" ></div>
</div>

<?php endif; ?> 

   


<div class="row margintop">
<?php
  
                    
//$post_type = array('brands','boutiques');

$users= null;
$post_type = array('advertisers_cats');
if(isset($_GET['p_type'])){
	if ($_GET['p_type'] == "brands")
	{
		$user_query = new WP_User_Query( array( 'role' => 'brand_role' , 'fields' => 'ID' ) );
	}
	if ($_GET['p_type'] == "boutiques")
	{
		$user_query = new WP_User_Query( array( 'role' => 'boutique_role' , 'fields' => 'ID' ) );
	}

	$users = $user_query->get_results();

}

if(isset($_GET['p_num'])){
    $paged=$_GET['p_num'];
} else 
{
	$paged= 1;	
}



if(empty($mainChildren)):
//$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

if ($paged == 1)
{
	$post_per_page = 12;	
}
else {
	$post_per_page = 12;			
}

$args = array(
    'post_type' => $post_type,
    'post_per_page' => $post_per_page,
    'showposts' => $post_per_page,
    'post_status' => 'publish',
	'author__in'  => $users,
    'tax_query' => array(
        array(
            'taxonomy' => 'ss_category',
            'field' => 'id',
            'terms' => $category_id
        )
    ),
        'paged' =>$paged
);

	$term_meta = get_term_by("id",$category_id,"ss_category");

	
    ?>     
	     <h1><span><?php echo $term_meta->name; ?></span></h1>
    <?php 
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
        } 
    ?>

    <div class="category_ss_title_under">
    <span class="left_ss"><?php echo $term_meta->description; ?> </span>
    <p class="ss_description category_ss">
        <b class="ss_trigger_dropdown">Brands & Boutique</b>
        <span class="dropdown_ss_bb">
            <a class="showBBA" href="#">Show All</a>
            <a class="showBBA" href="?p_type=brands">Just Brands</a>
            <a class="showBBA" href="?p_type=boutiques">Just Boutiques</a>
        </span>
    </p>
    <div class="ss_clear"></div> 
</div>
    <div id="infiniteScroll" class="infiniteScroll">
    <?php
      $my_query = new WP_Query($args);
     	    $counterColor = 1;
            $counterRows = 1;
			$max = $my_query->max_num_pages;
// Add some parameters for the JS.
		$p_num = (isset($_GET['p_num']) ? $_GET['p_num'] : 2);

?>
<script type='text/javascript'>
/* <![CDATA[ */
var pbd_alp = {"startPage":"1","maxPages":"<? echo $max; ?>","nextLink":"<? echo $_SERVER['REQUEST_URI'] ?>?p_num=<? echo $p_num; ?>"};
/* ]]> */
</script>
<?php


        while ($my_query->have_posts()) : $my_query->the_post();
		 ?>
                    <div class="post col-md-8 ss_border fadebox ss_advertisers_cats showme animated fadeIn <?php if($counterRows == 3) { echo 'breakRowClass';} ?>" style="visibility: visible;">
<?php
$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$my_query->post->post_author}' ", OBJECT );
$post_name = isset($advertiser[0]->post_name) ? $advertiser[0]->post_name : null;
// echo "<pre>"; print_r($advertiser); echo "</pre>"; 

?>

                            <a href="<?php echo get_site_url().'/brands-and-boutiques/' . $post_name; ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>" class="aHolderImgSS">
                               
								<?php $image =  bfi_thumb( get_post_meta( get_the_ID(), 'ss_advertisers_cats_image', true ), $cat_params ); ?>

                                <img src="<?php echo $image; ?>" class="img-responsive" />   
                           
                                    <?php if($counterColor % 2): echo '<div class="whitebar ss_whitebar" style="display: block;">'; else: echo '<div class="blackbar ss_blackbar" style="display: block;">'; endif; ?> 
                                        <h2><span> <?php the_title(); ?></span></h2>              
                                    </div>
                             </a>
                              <div class="ss_clear"></div>
							 <div class="ss_advertisers_cats_description">
                             		<?
						$description = get_post_meta( get_the_ID(), 'ss_advertisers_cats_description', true );
						$description = strip_tags($description);
				
						if (strlen($description) > 180) {
									
						// truncate string
						$stringCut = substr($description, 0, 180);
									
						// make sure it ends in a word so assassinate doesn't become ass...
						$description = substr($stringCut, 0, strrpos($stringCut, ' ')); 
					}
						echo $description;
				?>
                                <a href="<?php echo get_site_url().'/brands-and-boutiques/' . $post_name; ?>">Read More</a>
	
                             </div>
                             
                             <a class="button_ss large_ss" target="_new" href="<?php  echo get_post_meta( get_the_ID(), 'ss_advertisers_cats_link', true );?>">Visit Website</a>

                    </div>
                          
            <?php  $counterColor++;  $counterRows++; if($counterRows == 4) { $counterRows = 1;}
        endwhile;
        //wp_pagenavi( array( 'query' => $my_query ) );
 endif;//end if cat have children

 ?>
 
    </div>
</div>
<div class="ss_clear"></div>

<?php
$args2 = array(
    'post_type' => $post_type,
    'showposts' => -1,
    'post_status' => 'publish',
     'meta_query' => array(
        array(
            'key' => 'ss_advertiser_featured',
            'value' => 1
        )
    ),
     'tax_query' => array(
        array(
            'taxonomy' => 'ss_category',
            'field' => 'id',
            'terms' => $category_id
        )
    )
); ?>
<div class="ss_clear" style="padding-bottom: 50px;"></div>
<h2 class="category_ss_title_under">
    <span>Featured</span>
</h2><div class="row margintop">

        <?php 
   $posts2 = get_posts($args2);
    $i=1;
   foreach($posts2 as $featured): ?>
                <div class="col-md-8 fadebox showme animated fadeIn" style="visibility: visible;">
                        <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                            <?php echo get_the_title($featured); ?>
                            <img class="ss_category_img" src="<?php echo get_post_meta( get_the_ID(), 'ss_logo', true ); ?>" />   
                        </a>
                 </div>
<?php 
if($i<get_option('ss_adv_number')){ $i++; }else{ break;}
endforeach;?>
<div class="ss_clear"></div>
</div>
