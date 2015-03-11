<?php 

require( '../../../../wp-load.php');
//echo "hello";

if (!is_user_logged_in()){exit();}
global $wpdb;
//print_r($wpdb);
$user=wp_get_current_user(); 
$advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );


function upload_user_file( $file = array() ) {

    require_once( ABSPATH . 'wp-admin/includes/admin.php' );

      $file_return = wp_handle_upload( $file, array('test_form' => false ) );

      if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
          return false;
      } else {

          $filename = $file_return['file'];

          $attachment = array(
              'post_mime_type' => $file_return['type'],
              'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
              'post_content' => '',
              'post_status' => 'inherit',
              'guid' => $file_return['url']
          );

          $attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );

          require_once(ABSPATH . 'wp-admin/includes/image.php');
          $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
          wp_update_attachment_metadata( $attachment_id, $attachment_data );

          if( 0 < intval( $attachment_id ) ) {
            return $attachment_id;
          }
      }

      return false;
} //end function

//$advertiserID=$advertiser[0]->ID;

//$countProducts= $wpdb->get_results("SELECT count(ID) as num FROM `{$wpdb->posts}` WHERE post_parent='{$advertiserID}' and post_type='advertisers_cats'",OBJECT);
//if($countProducts[0]->num < get_option('ss_product_number')):

  $image_video_id = upload_user_file( $_FILES['upload_advertisers_cats_image']);
	

  $post1=array(
    'post_title' => $advertiser[0]->post_title,
  //  'post_title' => "title",
    'post_type' => 'advertisers_cats',
    'post_status' => 'pending',
    'post_parent' => $advertiser[0]->ID,
    'post_author' => $user->ID,
    );

  if(isset($_POST['advertisers_cats_id']) && (!empty($_POST['advertisers_cats_id']) && ($_POST['ss_action']=='edit'))){
      $post_id=$_POST['advertisers_cats_id']; 
	  $post1['ID'] = $post_id;
      wp_update_post($post1);
	
  }elseif (isset($_POST['advertisers_cats_id']) && (!empty($_POST['advertisers_cats_id']) && ($_POST['ss_action']=='delete'))) {
      $post_id=$_POST['advertisers_cats_id'];
      wp_delete_post($post_id);
  }else{
    $post_id=wp_insert_post($post1,$wp_error);
  }


   // foreach($_POST['advertiser_category'] as $key=>$value){
		   $term = get_term((int)$_POST['advertiser_category_id'], 'ss_category' );
          $add_cats[]=(int)$_POST['advertiser_category_id'];
       //   if($term->parent){
        //    $add_cats[]=(int)$term->parent;
        //  }
    //}

 $check = wp_set_post_terms($post_id,$_POST['advertiser_category_id'],'ss_category');


  foreach($_POST['sosensational_options'] as $key=>$value)
      {
		 if ($key == "advertisers_cats_link") {
				if (preg_match("#https?://#", $value) === 0) {
 			 $value = 'http://'.$value;
			}	 
		 }
        update_post_meta( $post_id, 'ss_'.$key, $value );
      }
	  
	   $check = wp_set_post_terms($post_id,$_POST['advertiser_category_id'],'ss_category');

 if($image_video_id){
      $image_video=get_post_meta($image_video_id);      
  }
   
 if(!empty($image_video))
  {
       update_post_meta( $post_id, 'ss_advertisers_cats_image', $image_video['_wp_attached_file'][0] );
   }        
  
  
wp_redirect(SITE_URL.'/show-advertisers-cats/');

//endif; // end if product number ...
?>