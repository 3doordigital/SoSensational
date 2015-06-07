<?php

add_action('init','ss_generate_image_size');
function ss_generate_image_size(){
	add_theme_support( 'post-thumbnails' ); 
    add_image_size('ss_image',380,250,true);
    add_image_size('ss_advertisers_cats_image',380,250,true);
}



add_filter( 'image_size_names_choose', 'custom_image_sizes_choose' );
function custom_image_sizes_choose( $sizes ) {
    $custom_sizes = array(
        'ss_image' => 'So Sensational Image',
		'ss_advertisers_cats_image' => 'So Sensational Category Image'
    );
    return array_merge( $sizes, $custom_sizes );
}
?>