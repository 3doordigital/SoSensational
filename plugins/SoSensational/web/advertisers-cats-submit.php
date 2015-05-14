<?php 

  require( '../../../../wp-load.php');
  //echo "hello";

  if (!is_user_logged_in()){exit();}
  global $wpdb;
  //print_r($wpdb);
  $user=wp_get_current_user(); 
  $advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );
  $pending_products = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where post_type='products' and post_status='pending' and post_author='{$user->ID}' ", OBJECT );

  // var_dump($advertiser[0]->post_type);

  $message = "<p>User with login ". $user->user_login . " submited listing for aproval:. </p><p> Company type: "; 
  $message .= $advertiser[0]->post_type. "</p><p> Company name: ".$advertiser[0]->post_title. "</p><p>";
  $message .= "Edit company profile: <a href='".get_edit_post_link( $advertiser[0]->ID )."'>link</a></p></br>";
  if(isset($pending_products)) {
    $message .= "<p>User pending product:</p>";
    foreach ($pending_products as $product) {
      $message .= "<p><a href='".get_edit_post_link( $product->ID )."'>".$product->post_title."</a></p>";
    };
  }

  $topic = $user->user_login." submited listing";

  add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
  wp_mail( get_option( 'admin_notification_email' ), $topic, $message); 


  wp_redirect(SITE_URL.'/ss_directory/?adminmsg=s'); 