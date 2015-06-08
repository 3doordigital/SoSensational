<?php

$allResultsPage = new Template('results-page.php');

global $query_string;    
parse_str($query_string, $argsArray);

switch($argsArray['search-section']) {
    case 'shop' :
        $args = array(
            's' =>   $argsArray['search-term'],
            'post_type' =>  array('wp_aff_products'),
            'posts_per_page'    => 12,
            'paged' => $argsArray['page-no'],
        );        
        
        $searchQuery = new WP_Query($args);
        
        relevanssi_do_query($searchQuery);                       

        if ($searchQuery->have_posts()) {
            $products = [];
            $i = 0;
            while($searchQuery->have_posts()) {
                $searchQuery->the_post();
                $productMeta = get_post_meta($searchQuery->post->ID);
                $productBrand = wp_get_post_terms($searchQuery->post->ID, 'wp_aff_brands');
                $productPrice = get_post_meta( $searchQuery->post->ID, 'wp_aff_product_price', true );
                $productRrp = get_post_meta( $searchQuery->post->ID, 'wp_aff_product_rrp', true );

                $products[$i]['title'] = $searchQuery->post->post_title;
                $products[$i]['picture'] = $productMeta['wp_aff_product_image'][0];
                $products[$i]['link'] = $productMeta['wp_aff_product_link'][0];
                $products[$i]['price'] = $productPrice;
                $products[$i]['brand'] = $productBrand[0]->name;

                $i++;
            }
            
            $allResultsPage->products = $products;
            $allResultsPage->shop = true;

            wp_reset_postdata();        
            
            break;
        }
        
    case 'brands' :
        $args = array(
            's' =>   $argsArray['search-term'],
            'post_type' =>  array('products'),
            'posts_per_page'    => 12,
            'post_status'   =>  array('publish', 'pending'),
            'paged' => $argsArray['page-no'],
        );  
        
        $searchQuery = new WP_Query($args);
        
        relevanssi_do_query($searchQuery);        
        
        if ($searchQuery->have_posts()) {
            $bbProducts = [];
            $i = 0;

            while($searchQuery->have_posts()) {
                $searchQuery->the_post(); 
                $bbProductMeta = get_post_meta($searchQuery->post->ID);

                $postAuthor = get_post_field('post_author', $searchQuery->post->ID);
                $bbProductBrand = get_posts(array('post_type' => array('brands', 'boutiques'), 'author' => $postAuthor, 'posts_per_page' => 1));            

                $bbProducts[$i]['title'] = $searchQuery->post->post_title;
                $bbProducts[$i]['link'] = $bbProductMeta['ss_product_link'][0];
                $bbProducts[$i]['picture'] = $bbProductMeta['ss_product_image'][0];
                $bbProducts[$i]['price'] = $bbProductMeta['ss_product_price'][0];
                $bbProducts[$i]['brand'] = $bbProductBrand[0]->post_title;

                $i++;
            }

            $allResultsPage->products = $bbProducts;
            $allResultsPage->shop = false;

            wp_reset_postdata();
            
            break;
        }           
        
    case 'blog' :
        $args = array(
            's' =>   $argsArray['search-term'],
            'post_type' =>  array('post'),
            'posts_per_page'    => 12,
            'paged' => $argsArray['page-no'],
        );          
        
        $searchQuery = new WP_Query($args);
        
        relevanssi_do_query($searchQuery);
        
    if ($searchQuery->have_posts()) {
        $blogPosts = [];
        $i = 0;
        while($searchQuery->have_posts()) {
            $searchQuery->the_post();
            $imgId = get_post_thumbnail_id($searchQuery->post->ID);

            $blogPosts[$i]['title'] = get_the_title();
            $blogPosts[$i]['link'] = get_the_permalink();
            $blogPosts[$i]['thumbnail'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'blog-small');
            $blogPosts[$i]['alt-text'] = get_post_meta($imgId, '_wp_attachment_image_alt', true);
            $blogPosts[$i]['meta'] = sosen_post_meta();
            $blogPosts[$i]['exerpt'] = the_excerpt_max_charlength(340);
            
            $i++;
        }
        wp_reset_postdata();
        
        $allResultsPage->posts = $blogPosts;
        
        break;
        
    }        
        
     
}

$allResultsPage->max_num_pages = $searchQuery->max_num_pages;

echo $allResultsPage;