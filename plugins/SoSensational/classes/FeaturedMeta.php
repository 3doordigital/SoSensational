<?php
/**
 * A class that registers a meta box on brands and boutiques.
 * 
 * The meta lets the admin choose categories in which an advertiser will be featured.
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
 */

require_once 'inc/FeaturedMeta/Form.php';

function callFeaturedMeta()
{
    new FeaturedMeta;
}

if (is_admin()) {
    add_action('load-post.php', 'callFeaturedMeta');
    add_action('load-post-new.php', 'callFeaturedMeta');
}

class FeaturedMeta
{
    public function __construct()
    {
        add_action( 'add_meta_boxes', array($this, 'addFeaturedMetaBox') );
        add_action( 'save_post', array($this, 'savePost') );
    }
    
    public function addFeaturedMetaBox($postType)
    {
        $postTypes = array('brands', 'boutiques');
        if (in_array($postType, $postTypes)) {
            add_meta_box(
                    'featured-in-categories',
                    'Featured in Categories',
                    array($this, 'renderFeaturedMetaBox'),
                    $postType,
                    'advanced',
                    'high'
            );
        }
    }
    
    public function renderFeaturedMetaBox($post)
    {
        wp_nonce_field('featured-meat-box', 'featured-meta-box-nonce');
        
        $selectedCategories = get_post_meta($post->ID, '_categories_featured', true);
        
        global $wpdb;
        
        /* Query for 'ss_category' term that do not have any parents (main categories)*/
        $categoriesToRender=$wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
            LEFT JOIN {$wpdb->terms} as wpt
            ON wpt.term_id=wptt.term_id
            WHERE wptt.taxonomy='ss_category' AND wptt.parent='0'", OBJECT);

        $form = new Form($categoriesToRender, $selectedCategories);
        $form->renderForm();
    }
    
    public function savePost($postId)
    {
        if ( ! isset($_POST['featured-meta-box-nonce']) ) {
            return $postId;
        }
        
        $nonce = $_POST['featured-meta-box-nonce'];
        
        if ( ! wp_verify_nonce($nonce, 'featured-meat-box') ) {
            return $postId;
        }
        
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $postId;
        }
        
        if ('page' == $_POST['post_type']) {
            if ( !current_user_can('edit_page', $postId) ) {
                return $postId;
            }
        } else {
            if ( !current_user_can('edit_post', $postId) ) {
                return $postId;
            }
        }
        
        $data = $_POST['categoriesFeatured'];        
        
        update_post_meta($postId, '_categories_featured', $data);
        
    }
            
}