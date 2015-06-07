<?php

/*
  Plugin Name: So Sensational
  Plugin URI:
  Description: So Sensational Brands & Boutiques
  Version: 0.1
  Author: 3doordigital.com
  Developement
  Author URI: http://www.3doordigital.com
 */

/* Definitions */
if (!defined('SOSENSATIONAL_URL'))
    define('SOSENSATIONAL_URL', WP_PLUGIN_URL . '/SoSensational');
if (!defined('SOSENSATIONAL_DIR'))
    define('SOSENSATIONAL_DIR', WP_PLUGIN_DIR . '/SoSensational');
if (!defined('SITE_URL'))
    define('SITE_URL', site_url());

require_once(SOSENSATIONAL_DIR . '/BFI_Thumb.php');


/* Includes */
require_once (dirname(__FILE__) . '/custom-post-types.php');
require_once (dirname(__FILE__) . '/custom-shortcodes.php');
require_once (dirname(__FILE__) . '/custom-sizes.php');
require_once (dirname(__FILE__) . '/page_settings.php');
require_once (dirname(__FILE__) . '/helpers/helper-functions.php');
require_once (dirname(__FILE__) . '/helpers/template-tags.php');

/**
 * My custom classes and other includes for SoSensational plugin
 * 
 * @author Lukasz Tarasiewicz
 */
require_once (dirname(__FILE__) . '/classes/FeaturedMeta.php');
require_once (dirname(__FILE__) . '/classes/RelatedCarousel.php');
require_once (dirname(__FILE__) . '/classes/FeaturedCarousel.php');
require_once (dirname(__FILE__) . '/classes/SeoMeta.php');
require_once (dirname(__FILE__) . '/lib/hooks.php');


register_activation_hook(__FILE__, 'add_roles_on_plugin_activation');
register_activation_hook(__FILE__, 'add_pages_on_plugin_activation');
register_activation_hook(__FILE__, 'move_template_pages_on_plugin_activation');

add_theme_support('infinite-scroll', array(
    'container' => 'infiniteScroll',
));

function add_roles_on_plugin_activation() {

    remove_role('boutique_role');
    remove_role('brand_role');

    add_role('boutique_role', 'Boutique Role', array(
        'read' => true,
        'delete_posts' => true,
        'edit_posts' => true,
        'edit_published_posts' => true,
        'upload_files' => true,
        'publish_posts' => true,
        'publish_pages' => true,
        'edit_pages' => true,
        'delete_pages' => true,
        'delete_published_posts' => true,
        'create_posts' => true,
        )
    );
    add_role('brand_role', 'Brand Role', array(
        'read' => true,
        'delete_posts' => true,
        'edit_posts' => true,
        'edit_published_posts' => true,
        'upload_files' => true,
        'publish_posts' => true,
        'publish_pages' => true,
        'edit_pages' => true,
        'delete_pages' => true,
        'delete_published_posts' => true,
        'create_posts' => true,
        )
    );

    $boutique_role = get_role('boutique_role');
    $boutique_role->add_cap('level_1');
    $brand_role = get_role('brand_role');
    $brand_role->add_cap('level_1');
}

function brand_boutique_no_admin_access() {
    /**
     * Amend the conditional so that the redirect does not take place when executing
     * a call to admin-ajax.php. Otherwise, ajax calls would fail.
     */
    if (((current_user_can('brand_role') || current_user_can('boutique_role')) && (!defined('DOING_AJAX') || !DOING_AJAX )) && $_SERVER['REQUEST_URI'] != '/wp-admin/admin-post.php' ) {
        exit(wp_redirect(SITE_URL . '/ss_directory/'));
    }
}

add_action('admin_init', 'brand_boutique_no_admin_access', 100);

