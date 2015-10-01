<?php
do_action('ss_css');
    global $post;
	global $current_user;
	get_currentuserinfo();
	
    if(isset($_GET['advertisers_cats_id'])){
        $advertisers_cats_id=$_GET['advertisers_cats_id'];
        $meta=get_post_meta($advertisers_cats_id);
		$the_post = get_post($advertisers_cats_id);
    }      

if ($the_post->post_author != $current_user->ID) {
	die("You are not the product owner!");	
}

?>       
<form action="<?php echo SOSENSATIONAL_URL?>/web/advertisers-cats-action.php" method="POST" enctype="multipart/form-data" >
  
  
   <p><label for="advertisers_cats_description">Description</label><input type="text" name="sosensational_options[advertisers_cats_description]" id="advertisers_cats_description"  value="<?php echo !isset($meta)?'': $meta['ss_advertisers_cats_description'][0] ;?>" /></p>
  <p><label for="advertisers_cats_link">Link</label><input type="text" name="sosensational_options[advertisers_cats_link]" id="advertisers_cats_link" class="widefat" value="<?php echo !isset($meta)?'': $meta['ss_advertisers_cats_link'][0] ;?>" /></p>

<tr valign="top">
        <th scope="row">Upload Image </th>
        <img src="<?php echo !isset($meta)?'': $meta['ss_advertisers_cats_image'][0] ;?>"/>
        <td><label for="upload_advertisers_cats_image">
        <input id="upload_advertisers_cats_image" type="file" size="50" name="upload_advertisers_cats_image" value="" />
        </label></td>
    </tr>
        <input type="submit" value="Submit">
        <?php if (isset($_GET['action'])):?>
        <input type="hidden" name="ss_action" value="<?php echo $_GET['action']?>" />
        <?php
        endif;
        if(isset($_GET['product_id'])):?>
        <input type="hidden" name="advertisers_cats_id" value="<?php echo $_GET['advertisers_cats_id']?>" />
        <?php
        endif;
        ?>
        <input type="hidden" name="post_type" value="advertisers_cats"/>
</form>

<script>
</script>