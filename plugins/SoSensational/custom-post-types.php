<?php
add_action('init', 'sosensational_custom_post_type');

function sosensational_custom_post_type() {
 $labelsBrands = array( 
        'name' => 'Brands',
        'singular_name' => 'Brand',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Brand',
        'edit_item' => 'Edit Brand',
        'new_item' => 'New Brand',
        'all_items' => 'All Brands',
        'view_item' => 'View Brand',
        'search_items' => 'Search Brand',
        'not_found' => 'No Brand found',
        'not_found_in_trash' => 'No Brand found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Brands'
    );
   $argsBrands = array( 
        'labels' => $labelsBrands,
        'hierarchical' => false,
        'description' => 'Brands',
        'supports' => array('title', 'page-attributes'),
        'taxonomies' => array('ss_category'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
	'register_meta_box_cb' => 'sosensational_brands_add_meta_box',        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array(
			'slug' => 'brands-and-boutiques',
			'with_front'=> false,
                    ),
        'capability_type' => 'post',
        'supports' => array( 'title','author')

    );
    register_post_type('brands', $argsBrands);

     $labelsBoutiques = array( 
        'name' => 'Boutiques',
        'singular_name' => 'Boutique',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Boutique',
        'edit_item' => 'Edit Boutique',
        'new_item' => 'New Boutique',
        'all_items' => 'All Boutiques',
        'view_item' => 'View Boutique',
        'search_items' => 'Search Boutique',
        'not_found' => 'No Boutique found',
        'not_found_in_trash' => 'No Boutique found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Boutiques',

    );

   $argsBoutiques = array( 
        'labels' => $labelsBoutiques,
        'hierarchical' => false,
        'description' => 'Boutiques',
        'supports' => array('title', 'page-attributes'),
        'taxonomies' => array('ss_category'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'register_meta_box_cb' => 'sosensational_boutiques_add_meta_box',        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array(
			'slug' => 'brands-and-boutiques',
			'with_front'=> false,
		),
        'capability_type' => 'post',
        'supports' => array( 'title','author')
    );

    register_post_type('boutiques', $argsBoutiques);

      $labelsProducts = array( 
        'name' => 'Products',
        'singular_name' => 'Product',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Product',
        'edit_item' => 'Edit Product',
        'new_item' => 'New Product',
        'all_items' => 'All Products',
        'view_item' => 'View Product',
        'search_items' => 'Search Product',
        'not_found' => 'No Product found',
        'not_found_in_trash' => 'No Product found in Trash',
        'parent_item_colon' => 'boutiques',
        'menu_name' => 'Products'
    );

   $argsProducts = array( 
        'labels' => $labelsProducts,
        'hierarchical' => false,
        'description' => 'Products',
        'supports' => array('title', 'page-attributes','author'),
        'taxonomies' => array('post_tag'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'register_meta_box_cb' => 'sosensational_products_add_meta_box',        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type('products', $argsProducts);
	
	$labelsAdvertisersCats = array( 
        'name' => 'Advertisers Cats',
        'singular_name' => 'Advertisers Cat',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Advertiser Cat',
        'edit_item' => 'Edit Advertiser Cat',
        'new_item' => 'New Advertiser Cat',
        'all_items' => 'All Advertiser Cat',
        'view_item' => 'View Advertiser Cat',
        'search_items' => 'Search Advertiser Cat',
        'not_found' => 'No Ad Cat found',
        'not_found_in_trash' => 'No Ad Cat found in Trash',
        'parent_item_colon' => '',
        'menu_name' => 'Advertisers Cats',

    );

   $argsAdvertisersCats = array( 
        'labels' => $labelsAdvertisersCats,
        'hierarchical' => false,
        'description' => 'Advertisers Cats',
        'supports' => array('title', 'page-attributes'),
        'taxonomies' => array('ss_category'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'register_meta_box_cb' => 'sosensational_advertisers_cats_add_meta_box',        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
         'rewrite' => array(
			'slug' => 'brands-boutiques',
			'with_front'=> false,
		),
        'capability_type' => 'post',
        'supports' => array( 'title','author')
    );

    register_post_type('advertisers_cats', $argsAdvertisersCats);

	
}
add_action('init', 'sosensational_custom_taxonomy',0);

function sosensational_custom_taxonomy() {
    // Add new taxonomy, make it hierarchical 
    $labels = array(
        'name'              => _x( 'SoSensational Categories', 'taxonomy general name' ),
        'singular_name'     => _x( 'SoSensational Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Categories' ),
        'all_items'         => __( 'All Categories' ),
        'parent_item'       => __( 'Parent Category' ),
        'parent_item_colon' => __( 'Parent Category:' ),
        'edit_item'         => __( 'Edit Category' ),
        'update_item'       => __( 'Update Category' ),
        'add_new_item'      => __( 'Add New Category' ),
        'new_item_name'     => __( 'New SoSensational Category' ),
        'menu_name'         => __( 'SoSensational Category' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'brands-and-boutiques', 'with_front' => false ),
    );
        register_taxonomy( 'ss_category', array('brands'), $args );

}

// Add term page
function sosensational_taxonomy_add_new_meta_field($term) {
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('SoSensational', plugins_url( 'SoSensational/sosensational-script.js'), array('jquery'));
    // this will add the custom meta field to the add new term page
    $t_id = isset($term->term_id) ? $term->term_id : "" ;
    
    $term_meta = get_option( "taxonomy_$t_id" );
    
    if ( ! key_exists('ss_cat_priority', $term_meta)) {
        $term_meta['ss_cat_priority'] = 20;
    }
    
    if ( ! key_exists('ss_aff_categories', $term_meta)) {
        $term_meta['ss_aff_categories'] = false;
    }    
    
    ?>
    <div class="form-field">
       <tr valign="top">
        <th scope="row">Upload Image or Video</th>
         <td>
            <img src="<?php echo $term_meta['ss_cat_image']; ?>"></img>
        <td>
        <td><label for="upload_image_video">
        <input id="upload_image_video" type="text" size="36" name="term_meta[ss_cat_image]" value="<?php echo $term_meta['ss_cat_image']; ?>" />
        <input id="upload_image_video_button" type="button" value="Upload Image or Video" />
        <br />Enter an URL or upload an media file.
        </label></td>
        </tr>
        <tr valign="top">
            <th scope="row">Order</th>
            <td>
                <select name="term_meta[ss_cat_priority]">
                    <?php for($x=0; $x<21; $x++) { ?>
                        <option value="<?php echo $x; ?>" <?php selected($term_meta['ss_cat_priority'], $x); ?> ><?php echo $x; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Display on shop categories:</th>
            <td>
                <?php 
                $shopCategories = get_terms('wp_aff_categories');
                $sortedCategories = sortShopCategories($shopCategories);   
                echo '<div id="affiliate-shop-categories">';
                    echo produceMenu($shopCategories, $term_meta['ss_aff_categories']);
                echo '</div>';
                ?>
            </td>
        </tr>
    </div>
<?php
}
add_action( 'ss_category_add_form_fields', 'sosensational_taxonomy_add_new_meta_field', 10, 2 );
add_action( 'ss_category_edit_form_fields', 'sosensational_taxonomy_add_new_meta_field', 10, 2 );

function save_taxonomy_custom_fields( $term_id ) {  
    if ( isset( $_POST['term_meta'] ) ) {  
        $t_id = $term_id;  
        $term_meta = get_option( "taxonomy_term_$t_id" );  
        $cat_keys = array_keys( $_POST['term_meta'] );  
            foreach ( $cat_keys as $key ){  
            if ( isset( $_POST['term_meta'][$key] ) ){  
                $term_meta[$key] = $_POST['term_meta'][$key];  
            }  
        }  
        //save the option array  
        update_option( "taxonomy_$t_id", $term_meta );  
    }  
} 

// Save the changes made on the taxonomy, using our callback function  
add_action( 'edited_ss_category', 'save_taxonomy_custom_fields', 10, 2 );  
add_action( 'create_ss_category', 'save_taxonomy_custom_fields', 10, 2 );

function sosensational_brands_add_meta_box(){
    add_meta_box('sosensational_meta_box_brand_details', 'Brand Details', 'sosensational_brands_meta_box_details_content', 'brands', 'normal', 'default');
    remove_meta_box('pageparentdiv', 'brands', 'side');

}
function sosensational_boutiques_add_meta_box(){
    add_meta_box('sosensational_meta_box_boutique_details', 'Boutique Details', 'sosensational_boutiques_meta_box_details_content', 'boutiques', 'normal', 'default');
    remove_meta_box('pageparentdiv', 'boutiques', 'side');

}

function sosensational_advertisers_cats_add_meta_box(){
    add_meta_box('sosensational_meta_box_advertisers_cats_details', 'Advertisers Cats Details', 'sosensational_advertisers_cats_meta_box_details_content', 'advertisers_cats', 'normal', 'default');
    remove_meta_box('pageparentdiv', 'advertisers_cats', 'side');
}

function sosensational_products_add_meta_box(){
    add_meta_box('sosensational_meta_box_product_details', 'Product Details', 'sosensational_products_meta_box_details_content', 'products', 'normal', 'default');
    remove_meta_box('pageparentdiv', 'products', 'side');
}
function sosensational_brands_meta_box_details_content($post)
{
  		global $post;
        $meta=get_post_meta($post->ID);   
		wp_nonce_field('custom_form_action', 'SoSensational_noncename');
    echo '<tr valign="top">
        <th scope="row">Upload Logo</th>
         <td>
            <img src="'.$meta['ss_logo'][0].'"></img>
        <td>
        <td><label for="upload_logo">
        <input id="upload_logo" type="text" size="36" name="upload_logo" value="" />
        <input id="upload_logo_button" type="button" value="Upload Logo" />
        <br />Enter an URL or upload media file.
        </label></td>
        </tr>';
    echo '<p><label for="advertiser_email">Email</label><input type="text" name="sosensational_options[advertiser_email]" id="advertiser_email" class="widefat" value="'.$meta['ss_advertiser_email'][0].'" /></p>';
    echo '<p><label for="advertiser_website">Website</label><input type="text" name="sosensational_options[advertiser_website]" id="advertiser_website" class="widefat" value="'.$meta['ss_advertiser_website'][0].'" /></p>';
    echo '<p><label for="advertiser_address">Address</label><input type="text" name="sosensational_options[advertiser_address]" id="advertiser_address" class="widefat" value="'.$meta['ss_advertiser_address'][0].'" /></p>';
    echo '<p><label for="advertiser_desc">Brand description</label><textarea rows="5" name="sosensational_options[advertiser_desc]" id="advertiser_desc" class="widefat">'.$meta['ss_advertiser_desc'][0].'</textarea></p>';
    echo '<p><label for="advertiser_co_desc">Company description</label><textarea  rows="6"  name="sosensational_options[advertiser_co_desc]" id="advertiser_co_desc" class="widefat">'.$meta['ss_advertiser_co_desc'][0].'</textarea></p>';
    echo '<p><label for="advertiser_facebook">Facebook page</label><input type="text" name="sosensational_options[advertiser_facebook]" id="advertiser_desc" class="widefat" value="'.$meta['ss_advertiser_facebook'][0].'" /></p>';
    echo '<p><label for="advertiser_pinterest">Pinterest page</label><input type="text" name="sosensational_options[advertiser_pinterest]" id="advertiser_desc" class="widefat" value="'.$meta['ss_advertiser_pinterest'][0].'" /></p>';
    echo '<p><label for="advertiser_google">Google +</label><input type="text" name="sosensational_options[advertiser_google]" id="advertiser_google" class="widefat" value="'.$meta['ss_advertiser_google'][0].'" /></p>';
    echo '<p><label for="advertiser_twitter">Twitter</label><input type="text" name="sosensational_options[advertiser_twitter]" id="advertiser_twitter" class="widefat" value="'.$meta['ss_advertiser_twitter'][0].'" /></p>';
    echo '<p><label for="advertiser_instagram">Instagram</label><input type="text" name="sosensational_options[advertiser_instagram]" id="advertiser_instagram" class="widefat" value="'.(isset($meta['ss_advertiser_instagram'][0]) ? $meta['ss_advertiser_instagram'][0] : null ).'" /></p>';

    echo '<p><label for="advertiser_featured">Is featured ?</label><input type="checkbox" name="sosensational_options[advertiser_featured]" value="1"></p>';
    echo '<tr valign="top">
        <th scope="row">Upload Image or Video</th>
        <td>
        <img src="'.isset($meta['ss_image_video'][0]) ? $meta['ss_image_video'][0] : "" .'" />';
	
  echo '<tr valign="top">
        <th scope="row">Upload Image or Video</th>
        <td>';
		$video_img = (isset($meta['ss_image_video'][0])) ? $meta['ss_image_video'][0] : null;
		echo '<img src="'.$video_img.'">';
        echo '<td>
        <td><label for="upload_image_video">
        <input id="upload_image_video" type="text" size="36" name="upload_image_video" value="" />
        <input id="upload_image_video_button" type="button" value="Upload Image or Video" />
        <br />Enter an URL or upload media file.<br>
        </label></td>
        </tr>';
	 	   echo '<br /><br><input type="text" name="sosensational_options[ss_image_video_text]" id="advertiser_image_video_text" class="widefat" value="'.isset($meta['ss_image_video_text'][0]) ? $meta['ss_image_video_text'][0] : "".'" />';
  	
  echo '<p><label for="advertiser_image_video_text">Embeded Video Text</label><textarea rows="6" name="sosensational_options[advertiser_image_video_text]" id="advertiser_image_video_text" class="widefat">'.(isset($meta['ss_image_video_text'][0]) ? $meta['ss_image_video_text'][0] : null).'</textarea></p>';
 
    }
function sosensational_boutiques_meta_box_details_content($post)
{
   global $post;
    $meta=get_post_meta($post->ID);   
    wp_nonce_field('custom_form_action', 'SoSensational_noncename');
    echo '<tr valign="top">
        <th scope="row">Upload Logo</th>
         <td>
            <img src='.$meta['ss_logo'][0].'></img>
        <td>
        <td><label for="upload_logo">
        <input id="upload_logo" type="text" size="36" name="upload_logo" value="" />
        <input id="upload_logo_button" type="button" value="Upload Logo" />
        <br />Enter an URL or upload media file.
        </label></td>
        </tr>';
    echo '<p><label for="advertiser_email">Email</label><input type="text" name="sosensational_options[advertiser_email]" id="advertiser_email" class="widefat" value="'.$meta['ss_advertiser_email'][0].'" /></p>';
    echo '<p><label for="advertiser_website">Website</label><input type="text" name="sosensational_options[advertiser_website]" id="advertiser_website" class="widefat" value="'.$meta['ss_advertiser_website'][0].'" /></p>';
    echo '<p><label for="advertiser_address">Address</label><input type="text" name="sosensational_options[advertiser_address]" id="advertiser_address" class="widefat" value="'.$meta['ss_advertiser_address'][0].'" /></p>';
     echo '<p><label for="advertiser_desc">Boutique description</label><textarea rows="5" name="sosensational_options[advertiser_desc]" id="advertiser_desc" class="widefat">'.$meta['ss_advertiser_desc'][0].'</textarea></p>';
    echo '<p><label for="advertiser_co_desc">Company description</label><textarea rows="6" name="sosensational_options[advertiser_co_desc]" id="advertiser_co_desc" class="widefat">'.$meta['ss_advertiser_co_desc'][0].'</textarea></p>';
    echo '<p><label for="advertiser_facebook">Facebook page</label><input type="text" name="sosensational_options[advertiser_facebook]" id="advertiser_desc" class="widefat" value="'.$meta['ss_advertiser_facebook'][0].'" /></p>';
    echo '<p><label for="advertiser_pinterest">Pinterest page</label><input type="text" name="sosensational_options[advertiser_pinterest]" id="advertiser_desc" class="widefat" value="'.$meta['ss_advertiser_pinterest'][0].'" /></p>';
    echo '<p><label for="advertiser_google">Google +</label><input type="text" name="sosensational_options[advertiser_google]" id="advertiser_google" class="widefat" value="'.$meta['ss_advertiser_google'][0].'" /></p>';
    echo '<p><label for="advertiser_twitter">Twitter</label><input type="text" name="sosensational_options[advertiser_twitter]" id="advertiser_twitter" class="widefat" value="'.(isset($meta['ss_advertiser_twitter'][0]) ? $meta['ss_advertiser_twitter'][0] : null).'" /></p>';
    echo '<p><label for="advertiser_instagram">Instagram</label><input type="text" name="sosensational_options[advertiser_instagram]" id="advertiser_instagram" class="widefat" value="'.(isset($meta['ss_advertiser_instagram'][0]) ? $meta['ss_advertiser_instagram'][0] : null) .'" /></p>';

    echo '<p><label for="advertiser_featured">Is featured ?</label><input type="checkbox" name="sosensational_options[advertiser_featured]" value="1"></p>';

    echo '<tr valign="top">
        <th scope="row">Upload Image or Video</th>
        <td>';
		$video_img = (isset($meta['ss_image_video'][0])) ? $meta['ss_image_video'][0] : null;
		echo '<img src="'.$video_img.'">';
        echo '<td>
        <td><label for="upload_image_video">
        <input id="upload_image_video" type="text" size="36" name="upload_image_video" value="" />
        <input id="upload_image_video_button" type="button" value="Upload Image or Video" />
        <br />Enter an URL or upload media file.<br><br>
        </label></td>
        </tr>';
		   echo '<br><br/><input type="text" name="sosensational_options[ss_image_video_text]" id="advertiser_image_video_text" class="widefat" value="'.isset($meta['ss_image_video_text'][0]) ? $meta['ss_image_video_text'][0] : "".'" />';
  	
  echo '<p><label for="advertiser_image_video_text">Embeded Video Text</label><textarea rows="6" name="sosensational_options[advertiser_image_video_text]" id="advertiser_image_video_text" class="widefat">'.(isset($meta['ss_image_video_text'][0]) ? $meta['ss_image_video_text'][0] : null).'</textarea></p>';
 
 			
}
function sosensational_products_meta_box_details_content($post)
{
        global $post;
    $meta=get_post_meta($post->ID);      
    wp_nonce_field('custom_form_action', 'SoSensational_noncename');
    echo '
        <img src="'.(isset($meta['ss_product_image'][0]) ? $meta['ss_product_image'][0] : "").'"></img>
        <tr valign="top">
        <th scope="row">Upload Image</th>
        <td><label for="upload_product_image">
        <input id="upload_product_image" type="text" size="36" name="upload_product_image" value="" />
        <input id="upload_product_image_button" type="button" value="Upload Image" />
        <br />Enter an URL or upload media file.
        </label></td>
        </tr>';
    echo '<p><label for="product_price">Price</label><input type="text" name="sosensational_options[product_price]" id="product_price" class="widefat" value="'.(isset($meta['ss_product_price'][0]) ? $meta['ss_product_price'][0] : "").'" /></p>';
    echo '<p><label for="product_link">Link</label><input type="text" name="sosensational_options[product_link]" id="product_link" class="widefat" value="'.(isset($meta['ss_product_link'][0]) ? $meta['ss_product_link'][0] : "") .'" /></p>';

    }


function sosensational_advertisers_cats_meta_box_details_content($post)
{
        global $post;
    $meta=get_post_meta($post->ID);      
    wp_nonce_field('custom_form_action', 'SoSensational_noncename');
    echo '
        <img src="'.$meta['ss_advertisers_cats_image'][0].'"></img>
        <tr valign="top">
        <th scope="row">Upload Image</th>
        <td><label for="upload_product_image">
        <input id="upload_product_image" type="text" size="36" name="upload_advertisers_cats_image" value="" />
        <input id="upload_product_image_button" type="button" value="Upload Image" />
        <br />Enter an URL or upload media file.
        </label></td>
        </tr>';
    echo '<p><label for="advertisers_cats_description">Description</label><input type="text" name="sosensational_options[advertisers_cats_description]" id="advertisers_cats_description" class="widefat" value="'.$meta['ss_advertisers_cats_description'][0].'" /></p>';
    echo '<p><label for="advertisers_cats_link">Link</label><input type="text" name="sosensational_options[advertisers_cats_link]" id="advertisers_cats_link" class="widefat" value="'.$meta['ss_advertisers_cats_link'][0].'" /></p>';

    }

add_action( 'save_post', 'sosensational_save_meta_box_data' );

function sosensational_save_meta_box_data($post_id){

    // Check if our nonce is set.
    if ( ! isset( $_POST['SoSensational_noncename'] ) ) 
    {            
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['SoSensational_noncename'], 'custom_form_action' ) ) 
    {
        return;
    }
    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) 
    {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) 
        {
            return;
        }
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
    {
        return;
    }

    // Make sure that it is set.
    if ( ! isset( $_POST['sosensational_options'] ) ) 
    {
        return;
    }

    // Update the meta field in the database.
    foreach($_POST['sosensational_options'] as $key=>$value)
    {
            update_post_meta( $post_id, 'ss_'.$key, $value );
    }

    switch($_POST['post_type'])
        {
            case 'boutiques':
            case 'advertisers_cats':

            if(!empty($_POST['upload_advertisers_cats_image']))
            {
                update_post_meta( $post_id, 'ss_advertisers_cats_image', $_POST['upload_advertisers_cats_image'] );
            }
             break;

            case 'brands':
                if(!empty($_POST['upload_logo']))
                {
                    update_post_meta( $post_id, 'ss_logo', $_POST['upload_logo'] );
                }
                if(!empty($_POST['upload_image_video']))
                {
                    update_post_meta( $post_id, 'ss_image_video', $_POST['upload_image_video'] );
                }

                break;
            case 'products':
            if(!empty($_POST['upload_product_image']))
            {
                update_post_meta( $post_id, 'ss_product_image', $_POST['upload_product_image'] );
            }
                break;


        }

}


function sosensational_scripts() {
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('SoSensational', plugins_url( 'SoSensational/sosensational-script.js'), array('jquery'));

}
 
function sosensational_styles() {
    wp_enqueue_style('thickbox');
}
if(isset($_GET['post_type']) && !empty($_GET['post_type'])){
    switch($_GET['post_type']){
        case 'brands':
        case 'boutiques':
		case 'advertisers_cats':
        case 'products':

            add_action('admin_print_scripts', 'sosensational_scripts');
            add_action('admin_print_styles', 'sosensational_styles');    
        break;

    }
}
if(isset($_GET['post']) && !empty($_GET['post'])){

$post_type=get_post_type($_GET['post']);
    switch($post_type){
        case 'brands':
        case 'boutiques':
		case 'advertisers_cats':		
        case 'products':

            add_action('admin_print_scripts', 'sosensational_scripts');
            add_action('admin_print_styles', 'sosensational_styles');    
        break;

    }
}
// if (isset($_GET['post_type']) && $_GET['post_type'] == 'brands') {
//         add_action('admin_print_scripts', 'sosensational_scripts');
//         add_action('admin_print_styles', 'sosensational_styles');  
// }

// add_action('save_post', 'sosensational_save_postdata');
// function sosensational_save_postdata($postID) {
//     if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
//     if(!wp_verify_nonce($_POST['sosensational_noncename'], basename( __FILE__ ))) { return; }
//     if($_POST['post_type'] == 'shops') {
//         if(!current_user_can('edit_post', $postID)) { return; }
//     } else { return; }  
//     update_post_meta($postID, 'sosensational_options', $_POST['sosensational_options']);
//     $categories = get_the_category($postID);
//     if($categories) {
//         foreach($categories as $category) {
//             update_post_meta($postID, 'sosensational_category-'.$category->cat_ID, $_POST['sosensational_category-'.$category->cat_ID]);
//         }
//     }
// }



