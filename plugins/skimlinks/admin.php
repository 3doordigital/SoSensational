<?php

//only enqueue all the admin stuff if is_admin
if ( is_admin() ){
  add_action( 'admin_menu', 'sl_add_options_menu' );
  add_action( 'admin_init', 'sl_register_settings' );
  add_action( 'admin_init', 'sl_register_meta_boxes' );
}

function sl_add_options_menu() {
 	add_options_page('Skimlinks Settings', 'Skimlinks', 'manage_options', 'skimlinks-options', 'sl_options_page');
}

/**
 * Register all the settings used on the Skimlinks settings page (accessed cia admin_init hook)
 * 
 */
function sl_register_settings() {
	register_setting( 'sl-settings', 'sl_publisher_id' );
	register_setting( 'sl-settings', 'sl_enable_subdomain' );
	register_setting( 'sl-settings', 'sl_subdomain' );
	register_setting( 'sl-settings', 'sl_enable_rss_filtering' );
	register_setting( 'sl-settings', 'sl_add_disclosure_badge' );
}

function sl_enqueue_admin_scripts() {
	wp_enqueue_script( 'sl_admin', SL_PLUGIN_URL . '/assets/skimlinks.js', array( 'jquery' ) );
}
add_action( 'admin_print_scripts-settings_page_skimlinks-options', 'sl_enqueue_admin_scripts' );

/**
 * Validates the Skimlinks options (via update_option() hook)
 */
function sl_validate_settings( $option_name, $old_value, $new_value ) {
	switch ( $option_name ) : 
		
		case 'sl_publisher_id' :
			
			//convert the value to uppercase for 1234x5678
			if( $new_value !== strtoupper( $new_value ) ) {
				$new_value = strtoupper( $new_value );
				update_option( $option_name, $new_value );
			}

			if( $new_value > '' && !sl_validate_publisher_id( $new_value ) ) {
				update_option( $option_name, ( sl_validate_publisher_id( $old_value ) ? $old_value : '' ) );
				sl_add_setting_validation_error( 'sl_validation', $option_name, 'The Publisher ID you entered was not valid.' );
			} 
			break;
		
		case 'sl_subdomain' :
			
			if( $new_value > '' && strpos( $new_value, 'http' ) !== 0 ) {
				$new_value = 'http://' . $new_value;
				update_option( $option_name, $new_value );
			}
			
			if( $new_value == '' && sl_is_subdomain_enabled() ) {
				sl_add_setting_validation_error( 'sl_validation', $option_name, 'The subdomain information you entered was not valid' );
				update_option( 'sl_enable_subdomain', '' );
			} else if( $new_value > '' && sl_is_subdomain_enabled() && !sl_validate_subdomain( $new_value ) ) {
				sl_add_setting_validation_error( 'sl_validation', $option_name, 'The subdomain information you entered was not valid' );
				if( $old_value > '' )
					update_option( $option_name, $old_value );
				else
					update_option( $option_name, '' );
			}
			break;
			
	endswitch;
	
}

// use legacy hooks for update_option / add_option (because 'updated_option' was only introduced in 2.9
add_action( 'update_option_sl_publisher_id', 'sl_update_option_sl_publisher_id', 10, 2 );
function sl_update_option_sl_publisher_id( $old_value, $new_value ) {
	sl_validate_settings( 'sl_publisher_id', $old_value, $new_value );
}

add_action( 'update_option_sl_subdomain', 'sl_update_option_sl_subdomain', 10, 2 );
function sl_update_option_sl_subdomain( $old_value, $new_value ) {
	sl_validate_settings( 'sl_subdomain', $old_value, $new_value );
}

add_action( 'add_option_sl_publisher_id', 'sl_update_option_sl_publisher_id', 10, 2 );
function add_option_sl_publisher_id( $name, $new_value ) {
	sl_validate_settings( 'sl_publisher_id', null, $new_value );
}

add_action( 'add_option_sl_subdomain', 'sl_update_option_sl_subdomain', 10, 2 );
function add_option_sl_subdomain( $name, $new_value ) {
	sl_validate_settings( 'sl_subdomain', null, $new_value );
}


function sl_add_setting_validation_error( $option_name, $slug, $message ) {
	$option = array_filter( (array) get_option( $option_name ) );
	$option[$slug] = $message;
	update_option( $option_name, $option );
	
}
  
function sl_settings_display_errors( $setting ) {
	$option = get_option( $setting );
	
	if( !sl_is_footer_js_verified() ) {
		$option['not-verified'] = 'There appears to be an error in calling the Skimlinks Plugin from your theme\'s footer.php file, please ensure your footer.php file includes the standard WordPress function "wp_footer()" above the closing body tag.';
	}
	
	if( sl_is_subdomain_enabled() && !sl_get_subdomain() ) {
		$option['no-subdomain'] = 'Please enter your custom subdomain or unselect "Enable Custom Subdomain"';
	}
	
	if ( is_array( $option ) ) :
		foreach ( $option as $slug => $message) :
			echo "<div id='sl_messages' class='error fade $slug'><p>$message</p></div>";
			unset( $option[$slug] );
		endforeach;
		update_option( $setting, $option );
	endif;
		
}
/**
 * Page display function for Skimlinks Settings
 *
 * Defined during sl_add_options_mena()
 */