function CopyFile($filename) {

    $TemplateFileSourceURL = SOSENSATIONAL_DIR . '/theme-files/' . $filename;
    $TemplateFileTargetURL = get_stylesheet_directory() . '/' . $filename;
    if (file_exists($TemplateFileTargetURL)) {
        return FALSE;
    }
    if (!file_exists($TemplateFileSourceURL)) {
        return FALSE;
    }

    $GetTemplate = file_get_contents($TemplateFileSourceURL);
    if (!$GetTemplate) {
        return FALSE;
    }

    $WriteTemplate = file_put_contents($TemplateFileTargetURL, $GetTemplate);
    if (!$WriteTemplate) {
        return FALSE;
    }
    return TRUE;
}

function move_template_pages_on_plugin_activation() {
    $filenames = array(
        'page-add-product.php',
        'page-edit-advertiser.php',
        'page_ss_category.php',
        'single-boutiques.php',
        'single-brands.php',
        'single-products.php',
        'taxonomy-ss_category.php',
    );

    foreach ($filenames as $filename) {
        //     CopyFile($filename);
    }
}

function check_page_by_slug($slug) {
    $args = array(
        'name' => $slug,
        'post_type' => 'page',
        'post_status' => 'publish',
        'page_template' => 'page-add-product.php',
    );
    return $my_posts = get_posts($args);
}

function add_pages_on_plugin_activation() {
    //page add product
    $post_add_product = array(
        'page_template' => 'page-add-product.php',
        'comment_status' => 'closed',
        'ping_status' => 'open',
        'post_author' => 1,
        'post_content' => '[ss_product]',
        'post_name' => 'add-product',
        'post_status' => 'publish',
        'post_title' => 'Add product',
        'post_type' => 'page',
    );
    $check = check_page_by_slug('add-product');
    if (empty($check)) {
        //   wp_insert_post( $post_add_product );
    }

    //page all categories
    $post_all_cats = array(
        'page_template' => 'page_ss_category.php',
        'comment_status' => 'closed',
        'ping_status' => 'open',
        'post_author' => 1,
        'post_content' => '',
        'post_name' => 'all-categories',
        'post_status' => 'publish',
        'post_title' => 'All categories',
        'post_type' => 'page',
    );
    $check_all_cats = check_page_by_slug('all-categories');
    if (empty($check_all_cats)) {
        // wp_insert_post( $post_all_cats );
    }

    //page edit advertiser
    $post_edit_adv = array(
        'page_template' => 'page-edit-advertiser.php',
        'comment_status' => 'closed',
        'ping_status' => 'open',
        'post_author' => 1,
        'post_content' => '[ss_edit_advertiser]',
        'post_name' => 'edit-advertiser',
        'post_status' => 'publish',
        'post_title' => 'Edit advertiser',
        'post_type' => 'page',
    );
    $check_edit_adv = check_page_by_slug('edit-advertiser');
    if (empty($check_edit_adv)) {
        //wp_insert_post( $post_edit_adv );
    }
}

add_filter('page_template', 'edit_advertiser_page_template');
add_action('ss_css', 'load_ss_css');

function load_ss_css() {
    
}

function edit_advertiser_page_template($page_template) {
    if (is_page('my-custom-page-slug')) {
        $page_template = dirname(__FILE__) . '/web/edit-.php';
    }
    return $page_template;
}

