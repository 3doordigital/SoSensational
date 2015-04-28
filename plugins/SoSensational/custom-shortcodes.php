<?php

function ss_directory() {
    global $wp_query, $wp_rewrite;
    add_filter('wpseo_breadcrumb_links', 'wpse_100012_override_yoast_breadcrumb_trail');


    $ss_cat = isset($wp_query->query_vars['ss_cat']) ? $wp_query->query_vars['ss_cat'] : "";
    $ss_sub_cat = isset($wp_query->query_vars['ss_sub_cat']) ? $wp_query->query_vars['ss_sub_cat'] : "";
    $ss_advertiser = isset($wp_query->query_vars['ss_advertiser']) ? $wp_query->query_vars['ss_advertiser'] : "";


    if (($ss_cat == "") && ($ss_sub_cat == "")) {
        ob_start();
        include (SOSENSATIONAL_DIR . '/web/show-categories.php');
        $content = ob_get_clean();
        echo $content;
    } elseif (($ss_cat != "") && ($ss_sub_cat == "")) {

        $tax_term = get_term_by("slug", $ss_cat, 'ss_category', OBJECT);

        if (!empty($tax_term)) {
            $ss_cat_id = $tax_term->term_id;
            ob_start();
            include (SOSENSATIONAL_DIR . '/web/view-category.php');
            $content = ob_get_clean();
            echo $content;
        } else {
            // This might be an advertiser.. So we shall do a check using the ss_cat as the ID

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
                include (SOSENSATIONAL_DIR . '/web/advertiser.php');
                $content = ob_get_clean();
                echo $content;
            } else {
                echo "Sorry no advertiser found or category... ";
            }
        }
    } elseif (($ss_cat != "") && ($ss_sub_cat != "")) {
        //	echo $ss_sub_cat;
        $tax_term = get_term_by("slug", $ss_sub_cat, 'ss_category', OBJECT);
        //	print_r($tax_term);


        if (!empty($tax_term)) {
            $ss_sub_cat_id = $tax_term->term_id;
            //$ss_cat_id  = $ss_sub_cat_id;
            ob_start();
            include (SOSENSATIONAL_DIR . '/web/view-category.php');
            $content = ob_get_clean();
            echo $content;
        }
    }
}

//////////////////////////////////////////////




add_shortcode('ss_directory', 'ss_directory');

function ss_add_product() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/product.php');
    $content = ob_get_clean();
    echo $content;
}

add_shortcode('ss_product', 'ss_add_product');

function ss_manage_listing() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/manage-listing.php');
    $content = ob_get_clean();
    echo $content;
}

add_shortcode('ss_manage_listing', 'ss_manage_listing');

function ss_edit_advertisers_cats() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/advertisers-cats-edit.php');
    $content = ob_get_clean();
    return $content;
}

add_shortcode('ss_edit_advertisers_cats', 'ss_edit_advertisers_cats');

function ss_show_products() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/product-show.php');
    $content = ob_get_clean();
    return $content;
}

add_shortcode('ss_show_products', 'ss_show_products');

function ss_show_advertisers_cats() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/advertisers-cats-show.php');
    $content = ob_get_clean();
    return $content;
}

add_shortcode('ss_show_advertisers_cats', 'ss_show_advertisers_cats');

function ss_edit_advertiser() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/edit-advertiser.php');
    $content = ob_get_clean();
    return $content;
}

add_shortcode('ss_edit_advertiser', 'ss_edit_advertiser');

function ss_show_advertiser() {

    global $wp_query, $wp_rewrite;

    $ss_advertiser = isset($wp_query->query_vars['ss_advertiser']) ? $wp_query->query_vars['ss_advertiser'] : "";
    //	print_r($ss_advertiser);

    $args = array(
        'name' => $ss_advertiser,
        'post_type' => array('brands', 'boutiques'),
        'post_status' => 'publish',
        'numberposts' => 1
    );
    $my_posts = get_posts($args);
    if ($my_posts) {

        $advertiser_id = $my_posts[0]->ID;
        //	add_filter( 'the_title', 'wordpress_title', 10, 1 );

        ob_start();
        include (SOSENSATIONAL_DIR . '/web/advertiser.php');
        $content = ob_get_clean();
        return $content;
    } else {
        echo "Sorry no advertiser found ... ";
    }
}

add_shortcode('ss_show_advertiser', 'ss_show_advertiser');

function ss_view_category() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/view-category.php');
    $content = ob_get_clean();
    return $content;
}

add_shortcode('ss_view_category', 'ss_view_category');

function ss_show_categories($atts, $content, $tag) {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/show-categories.php');
    $content = ob_get_clean();
    return $content;
}

add_shortcode('ss_show_categories', 'ss_show_categories');

function ss_login_form() {
    ob_start();
    include (SOSENSATIONAL_DIR . '/web/ss_login_form.php');
    $content = ob_get_clean();
    return $content;
}

add_shortcode('ss_login_form', 'ss_login_form');

function wordpress_title($old_title) {

    return 'New title';
}

function wpse_100012_override_yoast_breadcrumb_trail($breadcrumb) {
    global $post;
    if (preg_match('/brands-and-boutiques\/([^\/]*)\/?([^\/]*)\/?([^\/]*)\/?$/', $_SERVER["REQUEST_URI"], $match)) {
        
    }
    foreach ($match as $this_match) {
        if ($this_match != "") {
            $tax_term = get_term_by("slug", $this_match, 'ss_category', OBJECT);

            if (!empty($tax_term)) {
                $breadcrumb[] = array(
                    'url' => $tax_term->slug,
                    'text' => $tax_term->name,
                );
            } else {
                $args = array(
                    'name' => $this_match,
                    'post_type' => array('brands', 'boutiques'),
                    'post_status' => 'publish',
                    'numberposts' => 1
                );
                $my_posts = get_posts($args);
                if ($my_posts) {
                    $breadcrumb[] = array(
                        'url' => $my_posts[0]->post_name,
                        'text' => $my_posts[0]->post_title,
                    );
                }
            }
        }
    }



    return $breadcrumb;
}

?>