function sl_options_page() {

	// run the footer check on every view of the skimlinks settings page
	sl_verify_footer_js();
	?>
	
	<div class="wrap">
		
		<h2><img style="float:left; margin: -3px 10px 0 0;" src="<?php echo SL_PLUGIN_URL ?>/assets/skimlinks-logo.png" />Skimlinks Settings</h2>
		
		<?php sl_settings_display_errors('sl_validation'); ?>

		<form method="post" action="options.php">
			<table class="form-table">
				
				<tr valign="top">
					<th scope="row"><strong>Skimlinks Publisher ID</strong></th>
					<td>
						<input type="text" name="sl_publisher_id" value="<?php echo get_option('sl_publisher_id'); ?>" />
						<span class="description">Enter your Skimlinks Publisher ID, log in <a target="_blank" href="https://skimlinks.com/index.php?menu=login&return=sites">here</a> to look it up</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><strong>Custom Subdomain</strong></th>
					<td>
						<label for="sl_enable_subdomain">
							<input type="checkbox" name="sl_enable_subdomain" id="sl_enable_subdomain" <?php echo get_option('sl_enable_subdomain') ? ' checked="checked" ' : '' ?> />
							Tick to enable Custom Subdomain (direct your users through a subdomain of your own site... <a target="_blank" href="https://skimlinks.com/index.php?menu=login&return=customdomain">more info</a>).
						</label>
					</td>
				</tr>
				
				<tr valign="top" id="subdomain-options" class="<?php echo !get_option('sl_enable_subdomain') ? 'hide-if-js' : '' ?>">
					<th scope="row"></th>
					<td>
						<input type="text" name="sl_subdomain" class="regular-text" value="<?php echo get_option('sl_subdomain'); ?>" />
						<span class="description">Enter CNAME record (click here for <a target="_blank" href="https://skimlinks.com/index.php?menu=login&return=customdomain">step-by-step</a> instructions)</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><strong>RSS Filtering</strong></th>
					<td>
						<label for="sl_enable_rss_filtering">
							<input type="checkbox" name="sl_enable_rss_filtering" id="sl_enable_rss_filtering" <?php echo get_option('sl_enable_rss_filtering') ? ' checked="checked" ' : '' ?> />
							Tick to modify all external links in your RSS feed to use Skimlinks
						</label>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><strong>Disclosure/Referral Badge</strong></th>
					<td>
						<label for="sl_add_disclosure_badge">
							<input type="checkbox" name="sl_add_disclosure_badge" id="sl_add_disclosure_badge" <?php echo get_option('sl_add_disclosure_badge') ? ' checked="checked" ' : '' ?> />
							Tick to add Disclosure/Referral Badge to posts (please make sure you have accepted T&amp;Cs first to implement... <a target="_blank" href="https://skimlinks.com/index.php?menu=login&return=referral">click here</a>).
						</label>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><strong>Skimlinks Status</strong></th>
					<td>
						<?php if( sl_is_plugin_active() ) : ?>
							<span style="color: green">Skimlinks is working correctly.</span>
						<?php else: ?>
							<span style="color: red">Skimlinks is not configured, please adjust the settings above</span>
						<?php endif; ?>
						
					</td>
				</tr>
				
			</table>
			
			<input type="hidden" name="action" value="update" />
			
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			
			<?php 
			settings_fields( 'sl-settings' );
			
			// Output any sections defined for page sl-settings
			do_settings_sections('sl-settings'); 
			?>
		</form>
	</div>
	<?php
}

function sl_admin_message_notification() {
	
	if( ( isset( $_GET['page'] ) && $_GET['page'] == 'skimlinks-options' ) || sl_is_plugin_active() )
		return;
	
	?>
	<div id="message" class="updated fade" style="">
		<p>Skimlinks is not configured, please update the Skimlinks settings to enable Skimlinks <a class="button" href="<?php echo SL_ADMIN_URL ?>">Update Options</a></p>
	</div>
	<?php
	 
}
add_action( 'admin_notices', 'sl_admin_message_notification' );

/*
 * Edit Post / Edit Page meta box 
 */

/**
 * Registers the meta boxes for add post / add page
 * 
 * @return void
 */
function sl_register_meta_boxes() {
	add_meta_box( 'sl-post-meta-box', 'Skimlinks', 'sl_post_meta_box', 'post', 'side', 'default' );
	add_meta_box( 'sl-post-meta-box', 'Skimlinks', 'sl_post_meta_box', 'page', 'side', 'default' );
}

/**
 * Outputs the content for the meta boxes for add post / add page
 * 
 * @return void
 */
function sl_post_meta_box() {
	
	if( sl_is_plugin_active() ) : ?>
	<p>
		Congratulations! Skimlinks is successfully installed and is monetising the links on your blog.
	</p>
	
	<?php else : ?>
	<p>
		Skimlinks is not configured - please <a href="<?php echo SL_ADMIN_URL ?>">modify settings</a>.
	</p>
	<?php endif;
} 