function ss_rewrite_rule() {
    
    /**
     * Remove the following add_reqrite_rule() to get rid of the redirect from Petite to Petite Fashion
     */
    add_rewrite_rule(
        'brands-and-boutiques/petite/?$', 'index.php?pagename=brands-and-boutiques&ss_cat=petite&ss_sub_cat=petite-fashion', 'top'
    );       

    add_rewrite_rule(
        'style-advice-blog/ss_category/([^/]*)/?$', 'index.php?pagename=brands-and-boutiques&ss_cat=$matches[1]', 'top'
    );

    add_rewrite_rule(
        'blog/ss_category/([^/]*)/?$', 'index.php?pagename=brands-and-boutiques&ss_cat=$matches[1]', 'top'
    );

    add_rewrite_rule(
        'brands-and-boutiques/?$', 'index.php?pagename=brands-and-boutiques', 'top'
    );

    add_rewrite_rule(
        'brands-and-boutiques/([^/]*)/?([^/]*)/?$', 'index.php?pagename=brands-and-boutiques&ss_cat=$matches[1]&ss_sub_cat=$matches[2]', 'top'
    );
    add_rewrite_rule(
        'brands-and-boutiques/([^/]*)/?([^/]*)/page/([0-9]{1,})/?$', 'index.php?pagename=brands-and-boutiques&ss_cat=$matches[1]&ss_sub_cat=$matches[2]&paged=$matches[3]', 'top'
    );
    add_rewrite_rule(
        'store/([^/]*)/?$', 'index.php?pagename=store&ss_advertiser=$matches[1]', 'top'
    );
    add_rewrite_rule(
        'brands-and-boutiques/([^/]*)/?([^/]*)/?([^/]*)/?$', 'index.php?pagename=brands-and-boutiques&ss_cat=$matches[3]', 'top'
    );        
     
}

add_action('init', 'ss_rewrite_rule');

/**
 * This function allows for wp_redirect() to execute successfully.
 */
function doOutputBuffer() {
    ob_start();
}

add_action('init', 'doOutputBuffer');

function ss_query_vars($query_vars) {
    $query_vars[] = 'ss_cat';
    $query_vars[] = 'ss_sub_cat';
    $query_vars[] = 'ss_advertiser';
    $query_vars[] = 'paged';

    return $query_vars;
}

add_filter('query_vars', 'ss_query_vars');

function create_firstpost($user_id) {


    if (isset($_POST['action'])) {
        if (( $_POST['action'] == "createuser")) {
            if ($_POST['role'] == "brand_role") {
                $post_type = "brands";
            } elseif ($_POST['role'] == "boutique_role") {
                $post_type = "boutiques";
            }
            $post1 = array(
                'post_title' => "Untitled Company",
                'post_type' => $post_type,
                'post_status' => 'draft',
                'post_author' => $user_id,
            );

            $post_id = wp_insert_post($post1);
        }
    }
}

add_action('user_register', 'create_firstpost', 10, 1);

/* Change Login Form */

function my_loginlogo() {
    echo '<style type="text/css">
    h1 a {
      background-image: url(' . get_template_directory_uri() . '/images/logo-new.png) !important;
    }
  </style>';
}

add_action('login_head', 'my_loginlogo');

function my_loginURL() {
    return site_url();
}

add_filter('login_headerurl', 'my_loginURL');

function my_logincustomCSSfile() {
    wp_enqueue_style('login-styles', SOSENSATIONAL_URL . '/login_styles.css');
}

add_action('login_enqueue_scripts', 'my_logincustomCSSfile');

function wpss_plugin_activation() {

    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'wpss_plugin_activation');

function wpss_plugin_deactivation() {

    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'wpss_plugin_deactivation');

