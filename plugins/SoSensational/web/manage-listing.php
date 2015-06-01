<?php
    global $wpdb;
    $user = wp_get_current_user();
    $advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );
?>

<h1><strong>Manage Your Listing</strong></h1>
&nbsp;

<?php if($_GET['adminmsg']== 's'):?>
  <div class="alert alert-success" role="alert"><? echo get_option( 'listing_send_message' ); ?></div>
<?php endif; ?>

<div class="form-buttons-group clearfix">
    <h3 class="pull-left" style="margin-bottom: 0;">Welcome to your listing management area.</h3>
    <a name="preview" class="preview-anchor-text button_ss_small" target="_blank" href="<?php echo $advertiser[0]->guid; ?>">Preview Your Listing</a>
</div>

<span class="large_font">Here you can add and edit the details of your profile page as well as upload and change products and the categories you are listed in.</span>
<h3 style="padding-left: 30px; margin-top: 30px;"><strong>Step 1:</strong> <a title="Edit Advertiser" href="/edit-advertiser/">Add/Edit Your Profile</a></h3>
&nbsp;
<h3 style="padding-left: 30px;"><strong>Step 2:</strong> <a title="View Products" href="/view-products/">Add/Edit Your Products</a></h3>
&nbsp;
<h3 style="padding-left: 30px;"><b><strong>Step 3:</strong> </b><a title="Show Advertisers Cats" href="/show-advertisers-cats/">Add/Edit Category Information</a></h3>
&nbsp;
<form action="<?php echo SOSENSATIONAL_URL?>/web/advertisers-cats-submit.php" method="POST" enctype="multipart/form-data" class="category-edit-block">
  <button type="submit" class="button_ss_small" href="#" style="margin-left: 30px;">Submit for Review</button>
</form>  