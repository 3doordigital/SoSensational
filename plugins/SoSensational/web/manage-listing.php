<?php
    global $wpdb;
    $user = wp_get_current_user();
    $advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT );
?>

<h1><strong>Manage Your Listing</strong></h1>
&nbsp;
<div class="form-buttons-group clearfix">
    <h3 class="pull-left">Welcome to your listing management area.</h3>
    <a name="preview" class="preview-anchor-text" target="_blank" href="<?php echo $advertiser[0]->guid; ?>">Preview Your Listing</a>
</div>

<span class="large_font">Here you can edit the details of your profile page as well as upload and change products and the categories you are listed in.</span>
<h3 style="padding-left: 30px;"><strong>Step 1:</strong> <a title="Edit Advertiser" href="http://sosen.3doordigital.com/edit-advertiser/">Edit Your Profile</a></h3>
&nbsp;
<h3 style="padding-left: 30px;"><strong>Step 2:</strong> <a title="View Products" href="http://sosen.3doordigital.com/view-products/">Edit Your Products</a></h3>
&nbsp;
<h3 style="padding-left: 30px;"><b><strong>Step 3:</strong> </b><a title="Show Advertisers Cats" href="http://sosen.3doordigital.com/show-advertisers-cats/">Edit Category Information</a></h3>
&nbsp;