function pbd_alp_init() {
    // Queue JS and CSS
	wp_enqueue_script('jquery');
    wp_enqueue_script(
        'pbd-alp-load-posts', plugin_dir_url(__FILE__) . 'sosensational-script.js', array('jquery'), '1.0', false
    );
    wp_enqueue_script('custom-scripts', plugin_dir_url(__FILE__) . 'js/custom-scripts.js', array('pbd-alp-load-posts'), '120215', true);
    wp_enqueue_script('tagsinput-scripts', plugin_dir_url(__FILE__) . 'js/tagsinput/bootstrap-tagsinput.min.js', array('bootstrap-js'), '130215', false);
    wp_localize_script('custom-scripts', 'AjaxObject', array('ss_ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_script('flexslider-js', plugin_dir_url(__FILE__) . 'js/flexslider/jquery.flexslider.js', array('jquery'), 19032015, false);
    wp_enqueue_style('tags-input', plugins_url('SoSensational/js/tagsinput/bootstrap-tagsinput.css'));
}

add_action('wp_enqueue_scripts', 'pbd_alp_init');

function enqueueAdminStylesheets() {
    wp_enqueue_style('sass-admin-styles', plugins_url('SoSensational/styles/dist/admin-styles.css'));
}

add_action('admin_enqueue_scripts', 'enqueueAdminStylesheets');

function deleteSelectedProduct() {
    if (isset($_POST['productToDelete']) && !empty($_POST['productToDelete'])) {
        $postId = $_POST['productToDelete'];
        wp_delete_post($postId);
    }
    die();
}

add_action('wp_ajax_ss_delete_action', 'deleteSelectedProduct');
add_action('wp_ajax_nopriv_ss_delete_action', 'deleteSelectedProduct');

function wpa54064_inspect_scripts() {
    global $wp_scripts;
    foreach ($wp_scripts->queue as $handle) :
        echo $handle . ' | ';
    endforeach;
}


/**
 * SEO title - filter provided by Yoast SEO Plugin
 * 
 * @param string $str SEO title provided be Yoast SEO Plugin
 * @return string $str | $seoTitle SEO title for a fiven ss_category
 */
function addSeoTitleToSsCategory($str)
{
    global $query_string;
    parse_str($query_string, $argumentsArray);
    
    if( ! key_exists('ss_sub_cat', $argumentsArray) && key_exists('ss_cat', $argumentsArray)) {
        $currentTerm = get_term_by('slug', $argumentsArray['ss_cat'], 'ss_category');     
    } elseif(key_exists('ss_sub_cat', $argumentsArray) && key_exists('ss_cat', $argumentsArray) ) {
        $currentTerm = get_term_by('slug', $argumentsArray['ss_sub_cat'], 'ss_category');
    } else {
        return $str;
    }
    
    $termId = $currentTerm->term_id;
    
    if ($termId) {
        $termMeta = get_option("taxonomy_$termId");
        $seoTitle = $termMeta['seo-title'];          
    } else {
        $args = array(
            'post_type' =>  array('brands', 'boutiques'),
            'name'    =>  $argumentsArray['ss_cat'],
        );
        $currentAdvertiser = get_posts($args);
        
        $currentSeoData = get_post_meta($currentAdvertiser[0]->ID, '_seo_metadata', true);
        $seoTitle = $currentSeoData['seo-title'];
    }
  
    return "$seoTitle";
    
   
}

add_filter('wpseo_title', 'addSeoTitleToSsCategory');

/**
 * SEO description - filter provided by Yoast SEO Plugin
 * 
 * @param string $desc SEO description provided be Yoast SEO Plugin
 * @return string $desc | $seoDescription SEO description for a fiven ss_category
 */
function addSeoDescriptionToSsCategory($desc)
{
    global $query_string;
    parse_str($query_string, $argumentsArray);
    
    if( ! key_exists('ss_sub_cat', $argumentsArray) && key_exists('ss_cat', $argumentsArray)) {
        $currentTerm = get_term_by('slug', $argumentsArray['ss_cat'], 'ss_category');     
    } elseif(key_exists('ss_sub_cat', $argumentsArray) && key_exists('ss_cat', $argumentsArray) ) {
        $currentTerm = get_term_by('slug', $argumentsArray['ss_sub_cat'], 'ss_category');
    } else {
        return $desc;
    }

    $termId = $currentTerm->term_id;
    
    if($termId) {
        $termMeta = get_option("taxonomy_$termId");
        $seoDescription = $termMeta['seo-description'];         
    } else {
        $args = array(
            'post_type' =>  array('brands', 'boutiques'),
            'name'    =>  $argumentsArray['ss_cat'],
        );
        $currentAdvertiser = get_posts($args);
        
        $currentSeoData = get_post_meta($currentAdvertiser[0]->ID, '_seo_metadata', true);
        $seoDescription = $currentSeoData['seo-description'];                
    }
    
  
    
    return "$seoDescription";
    
   
}

add_filter('wpseo_metadesc', 'addSeoDescriptionToSsCategory');
