<?php

require( '../../../../wp-load.php');
(int)$post_id=$_POST['post_id'];
$post_type=$_POST['post_type'];
$options = get_option( 'ss_settings' );
$error_code = "";
//print_r($_POST);

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
		  
	        if($logo_id){
    	        $logo=get_post_meta($logo_id);  
				// Inserted to overwrite
				$logo =  wp_get_attachment_image_src( $logo_id, 'ss_image' ) ;  
   		     }

        }
        if(!empty($_FILES['upload_image_video'])){
          $image_video_id = upload_user_file( $_FILES['upload_image_video']);
			 if($image_video_id){
				$image_video=get_post_meta($image_video_id);      
			}

        }



    $no_of_cats=count(isset($_POST['advertiser_category']) ? $_POST['advertiser_category'] : 0);
    $cats_good=false;
//	echo $options['ss_categories_per_brand'];
	
    if($post_type=='boutiques'){
       if($no_of_cats<=$options['ss_categories_per_boutique']){
         $cats_good=true;
       }
    }
   if($post_type=='brands'){
      if($no_of_cats<=$options['ss_categories_per_brand']){
        $cats_good=true;
      }
   }
   
   if($cats_good) {
  
    $add_cats=array();
	if (isset($_POST['advertiser_category'])) {
      foreach($_POST['advertiser_category'] as $key=>$value){
		   $term = get_term((int)$value, 'ss_category' );
          $add_cats[]=(int)$value;
       //   if($term->parent){
        //    $add_cats[]=(int)$term->parent;
        //  }
   		 }
	}
	//print_r($post_id);
    //insert categories into database. field ss_advertiser_category in post_meta of advertiser
 //   update_post_meta( $post_id, 'ss_category', $add_cats );
 	wp_set_post_terms($post_id,$add_cats,'ss_category');
   } else {
		$error_code = "1"; // Too many cats		
	}//cats_good

    foreach($_POST['sosensational_options'] as $key=>$value)
    {	
             update_post_meta( $post_id, 'ss_'.$key, $value );
			 if ($key == "advertiser_co_name")
			 {
				  $my_post = array(
   				    'ID'           => $post_id,
      				'post_title' => $value,
  					);

				// Update the post into the database
  				wp_update_post( $my_post ); 
			 }
			 
    }
    switch($_POST['post_type'])
        {
            case 'boutiques':
                if(!empty($logo))
                {
                   //  update_post_meta( $post_id, 'ss_logo', $logo['_wp_attached_file'][0] );
				    update_post_meta( $post_id, 'ss_logo', $logo[0] );
                }
                if(!empty($image_video))
                {
                    update_post_meta( $post_id, 'ss_image_video', $image_video['_wp_attached_file'][0] );
                } 
				if(isset($_POST['delete_video_image'])) {
					
                    update_post_meta( $post_id, 'ss_image_video', "" );					
				}
                break;

            case 'brands':
                if(!empty($logo))
                {
                   //  update_post_meta( $post_id, 'ss_logo', $logo['_wp_attached_file'][0] );
				    update_post_meta( $post_id, 'ss_logo', $logo[0] );
                }
                if(!empty($image_video))
                {
                    update_post_meta( $post_id, 'ss_image_video', $image_video['_wp_attached_file'][0] );
                }
				if(isset($_POST['delete_video_image'])) {
					
                    update_post_meta( $post_id, 'ss_image_video', "" );					
				}        
                break;
        }
		if (!empty($error_code))
		{
			
		     wp_redirect(SITE_URL.'/edit-advertiser/?error_code=' . $error_code);
		}
		else 		
		{
		     wp_redirect(SITE_URL.'/edit-advertiser/?success_code=1' );			
		}
?>