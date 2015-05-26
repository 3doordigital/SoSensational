<?php
/**
 * A class that registers a meta box for providing SEO metadata on brands and boutiques.
 * 
 * The saved SEO metadata is later handled by Yoast SEO plugin filters.
 * SoSensational.php - filters: wpseo_title, wpseo_metadesc
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data May 2015
 */

require_once 'inc/SeoMeta/SeoForm.php';

function callSeoMeta()
{
    new SeoMeta;
}

if (is_admin()) {
    add_action('load-post.php', 'callSeoMeta');
    add_action('load-post-new.php', 'callSeoMeta');
}

class SeoMeta
{
    public function __construct()
    {
        add_action( 'add_meta_boxes', array($this, 'addSeoMetaBox') );
        add_action( 'save_post', array($this, 'savePost') );
    }
    
    public function addSeoMetaBox($postType)
    {
        $postTypes = array('brands', 'boutiques');
        if (in_array($postType, $postTypes)) {
            add_meta_box(
                    'seo-metadata',
                    'SEO Metadata',
                    array($this, 'renderSeoMetaBox'),
                    $postType,
                    'advanced',
                    'high'
            );
        }
    }
    
    public function renderSeoMetaBox($post)
    {
        wp_nonce_field('seo-meat-box', 'seo-meta-box-nonce');
        
        $currentSeoData = get_post_meta($post->ID, '_seo_metadata', true);        

        $form = new SeoForm($currentSeoData);
        $form->renderForm();
    }
    
    public function savePost($postId)
    {
        if ( ! isset($_POST['seo-meta-box-nonce']) ) {
            return $postId;
        }
        
        $nonce = $_POST['seo-meta-box-nonce'];
        
        if ( ! wp_verify_nonce($nonce, 'seo-meat-box') ) {
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
        
        $seoMetadata['seo-title'] = $_POST['seo-title'];    
        $seoMetadata['seo-description'] = $_POST['seo-description'];
        
        update_post_meta($postId, '_seo_metadata', $seoMetadata);
        
    }
            
}