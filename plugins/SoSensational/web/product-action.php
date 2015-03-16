<?php 

require( '../../../../wp-load.php');

if (!is_user_logged_in()){exit();}
global $wpdb;
$error_code = "";
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

$advertiserID=$advertiser[0]->ID;

$countProducts= $wpdb->get_results("SELECT count(ID) as num FROM `{$wpdb->posts}` WHERE post_parent='{$advertiserID}' and post_type='products'",OBJECT);

/**
 * Retrieve the limit of products to upload by the current user by user role
 */
$currentUser = wp_get_current_user();
$currentUserRole = $currentUser->roles[0];

$options = get_option('ss_settings');
if ($currentUserRole == 'boutique_role') {
    $productsLimit = $options['ss_products_per_boutique'];
} elseif ($currentUserRole == 'brand_role') {
    $productsLimit = $options['ss_products_per_brand'];
}



/* Proceed with the upload only if the limit has not been exhausted */
if($countProducts[0]->num < $productsLimit) {

    if (!empty($_FILES['upload_product_image'])) {

     $image_video_id = upload_user_file( $_FILES['upload_product_image']);

    }


            if (empty($_POST['post_title']))
            {
                    $error_code =3;	
            }

            if (empty($_POST['post_title']))
            {
                    $error_code =3;	
            }



      $post=array(
        'post_title' => $_POST['post_title'],
        'post_type' => 'products',
        'post_status' => 'pending',
        'post_parent' => $advertiser[0]->ID,
        'post_author' => $user->ID,
        );


      if(isset($_POST['product_id']) && (!empty($_POST['product_id']) && ($_POST['ss_action']=='edit'))){
          $post_id=$_POST['product_id'];
              $post['ID'] = $post_id;
          wp_update_post($post);
      }elseif (isset($_POST['product_id']) && (!empty($_POST['product_id']) && ($_POST['ss_action']=='delete'))) {
          $post_id=$_POST['product_id'];
          wp_delete_post($post_id);
      }else{
        $post_id=wp_insert_post($post);
      }


            // update post tags
      wp_set_post_tags($post_id, $_POST['post_tags']);

      foreach($_POST['sosensational_options'] as $key=>$value)
          {
                    if ($key == "product_link")
                    {
                            if (preg_match("#https?://#", $value) === 0) {
                             $value = 'http://'.$value;
                            }	
                    }

                    if ($key == "product_price")
                    {
                            $symbols = array("£","$");
                            $value = str_replace($symbols, "", $value);	
                    }


            update_post_meta( $post_id, 'ss_'.$key, $value );

                    if ($value == "") { $error_code = 4; }
          }


      if(isset($image_video_id)){
          $image_video=get_post_meta($image_video_id);      
      }


                      if(!empty($image_video))
                      {
                          update_post_meta( $post_id, 'ss_product_image', $image_video['_wp_attached_file'][0] );
                      }        

                    if ($error_code =="") {			
                    wp_redirect(SITE_URL.'/add-product/?success_code=2&action=edit&product_id='.$post_id);
                    } else {
                    wp_redirect(SITE_URL.'/add-product/?error_code=' .$error_code. '&action=edit&product_id='.$post_id);			
                    }
} // end if product number ...