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
        $i = 0;
        while($shopQuery->have_posts()) {
            $shopQuery->the_post();
            $productMeta = get_post_meta($shopQuery->post->ID);
            $productBrand = wp_get_post_terms($shopQuery->post->ID, 'wp_aff_brands');
            $productPrice = get_post_meta( $shopQuery->post->ID, 'wp_aff_product_price', true );
            $productRrp = get_post_meta( $shopQuery->post->ID, 'wp_aff_product_rrp', true );

            $products[$i]['title'] = $shopQuery->post->post_title;
            $products[$i]['picture'] = $productMeta['wp_aff_product_image'][0];
            $products[$i]['link'] = $productMeta['wp_aff_product_link'][0];
            $products[$i]['price'] = $productPrice;
            $products[$i]['brand'] = $productBrand[0]->name;
            
            
            $i++;
        }
        wp_reset_postdata();
    }
    
    
    $bbArgs = array(
        's' =>   get_query_var('s'),
        'post_type' =>  array('products'),
        'posts_per_page'    => 3,
        'post_status'   =>  array('publish', 'pending'),
    );
    $bbQuery = new WP_Query($bbArgs);    
    
    if ($bbQuery->have_posts()) {
        $bbProducts = [];
        $i = 0;
        while($bbQuery->have_posts()) {
            $bbQuery->the_post(); 
            $bbProductMeta = get_post_meta($bbQuery->post->ID);
            
            $postAuthor = get_post_field('post_author', $bbQuery->post->ID);
            $bbProductBrand = get_posts(array('post_type' => array('brands', 'boutiques'), 'author' => $postAuthor, 'posts_per_page' => 1));            

            $bbProducts[$i]['title'] = $bbQuery->post->post_title;
            $bbProducts[$i]['link'] = $bbProductMeta['ss_product_link'][0];
            $bbProducts[$i]['pricture'] = $bbProductMeta['ss_product_image'][0];
            $bbProducts[$i]['price'] = $bbProductMeta['ss_product_price'][0];
            $bbProducts[$i]['brand'] = $bbProductBrand[0]->post_title;
            
            $i++;
        }
        wp_reset_postdata();
    }
    
    
    
    $blogArgs = array(
        's' =>   get_query_var('s'),
        'post_type' =>  array('post'),
        'posts_per_page'    => 3,
    );
    $blogQuery = new WP_Query($blogArgs);    
    
    if ($blogQuery->have_posts()) {
        $blogPosts = [];
        $i = 0;
        while($blogQuery->have_posts()) {
            $blogQuery->the_post();
            $imgId = get_post_thumbnail_id($blogQuery->post->ID);

            $blogPosts[$i]['title'] = get_the_title();
            $blogPosts[$i]['link'] = get_the_permalink();
            $blogPosts[$i]['thumbnail'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'blog-small');
            $blogPosts[$i]['alt-text'] = get_post_meta($imgId, '_wp_attachment_image_alt', true);
            $blogPosts[$i]['meta'] = sosen_post_meta();
            $blogPosts[$i]['exerpt'] = the_excerpt_max_charlength(340);
            
            $i++;
        }
        wp_reset_postdata();
    }
    
    
    
    $searchPage->products = $products;
    $searchPage->bbProducts = $bbProducts;
    $searchPage->blogPosts = $blogPosts;

    
    echo $searchPage;