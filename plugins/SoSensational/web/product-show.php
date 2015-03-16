 <nav>
  <ul class="pager">
    <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Menu </a></li>
  </ul>
</nav>

<?php
do_action('ss_css');
    global $post;
	global $wpdb;
	$options = get_option( 'ss_settings' );
	$user=wp_get_current_user();
	$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );
                
        $currentUserRole = $user->roles[0];
        
	$post_categories_available =  get_the_terms($advertiser[0]->ID,'ss_category');

        /**
         * Determine the allowed uploads limit based on the user role - brand_role/ boutique_role
         */
	if ($currentUserRole == "brand_role")
	{
		$allowed_products = $options['ss_products_per_brand'];
	} else
	{
		$allowed_products = $options['ss_products_per_boutique'];	
	}

        
	$args = array(
    'post_type' => 'products',
    'showposts' => -1,
    'post_status' => array('publish','pending','draft'),   
	'author' => $user->ID 
	);
         
	?> <ul class="nav nav-pills nav-stacked"> <?php
 
      $my_query = new WP_Query($args);
	  $num_of_products = 0;
	  
    	if($my_query->have_posts()) : while($my_query->have_posts()) : $my_query->the_post(); 
	  $num_of_products = count($my_query->posts);
	  
	  ?>
        
                    <li>
                   		<a href="/add-product/?action=edit&product_id=<?php echo get_the_ID(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                       	 <img class="ss_product_img" src="<?php echo get_post_meta( get_the_ID(), 'ss_product_image', true ); ?>" />  
                         <span class="large_font"><?php the_title(); ?></span>
                     
                		</a>
                    </li>
<?php endwhile; else : ?>
    <div class="alert alert-danger" role="alert">
  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  <span class="sr-only">Error:</span>
	No Products Found 
</div>
	
	<?php endif; ?>
 
</ul>
<?php if ($num_of_products < $allowed_products) { ?><br /><br />
                   		<a href="/add-product/" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">

<button type="button" class="btn btn-default navbar-btn">Add A New Product</button>
<h4>You have entered <?php echo $num_of_products ?> of <?php echo $allowed_products ?> products</h4>
              <!--          	 <img class="ss_category_img" src="<?php echo get_post_meta( get_the_ID(), 'ss_logo', true ); ?>" />    -->
                		</a>
<?php } ?>


    <nav>
  <ul class="pager">
    <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Menu </a></li>
  </ul>
</nav>
