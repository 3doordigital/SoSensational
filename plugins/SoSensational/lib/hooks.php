<?php
/**
 * Custom hooks for the SoSensational Plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data April 2015
 */

add_filter('manage_products_posts_columns', 'addProductsCustomColumn');
add_action('manage_products_posts_custom_column', 'processBBColumn', 10, 2);
add_filter('lost_password', 'ssPreventPasswordReset', 10, 2);
add_filter('login_message', 'ssAddContactAdminMessage');
add_action('admin_init', 'ssSettingsForHomepage');
add_action('before_delete_post', 'deleteCorrespondingAdvertiserCategory');
//add_action('wp_trash_post', 'beforeTrash');

function deleteCorrespondingAdvertiserCategory($postId)
{
    if ('advertisers_cats' === get_post_type($postId)) {
        return $postId;
    }

    if ('brands' === get_post_type($postId) || 'boutiques' === get_post_type($postId)) {
        $deletedAdvertiser = get_post($postId);
        $deletedAdvertiserTitle = $deletedAdvertiser->post_title;
        $deletedAdvertiserAuthor = $deletedAdvertiser->post_author;

        $args = array(
            'post_type' => 'advertisers_cats',
            'post_status' => array('publish', 'draft', 'pending'),
            'author' => $deletedAdvertiserAuthor,
            'posts_per_page' => -1,
            'field' => 'ids'
        );

        $advertiserCategoriesByAuthor = get_posts($args);

        foreach($advertiserCategoriesByAuthor as $category) {
            if ($category->post_title == $deletedAdvertiserTitle) {
                wp_delete_post($category->ID, true);
            }
        }
    }

    return $postId;
}

function ssSettingsForHomepage()
{
    add_settings_section('homepage_button_section', 'Homepage Buttons Text', 'homepageButtonSectionCallback', 'reading');
    add_settings_field('homepage_button_1', 'Homepage Button One Text', 'homepageButtonOneCallback', 'reading', 'homepage_button_section');
    add_settings_field('homepage_button_2', 'Homepage Button Two Text', 'homepageButtonTwoCallback', 'reading', 'homepage_button_section');
    add_settings_field('homepage_button_3', 'Homepage Button Three Text', 'homepageButtonThreeCallback', 'reading', 'homepage_button_section');
    
    register_setting('reading', 'homepage_button_1');
    register_setting('reading', 'homepage_button_2');
    register_setting('reading', 'homepage_button_3');
}

function homepageButtonSectionCallback()
{
    
}

function homepageButtonOneCallback()
{
    echo '<input type="text" name="homepage_button_1" id="homepage_button_1" value="' . get_option( 'homepage_button_1' ) . '">';
}

function homepageButtonTwoCallback()
{
    echo '<input type="text" name="homepage_button_2" id="homepage_button_2" value="' . get_option( 'homepage_button_2' ) . '">';
}

function homepageButtonThreeCallback()
{
    echo '<input type="text" name="homepage_button_3" id="homepage_button_3" value="' . get_option( 'homepage_button_3' ) . '">';
}

/**
 * If a user wanted to reset her password, a message to contact a representative is displayed.
 * 
 * @param string $message
 * @return string
 */
function ssAddContactAdminMessage($message)
{
    if(isset($_GET['h'])) {
        $message = '<div id="login_error"><p>Please contact your SoSensational representative to request a new password.</p></div>';
    }    
    return $message;
}

/**
 * 'Lost your password' button redirects to the login page. Password reset is disabled.
 */
function ssPreventPasswordReset()
{
    $redirectUrl = esc_url(add_query_arg('h', 'p', wp_login_url()));
    wp_redirect($redirectUrl);
}


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