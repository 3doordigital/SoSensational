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
        );        
        
        $searchQuery = new WP_Query($args);
        
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
            
            wp_reset_postdata();            
        }
        
    case 'brands' :
        $args = array(
            's' =>   $argsArray['search-term'],
            'post_type' =>  array('products'),
            'posts_per_page'    => 12,
            'post_status'   =>  array('publish', 'pending'),
        );  
    case 'blog' :
        $args = array(
            's' =>   $argsArray['search-term'],
            'post_type' =>  array('post'),
            'posts_per_page'    => 12,
        );          
}

echo $allResultsPage;