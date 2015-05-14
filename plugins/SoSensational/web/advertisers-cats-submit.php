<?php 

  require( '../../../../wp-load.php');
  //echo "hello";

  if (!is_user_logged_in()){exit();}
  global $wpdb;
  //print_r($wpdb);
  $user=wp_get_current_user(); 

  // var_dump($user->user_login);

  $message = "User with login ". $user->user_login . " submited listing for aprov.";
  $topic = $user->user_login." submited listing";

  wp_mail( get_option( 'admin_notification_email' ), $topic, $message); 


  wp_redirect(SITE_URL.'/ss_directory/?adminmsg=s'); 