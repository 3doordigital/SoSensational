<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
Plugin Name: Convert a link
Description: Convert-a-link switches normal product links on your website into affiliate links with little effort
Version:     1.0
Author:      digitalwindow
Author URI:  http://www.affiliatewindow.com/
Plugin URI:  https://wordpress.org/plugins/convert-a-link
*/

###############################################
# MENU PAGES #
###############################################
add_action( 'admin_menu', "convert_a_link_settings");
function convert_a_link_settings(){
    add_menu_page (
        'Convert-a-link',
        'Convert-a-link',
        'manage_options',
        'convert-a-link/convert-a-link-admin.php',
        '',
        plugins_url( 'icon.png', __FILE__ )
    );
}

$publisherId = get_option('cal_publisherId');
wp_enqueue_script('convert-a-link', 'https://www.dwin2.com/pub.'. $publisherId .'.min.js', array(), false, true);


##########################################################
# ADMIN NOTIFICATION #
##########################################################
function convert_a_link_admin_notice() {
    $publisherId = get_option('cal_publisherId');

    if(empty($publisherId)) {
    ?>
        <div class="error">
            <p><?php _e( '<a href="'.admin_url('admin.php?page=convert-a-link/convert-a-link-admin.php').'">
                Enter your publisherId to cofigure Convert-a-link!</a>', 'my-text-domain' ); ?>
            </p>
        </div>
    <?php
    }
}
add_action( 'admin_notices', 'convert_a_link_admin_notice' );

##########################################################
# DEACTIVATION #
##########################################################
register_deactivation_hook( __FILE__, 'convertALinkUninstall' );
function convertALinkUninstall() {
    delete_option('cal_publisherId');
}
