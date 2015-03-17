<?php
do_action('ss_css');
    global $post;
	global $current_user;
	get_currentuserinfo();
	$post_tags= "";	
	
    if(isset($_GET['product_id'])){
        $product_id=$_GET['product_id'];
        $meta=get_post_meta($product_id);
		$the_post = get_post($product_id);
		$post_tags = wp_get_post_tags( $product_id, array( 'fields' => 'names' ) );
		$post_tags = implode(', ',$post_tags); 
		if ($the_post->post_author != $current_user->ID) {
			die("You are not the product owner!");	
		}
    }  

$error_code=isset($_GET['error_code']) ? $_GET['error_code'] : "";
$success_code=isset($_GET['success_code']) ? $_GET['success_code'] : "";

if (!empty($success_code)) :
    switch($success_code) {
        case '2':
            $display_message = "Product Updated Successfully";                
            break;        
}

/* Redirect the user to products listing on success */
wp_redirect(home_url() . '/view-products/?adminmsg=s');

?>

<div class="alert alert-success" role="alert"><?php echo $display_message; ?></div>

<?php
elseif (!empty($error_code)) :
  switch($error_code)
        {
            case '3':
				$display_message = "Title Is Blank";
	
            case '4':
				$display_message = "No fields Can Be Empty";				
			break;

		}

?>
<div class="alert alert-danger" role="alert">
  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  <span class="sr-only">Error:</span>
	<?php echo $display_message ?>
</div>
<?php
endif; 

?> 

    <nav>
  <ul class="pager">
    <li class="previous"><a href="/view-products"><span aria-hidden="true">&larr;</span> Go Back To View Products </a></li>
  </ul>
</nav>
      
<form   id="addproduct" action="<?php echo SOSENSATIONAL_URL?>/web/product-action.php" method="POST" enctype="multipart/form-data" >

<div class="media">
  <div class="media-left media-middle">
    <a href="#">
        <img src="<?php echo isset($meta['ss_product_image'][0])? $meta['ss_product_image'][0] : get_template_directory_uri() . "/images/upload-artwork.png"; ?>"/>
    </a>
  </div>
  <div class="media-body">
    <h4 class="media-heading">Upload Product Image</h4>
      <input id="upload_product_image" type="file" size="50" name="upload_product_image" value="" />
  </div>
</div>

<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Product Title:</span>
        <input type="text" name="post_title" id="post_title" value="<?php echo !isset($meta)?'':get_the_title($product_id);?>"  class="required form-control" aria-describedby="basic-addon1" />
</div>
<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Product Price:</span>
<input type="text" name="sosensational_options[product_price]" id="product_price"  value="<?php echo !isset($meta)?'': $meta['ss_product_price'][0] ;?>"  class="required form-control" aria-describedby="basic-addon1"  />
  </div>
<br />
<div class="input-group">
  <span class="input-group-addon input-width" id="basic-addon1">Product Direct Link:</span>
<input type="text" name="sosensational_options[product_link]" id="product_link" value="<?php echo !isset($meta)?'': $meta['ss_product_link'][0] ;?>"   class="required form-control" aria-describedby="basic-addon1" />
</div>
<br />
<div class="input-group">
    <span class="input-group-addon input-width" id="basic-addon1">Product Tags: <small id="tags-counter"></small></span>
<input type="text" name="post_tags" id="post_tags" data-role="tagsinput" value="<?php echo $post_tags ;?>" class="required form-control" aria-describedby="basic-addon1" />
</div>
<br />


        <button class="button_ss_small" type="submit" value="Submit Product">Submit Product</button>
        <?php if (isset($_GET['action'])):?>
        <input type="hidden" name="ss_action" value="<?php echo $_GET['action']?>" />
        <?php
        endif;
        if(isset($_GET['product_id'])):?>
        <input type="hidden" name="product_id" value="<?php echo $_GET['product_id']?>" />
        <?php
        endif;
        ?>
        <input type="hidden" name="post_type" value="products"/>
</form>
    <nav>
  <ul class="pager">
    <li class="previous"><a href="/view-products"><span aria-hidden="true">&larr;</span> Go Back To View Products </a></li>
  </ul>
</nav>

<script>
function checkImage()
{
}

jQuery("#addproduct").submit(function(){

    var isFormValid = true;
	var displayed = false;
	var imageUrl = "<?php echo isset($meta['ss_product_image'][0])? $meta['ss_product_image'][0] : ""; ?>"	

	if ((imageUrl == "" ) && (jQuery("#upload_product_image").val() == ""))
	{
		 alert("Please Submit A Product Image");
		 isFormValid = false;
		 displayed = true;	
	}


    jQuery("input.required ").each(function(){
        if (jQuery.trim(jQuery(this).val()).length == 0){
            jQuery(this).addClass("highlight");
            isFormValid = false;
        }
        else{
            jQuery(this).removeClass("highlight");
        }
    });

    if ((!isFormValid) && (!displayed)) alert("Please fill in all the required fields");

    return isFormValid;
});


</script>