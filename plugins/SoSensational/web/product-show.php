<nav>
    <ul class="pager">
        <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Menu </a></li>
    </ul>
</nav>



<?php
do_action('ss_css');
global $post;
global $wpdb;
$options = get_option('ss_settings');
$user = wp_get_current_user();
$advertiser = $wpdb->get_results("SELECT DISTINCT * FROM {$wpdb->posts} where (post_type='brands' or post_type='boutiques') and post_author='{$user->ID}' ", OBJECT);
$currentUserRole = $user->roles[0];

$post_categories_available = get_the_terms($advertiser[0]->ID, 'ss_category');

/**
 * Determine the allowed uploads limit based on the user role - brand_role/ boutique_role
 */
if ($currentUserRole == "brand_role") {
    $allowed_products = $options['ss_products_per_brand'];
} else {
    $allowed_products = $options['ss_products_per_boutique'];
}


$args = array(
    'post_type' => 'products',
    'showposts' => -1,
    'post_status' => array('publish', 'pending', 'draft'),
    'author' => $user->ID
);

?> 

<?php echo displaySystemNoticeForSteps($user, $advertiser); ?>

    <?php
    $my_query = new WP_Query($args);
    $num_of_products = null;
    $productIndex = 0;
    $num_of_products = count($my_query->posts);
    // Display an additional 'Add a product' button if more than 3 products have been added
    if ($num_of_products > 3 && $productIndex === 0) :
        ?>
        <?php if ($num_of_products < $allowed_products) { ?>
            <div class="form-buttons-group clearfix">
                <a href="/add-product/" rel="bookmark" class="btn btn-default navbar-btn" title="Permanent Link to <?php the_title_attribute(); ?>">Add A New Product</a>
                <a name="preview" class="preview-anchor-text button_ss_small" target="_blank" href="<?php echo $advertiser[0]->guid; ?>">Preview Your Listing</a>
            </div>   
        <?php } ?>
    <?php endif; ?>  
    <ul class="nav nav-pills nav-stacked advertiser-items-list" > 
    <?php
    if ($my_query->have_posts()) : while ($my_query->have_posts()) : $my_query->the_post();?>
            <li>                
                <a href="/add-product/?action=edit&product_id=<?php echo get_the_ID(); ?>" rel="bookmark" title="Edit <?php the_title_attribute(); ?>">
                    <img class="ss_product_img" src="<?php echo get_post_meta(get_the_ID(), 'ss_product_image', true); ?>" /> 
                    <span class="large_font"><?php the_title(); ?></span> <span class="remove-product" data="<?php echo get_the_ID(); ?>" title="Remove Product">remove product<i class="glyphicon glyphicon-remove ajax-delete" ></i></span>
                </a>                
            </li>
            <?php
            ++$productIndex;
        endwhile;
    else :
        ?>
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
            <span class="sr-only">Error:</span>
            No Products Found 
        </div>

    <?php endif; ?>

</ul>
<?php if ($num_of_products < $allowed_products) { ?><br /><br />    
    <div class="form-buttons-group clearfix">
        <a href="/add-product/" rel="bookmark" class="btn btn-default navbar-btn" title="Permanent Link to <?php the_title_attribute(); ?>">Add A New Product</a>
        <a name="preview" class="preview-anchor-text button_ss_small" target="_blank" href="<?php echo $advertiser[0]->guid; ?>">Preview Your Listing</a>
    </div>
<?php } ?>
<h4>You have entered <?php echo $num_of_products ?> of <?php echo $allowed_products ?> products</h4>



<nav>
    <ul class="pager">
        <li class="previous"><a href="/ss_directory"><span aria-hidden="true">&larr;</span> Go Back To Menu </a></li>
    </ul>
</nav>
<button id="changeItemsOrder">Save Order</button>
<?php
    var_dump($post);
wp_enqueue_script('jquery-ui-sortable');
