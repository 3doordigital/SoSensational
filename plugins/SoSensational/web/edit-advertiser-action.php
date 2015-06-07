<?php

require( '../../../../wp-load.php');
(int)$post_id=$_POST['post_id'];
$post_type=$_POST['post_type'];
$options = get_option( 'ss_settings' );
$error_code = "";
$isAjaxPreview = false;

/**
 * Determine if the current request is an AJAX request for preview
 * This is a double check on two global arrays
 */
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' &&
    $_POST['ajaxPreview'] == true) {
    
    $isAjaxPreview = true;      
    
}

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

		//	print_r($attachment_data);
          if( 0 < intval( $attachment_id ) ) {
            return $attachment_id;
          }
      }

      return false;
}    


	/* ============================= End of function =============================== */


        if(!empty($_FILES['upload_logo'])){
          $logo_id = upload_user_file( $_FILES['upload_logo']);
		  
	        if($logo_id) {
                    $logo = get_post_meta($logo_id);  
                    $logo = wp_get_attachment_image_src( $logo_id, 'ss_image' );  
   		 }

        }
        if(!empty($_FILES['upload_image_video'])){
          $image_video_id = upload_user_file( $_FILES['upload_image_video']);
			 if($image_video_id){
				$image_video=get_post_meta($image_video_id);      
			}

        }



    $no_of_cats = count(isset($_POST['advertiser_category']) ? $_POST['advertiser_category'] : 0);
    $cats_good = false;
	
    if($post_type == 'boutiques'){
       if($no_of_cats <= $options['ss_categories_per_boutique']){
         $cats_good = true;
       }
    }
   if($post_type == 'brands'){
      if($no_of_cats <= $options['ss_categories_per_brand']){
        $cats_good = true;
      }
   }
   
    if($cats_good) {  
        $add_cats = array();    
	if (isset($_POST['advertiser_category'])) {
            foreach($_POST['advertiser_category'] as $key=>$value) {
                $term = get_term((int)$value, 'ss_category' );
                $add_cats[] = (int)$value;
            }
	}        
 	wp_set_post_terms($post_id, $add_cats, 'ss_category');
    } else {
        $error_code = "1"; // Too many cats		
    }
                       
    
    foreach($_POST['sosensational_options'] as $key => $value) {
        if ($key == 'promo_image_link' && ! $value) {
            $value = $_POST['sosensational_options']['advertiser_website'];
        }
        update_post_meta( $post_id, 'ss_'.$key, $value );
        if ($key == "advertiser_co_name") {
                $my_post = array(
                    'ID'           => $post_id,
                    'post_title' => $value,
                    'post_status' => 'publish'
                );
            // Update the post into the database
            wp_update_post( $my_post ); 
        }	
    }
    
    if ($_POST['post_type'] === 'boutiques' || $_POST['post_type'] === 'brands') {
        if(!empty($logo)) {
           update_post_meta( $post_id, 'ss_logo', $logo[0] );
        }
        if(!empty($image_video)) {
            update_post_meta( $post_id, 'ss_image_video', $image_video['_wp_attached_file'][0] );
        } 
        if(isset($_POST['delete_video_image'])) {
            update_post_meta( $post_id, 'ss_image_video', "" );					
        }                
    }
    
       
    removeCategoryPostOnCategoryUnselect($post_id, $add_cats);
    
          
    if ( ! empty($error_code)) {			
         wp_redirect(SITE_URL.'/edit-advertiser/?error_code=' . $error_code);
    } elseif (empty($error_code) && ! $isAjaxPreview) {
         wp_redirect(SITE_URL.'/edit-advertiser/?success_code=1' );			
    } elseif (empty($error_code) && $isAjaxPreview) {
        $previewPost = get_post($_POST['post_id']);
        $slug = $previewPost->post_name;
        $previewURL = SITE_URL.'/brands-and-boutiques/' . $slug . '?preview=true';
        echo $previewURL;
        die();
    }