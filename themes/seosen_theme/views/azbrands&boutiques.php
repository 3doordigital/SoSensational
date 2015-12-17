<?php get_header();
global $wp_query, $wpdb;
$cat_params = array('width' => 367, 'height' => 240, 'crop' => true);

$ss_cat = isset($wp_query->query_vars['ss_cat']) ? $wp_query->query_vars['ss_cat'] : "";
$ss_sub_cat = isset($wp_query->query_vars['ss_sub_cat']) ? $wp_query->query_vars['ss_sub_cat'] : "";

print
    $advertiser = false;
?>

<div class="container">
    <div class="row">
        <div class="col-md-24" id="content">
            <?php if ($ss_cat !== '' && !preg_match('/[\d]/', $ss_cat)) : ?>
                <?php
                $tax_term = get_term_by("slug", $ss_cat, 'ss_category', OBJECT);
                if (!empty($tax_term)) {
                    $ss_cat_id = $tax_term->term_id;
                    ob_start();
                    include(SOSENSATIONAL_DIR . '/web/view-category.php');
                    $content = ob_get_clean();
                    echo $content;
                } else {
                    // This might be an advertiser.. So we shall do a check using the ss_cat as the ID
                    $advertiser = true;
                    $args = array(
                        'name' => $ss_cat,
                        'post_type' => array('brands', 'boutiques'),
                        'post_status' => 'publish',
                        'numberposts' => 1
                    );
                    $my_posts = get_posts($args);
                    if ($my_posts) {

                        $advertiser_id = $my_posts[0]->ID;

                        ob_start();
                        include(SOSENSATIONAL_DIR . '/web/advertiser.php');
                        $content = ob_get_clean();
                        echo $content;
                    } else {
                        return_404();
                    }
                }
                ?>
            <?php elseif (preg_match('/[\d]/', $ss_cat)): ?>
                <?php return_404(); ?>
            <?php else: ?>
            <?php
            do_action('ss_css');
            ?>
            <h1><span><?php echo get_the_title(); ?></span></h1>
            <?php
            if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<div id="breadcrumbs">', '</div>');
            }
            ?>
            <div class="alphabet-links alphabet-links-first container">
                <h3 class="display-alphabet">Jump to letter... <span class="glyphicon glyphicon-arrow-down"></span></h3>
                <ul class="pagination">
                    <?php foreach ($postsByLetters as $letter => $posts) {
                        echo "<li><span><a href=\"#{$letter}\">{$letter}</a></span></li>";
                    }
                    ?>
                </ul>
                <label>
                    <input type="text" id="searchBrands">
                    <button class="button-search"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>Search
                    </button>
                </label>
            </div>
            <div id="infiniteScroll" class="infiniteScroll">
                <?php foreach ($postsByLetters as $letter => $posts): ?>
                <div class="category_ss_title_under" style="border-bottom: solid 3px #999FB5">
                    <span class="right_ss"> </span>

                    <p class="ss_description" style="margin-top: 40px;float: left">

                        <b class="" id="<?php echo $letter; ?>" name="<?php echo $letter; ?>" style="    position: relative;
    float: left;
    padding: 6px 12px;
    line-height: 1.42857143;
    text-decoration: none;
    color: #a0862a;
    background-color: #fff;
    border: 1px solid #ddd;
    margin-left: -1px;font-family: DidotLT-Roman;font-size: 28px;font-weight: bold"><?php echo $letter; ?></b>
                    </p>

                    <div class="ss_clear"></div>
                    <?php $counter = 1; ?>
                    <?php foreach ($posts as $onePost): ?>
                    <?php if ($onePost->post_type === 'custom_advertisers'): ?>
                        <div class="post col-md-8 col-sm-12 fadebox ss_advertisers_cats showme animated fadeIn <?php
                        if ($counter == 3) {
                            echo 'breakRowClass';
                        }
                        ?>" style="visibility: visible;margin-top: 30px">
                            <div class="advertiser-block-wrapper">
                                <?php
                                $meta = get_post_meta($onePost->ID);
                                $post_name = isset($onePost->post_name) ? $onePost->post_name : null;
                                $advertiser = $wpdb->get_results("SELECT DISTINCT * FROM {$wpdb->posts} WHERE (post_type='brands' or post_type='boutiques') AND post_author='{$onePost->post_author}' AND post_status='publish'", OBJECT);
                                ?>
                                <a href="<?php echo $meta['ss_custom_advertiser_url'][0] ?>"
                                   rel="bookmark" title="Permanent Link to <?php $onePost->post_name; ?>"
                                   class="aHolderImgSS">
                                    <?php $image = bfi_thumb(wp_get_attachment_url(get_post_thumbnail_id($onePost->ID)), $cat_params);
                                    ?>
                                    <img src="<?php echo $image; ?>" class="img-responsive"/>
                                    <?php
                                    if ($counter % 2): echo '<div class="whitebar ss_whitebar" style="display: block;">';
                                    else: echo '<div class="blackbar ss_blackbar" style="display: block;">';
                                    endif;
                                    ?>
                                    <h2 class="advertiser-title"><?php echo $onePost->post_title; ?></h2>
                            </div>
                            </a>
                            <div class="ss_clear"></div>
                            <div class="ss_advertisers_cats_description">
                                <?php
                                $description = $onePost->post_content;
                                $description = substr(strip_tags($description), 0, 186);
                                $description = strip_tags($description);
                                echo $description;
                                ?>
                            </div>
                            <a class="button_ss large_ss" target="_blank"
                               href="<?php echo $meta['ss_custom_advertiser_url'][0] ?>">Visit
                                Website
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="post col-md-8 col-sm-12 fadebox ss_advertisers_cats showme animated fadeIn <?php
                        if ($counter == 3) {
                            echo 'breakRowClass';
                        }
                        ?>" style="visibility: visible;margin-top: 30px">
                            <div class="advertiser-block-wrapper">
                                <?php
                                $post_name = isset($onePost->post_name) ? $onePost->post_name : null;
                                $advertiser = $wpdb->get_results("SELECT DISTINCT * FROM {$wpdb->posts} WHERE (post_type='brands' or post_type='boutiques') AND post_author='{$onePost->post_author}' AND post_status='publish'", OBJECT);
                                ?>
                                <a href="<?php echo get_site_url() . '/brands-and-boutiques/' . $post_name; ?>"
                                   rel="bookmark" title="Permanent Link to <?php $onePost->post_name; ?>"
                                   class="aHolderImgSS">
                                    <?php $image = bfi_thumb(get_post_meta($onePost->ID, 'ss_image_video', true), $cat_params);
                                        if(!$image){
                                            $image = bfi_thumb(get_post_meta($onePost->ID, 'ss_logo', true), $cat_params);
                                        }
                                    ?>
                                    <img src="<?php echo $image; ?>" class="img-responsive"/>
                                    <?php
                                    if ($counter % 2): echo '<div class="whitebar ss_whitebar" style="display: block;">';
                                    else: echo '<div class="blackbar ss_blackbar" style="display: block;">';
                                    endif;
                                    ?>
                                    <h2 class="advertiser-title"><?php echo $onePost->post_title; ?></h2>
                            </div>
                            </a>
                            <div class="ss_clear"></div>
                            <div class="ss_advertisers_cats_description">
                                <?php
                                $description = get_post_meta($onePost->ID, 'ss_advertiser_desc', true);
                                echo truncateDescription($description, $post_name);
                                ?>
                            </div>
                            <a class="button_ss large_ss" target="_blank"
                               href="http:\\<?php echo get_post_meta($onePost->ID, 'ss_affiliate_advertiser_link', true); ?>">Visit
                                Website</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php get_footer(); ?>
