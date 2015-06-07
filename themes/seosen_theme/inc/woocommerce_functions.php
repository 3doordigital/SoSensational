<?php
	
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
	
	add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
	add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);
	
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	//remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	//remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
	
	add_filter( 'woocommerce_enqueue_styles', '__return_false' );
	
    
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 20 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 16 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_thumbnails', 14 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 15 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	
	
	function my_theme_wrapper_start() {
	 ?>
     <div class="container">
          <div class="row">
            <div class="col-md-24">
              <?php if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
            } ?>
            </div>
          </div>
          <div class="row">
            <div class="col-md-24" id="content">
             <?php
            }
            
            function my_theme_wrapper_end() {
              ?>
          </div>
        </div>
        </div>
      <?php 
	}
	
	add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

	function custom_override_checkout_fields( $fields ) {
	  unset($fields['billing']['billing_company']);
	  unset($fields['order']['order_comments']);
	  
	  unset($fields['shipping']['shipping_company']);
	  $fields['billing']['billing_address_2']['label'] = 'Address 2';
	  $fields['shipping']['shipping_address_2']['label'] = 'Address 2';
      $fields['billing']['billing_state']['required'] = true;
      $fields['shipping']['shipping_state']['required'] = true;
	  $fields['account']['account_password']['label'] = 'Password';
	  return $fields;
	}
	
	add_filter("woocommerce_checkout_fields", "order_fields");

	function order_fields($fields) {
	
		$order = array(
			"billing_first_name", 
			"billing_last_name",
			"billing_email", 
			"billing_phone",
			"billing_address_1", 
			"billing_address_2",
			"billing_city",
			"billing_state", 
			"billing_postcode", 
			"billing_country"
			 
		);
		$order2 = array(
			"shipping_address_1", 
			"shipping_address_2", 
			"shipping_city", 
			"shipping_state", 
			"shipping_postcode", 
			"shipping_country"
			 
		);
		foreach($order as $field)
		{
			$ordered_fields[$field] = $fields["billing"][$field];
		}
		foreach($order2 as $field)
		{
			$ordered_fields2[$field] = $fields["shipping"][$field];
		}
	
		$fields["billing"] = $ordered_fields;
		$fields["shipping"] = $ordered_fields2;
		return $fields;
	
	}
	
	// Hook in
	add_filter( 'woocommerce_default_address_fields' , 'custom_override_default_address_fields' );
	
	// Our hooked in function - $address_fields is passed via the filter!
	function custom_override_default_address_fields( $address_fields ) {
		 $address_fields['state']['required'] = true;
	
		 return $address_fields;
	}
	
	add_filter('comment_post_redirect', 'redirect_after_comment');
	function redirect_after_comment($location)
	{
		return $_SERVER["HTTP_REFERER"].'#reviews';
	}
	
	add_action( 'comment_post', 'custom_add_comment_rating' , 1 );
	function custom_add_comment_rating( $comment_id ) {
		if ( isset( $_POST['comment-title'] ) ) {
			if ( ! $_POST['comment-title'] ) {
				return;
			}
			add_comment_meta( $comment_id, 'comment_title', $_POST['comment-title'], true );
		}
	}
	
	function tdd_in_cart($product_id) {
		global $woocommerce;
		$i = 0;
		$cart = $woocommerce->cart->get_cart();
		foreach($cart as $values ) {
			if( $product_id == $values['product_id'] ) {
				$i++;
			}
		}	
		if($i ==0) { 
				return false;
			} else {
				return true;
			}
	}
	
	add_action('the_post', 'sb_remove_woocommerce_disqus');
	remove_action('pre_comment_on_post', 'dsq_pre_comment_on_post');
	
	function sb_remove_woocommerce_disqus() {
		global $post, $wp_query;
		if (get_post_type() == 'page') { 
			remove_filter('comments_template', 'dsq_comments_template');
		}
	}	

    add_filter('loop_shop_columns', 'loop_columns');
    if (!function_exists('loop_columns')) {
        function loop_columns() {
            return 3; // 3 products per row
        }
    }

    



add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text' );    // 2.1 +
 
function woo_custom_cart_button_text() {
 
        global $product;

        $product_type = $product->product_type;
    
        switch ( $product_type ) {
            case 'external':
                return __( 'Buy Now', 'woocommerce' );
            break;
            case 'grouped':
                return __( 'View products', 'woocommerce' );
            break;
            case 'simple':
                return __( 'Add to cart', 'woocommerce' );
            break;
            case 'variable':
                return __( 'Select options', 'woocommerce' );
            break;
            default:
                return __( 'Read more', 'woocommerce' );
        }
 
}

function sv_custom_woocommerce_catalog_orderby( $sortby ) {
    unset($sortby['popularity']);
    unset($sortby['rating']);
    $sortby['date'] = 'Sort by: Newest';
    $sortby['price'] = 'Sort by price: Low to High';
    $sortby['price-desc'] = 'Sort by price: High to Low';
    return $sortby;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'sv_custom_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'sv_custom_woocommerce_catalog_orderby' );