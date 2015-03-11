<?php
add_action('init', 'sosensational_custom_taxonomy',0);

function sosensational_custom_taxonomy() {
    // Add new taxonomy, make it hierarchical 
    $labels = array(
        'name'              => _x( 'SoSensational Categories', 'taxonomy general name' ),
        'singular_name'     => _x( 'SoSensational Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Categories' ),
        'all_items'         => __( 'All Categories' ),
        'parent_item'       => __( 'Parent Category' ),
        'parent_item_colon' => __( 'Parent Category:' ),
        'edit_item'         => __( 'Edit Category' ),
        'update_item'       => __( 'Update Category' ),
        'add_new_item'      => __( 'Add New Category' ),
        'new_item_name'     => __( 'New SoSensational Category' ),
        'menu_name'         => __( 'SoSensational Category' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'ss_category' ),
    );
}
    register_taxonomy( 'ss_category', array('brands'), $args );

    ?>