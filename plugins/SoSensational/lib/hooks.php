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

/**
 * Add the name of the Brand/Boutique to the 'Brand/Boutique' column for each product
 * 
 * @param string $column The name of the column to populate
 * @param WP_Post $postId Current post in the post listing page (a given row in the table)
 */
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