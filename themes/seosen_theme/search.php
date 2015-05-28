<?php
    $searchPage = new Template('searchpage.php');
    
    $shopArgs = array(
        's' =>   get_query_var('s'),
        'post_type' =>  array('wp_aff_products'),
        'posts_per_page'    => 3,
    );
    $shopQuery = new WP_Query($shopArgs);        
    
    if ($shopQuery->have_posts()) {
        $products = [];
        while($shopQuery->have_posts()) {
            $shopQuery->the_post();
            $productMeta = get_post_meta($shopQuery->post->ID);
            $productBrand = wp_get_post_terms($shopQuery->post->ID, 'wp_aff_brands');
            $productPrice = get_post_meta( $shopQuery->post->ID, 'wp_aff_product_price', true );
            $productRrp = get_post_meta( $shopQuery->post->ID, 'wp_aff_product_rrp', true );
            
            $products[]['price'] = $productPrice;
        }
        wp_reset_query();
    }
    
    
    $bbArgs = array(
        's' =>   get_query_var('s'),
        'post_type' =>  array('brands', 'boutiques'),
        'posts_per_page'    => -1,
    );
    $bbQuery = new WP_Query($bbArgs);    
    
    $blogArgs = array(
        's' =>   get_query_var('s'),
        'post_type' =>  array('post'),
        'posts_per_page'    => -1,
    );
    $blogQuery = new WP_Query($blogArgs);    
    
    
    
    $searchPage->products = $products;
    $searchPage->bbQuery = $bbQuery;
    $searchPage->blogQuery = $blogQuery;
    
    echo $searchPage;