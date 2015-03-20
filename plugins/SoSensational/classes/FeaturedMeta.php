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
        
        //$value = get_post_meta($post->ID, '_brands_fetured_in', true);
        
        new Form();
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
        
        if ( define('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
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
        
        $data = sanitize_text_field($_POST['selection_fields']);
        
        update_post_meta($postId, '_brands_featured_in', $data);
        
    }
            
}