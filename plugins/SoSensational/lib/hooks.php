<?php

add_filter('manage_products_posts_columns', 'addProductsCustomColumn');
add_action('manage_products_posts_custom_column', 'processBBColumn', 10, 2);


/**
 * Add 'Brands/Boutiques' column to 'products' post type listing page
 * 
 * @param array $columns Current columns on a post listing page
 * @return array An extended set of columns
 */
function addProductsCustomColumn($columns)
{
    return array_merge($columns, array(
        'brand/boutique'    =>  __('Brand/Boutique')
    ));
}

function processBBColumn($column, $postId)
{
    $postAuthor = get_post_field('post_author', $postId);
    if ($column === 'brand/boutique') {
        $args = array(
            'post_type' =>  array('brands', 'boutiques'),
            'author'    =>  $postAuthor
        );
        $brands = get_posts($args);
        echo $brands[0]->post_title;
    }        
}