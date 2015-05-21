<?php
/*
  Plugin Name: Affiliate Shop &raquo; Feed Manager
  Plugin URI: 
  Description: Feed Manager for WP Affiliate Shop
  Version: 1.0.0
  Author: 3 Door Digital
  Author URI: http://www.3doordigital.com
  License: GPL V3
 */
 require_once('inc/base_functions.php');
 
 /**
 * Allows management of feeds from affilate networks
 *
 *
 * @copyright  2015 3 Door Digital
 * @license    GPL v3
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0
 */ 
 class WordPress_Affiliate_Shop_Manager {
 	private $plugin_path;
	private $plugin_url;
    private $text_domain    = 'wpaffman';
    private $admin_icon     = 'dashicons-admin-generic';
    private $option_name    = 'wp_aff_man';
	private $page_title 	= 'Affiliate Feed Manager';
	public function __construct() {
		
		// Globals
		
		//Set Variables
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
        $this->option = get_option( $this->option_name );
		
		// WP Hooks
		load_plugin_textdomain( $this->text_domain, false, 'lang' );
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		
		// Actions
		add_action( 'admin_menu', array( $this, 'create_menu' ) );
		
		// Filters
	}
	
	/**
	* Fires on plugin activation. Sets inital options
	*
	* @return nothing
	*/ 
	public function activation() {
		$this->create_table();
		
		if( $this->option == FALSE ) {
			$array = array(
				'schedule' => 1
			);
			update_option( $this->option_name, $array );
		}
	}
	
	/**
	* Fires on plugin deactivation. 
	*
	* @return nothing
	*/
	public function deactivation() {
		
	}
	
	/**
	* Returns array of options 
	*
	* @return array $options
	*/
	public function get_option() {
		return $this->option;
	}
	
	/**
	* Returns full url of plugin folder.
	*
	* @return string Full URL of plugin
	*/
	public function get_plugin_url() {
		return $this->plugin_url;
	}
	
	/**
	* Returns full path of plugin folder.
	*
	* @return string full path of plugin
	*/
	public function get_plugin_path() {
		return $this->plugin_path;
	}
	
	/**
	* Detects $_GET['msg'] number and calls render_msg
	*
	* @return nothing
	*/
	public function parse_message() {
        if ( ! isset ( $_GET['msg'] ) )
            return;

        $text = FALSE;
        
        switch($_GET[ 'msg' ]) {
            case 1 :
                $this->msg_text = 'Updated!';
                $this->msg_class = 'updated';
                break;
            case 2 :
                $this->msg_text = 'Error!';
                $this->msg_class = 'error';
                break;
            case 3 :
                $this->msg_text = 'Deleted!';
                $this->msg_class = 'updated';
                break;
            case 4 :
                $this->msg_text = 'Category Added!';
                $this->msg_class = 'updated';
                break;
            case 5 :
                $this->msg_text = 'Error! Category Name Already Exists!';
                $this->msg_class = 'error';
                break;
             case 6 :
                $this->msg_text = 'Products Added';
                $this->msg_class = 'updated';
                break;
            case 7 :
                $this->msg_text = 'Category Updated!';
                $this->msg_class = 'updated';
                break;
			case 8 :
                $this->msg_text = 'Colour Updated!';
                $this->msg_class = 'updated';
                break;
			case 9 :
                $this->msg_text = 'Size Updated!';
                $this->msg_class = 'updated';
                break;
			
        }
        
        if ( $this->msg_text )
            add_action( 'admin_notices', array ( $this, 'render_msg' ) );
    }
	
	/**
	* Renders WordPress style admin notice
	*
	* @param  string  $this->msg_class set from parse_message
	* @param  string  $this->msg_text set from parse_message	
	* @return echo to screen
	*/ 
    public function render_msg() {
        echo '<div id="message" class="' . $this->msg_class . '"><p>'
            . $this->msg_text . '</p></div>';
    }
	
	/**
	* Creates WordPress Menus
	*
	* @return nothing
	*/ 
	public function create_menu() {
        add_object_page( 'Affiliate Feed Manager', 'Affiliate Feed Manager', 'manage_options', 'affiliate-feed-manager', array( $this, 'main_page' ), $this->admin_icon );
        $this->main_page = add_submenu_page('affiliate-feed-manager', 'Affiliate Feed Manager', 'Affiliate Feed Manager', 'manage_options', 'affiliate-feed-manager', array( $this, 'main_page' ));
		$this->settings = add_submenu_page('affiliate-feed-manager', 'Settings', 'Settings', 'manage_options', 'affiliate-feed-manager/settings', array( $this, 'settings_page' ));
		
        add_action( "load-$this->settings", array ( $this, 'parse_message' ) );
        add_action( "load-$this->main_page", array ( $this, 'parse_message' ) );
	}
	
	/**
	* Renders Outputs page title on admin pages
	*
	* @param  string  $this->page_title
	* @return echo to screen
	*/ 
	private function page_title() {
		echo '<h2>'.$this->page_title.'</h2>';	
	}
	
	/**
	* Creates table for feed data if it doesn't exist
	*
	* @return nothing
	*/ 
	public function create_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . "feed_data"; 
		
		if (!empty ($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty ($wpdb->collate))
			$charset_collate .= " COLLATE {$wpdb->collate}";
				 
		  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			product_id varchar(255) NOT NULL,
			product_aff varchar(255) DEFAULT NULL,
			product_title varchar(255) DEFAULT NULL,
			product_brand varchar(255) DEFAULT NULL,
			product_image varchar(255) DEFAULT NULL,
			product_desc longtext DEFAULT NULL,
			product_price decimal(12,2) DEFAULT NULL,
			product_rrp decimal(12,2) DEFAULT NULL,
			product_link varchar(255) DEFAULT NULL,
			UNIQUE KEY product_id (product_id)
		) {$charset_collate};";
				
		require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/upgrade.php');
		dbDelta($sql);	
	}
	
	/**
	* The output for the main page of the plugin
	*
	* @return echo to screen
	*/ 
	public function main_page() { ?>
		<div class="wrap">
			<?php $this->page_title(); ?>
            <h3>Update Feed</h3>
            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">                
                <table class="form-table">
                    <tr>
                        <th>Update frequency</th>
                        <td>
                            <select name="<?php echo $this->option_name; ?>[product_update][frequency]" value="<?php echo ( isset( $option['product_update']['frequency'] ) ? $option['product_update']['frequency'] : '' ); ?>" id="<?php echo $this->option_name; ?>[product_update][frequency]">
                                <option <?php if( isset( $option['product_update']['frequency'] ) ) selected( $option['product_update']['frequency'], 0, true ); ?> value="0">Manual</option>
                                <option <?php if( isset( $option['product_update']['frequency'] ) ) selected( $option['product_update']['frequency'], 1, true ); ?> value="1">Daily</option>
                                <option <?php if( isset( $option['product_update']['frequency'] ) ) selected( $option['product_update']['frequency'], 2, true ); ?> value="2">Weekly</option>
                                <option <?php if( isset( $option['product_update']['frequency'] ) ) selected( $option['product_update']['frequency'], 3, true ); ?> value="3">Monthly</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Manual Update</th>
                        <td><a href="<?php echo $_SERVER['REQUEST_URI']; ?>" class="button button-secondary manual_feed_update">Run Manual Update</a></td>
                    </tr>
                    <tr class="prod_update_row">
                        <th>Update Progress</th>
                        <td>
                            <span class="update_percent">0%</span> <div id="update_cont"><div id="update_progress"></div></div> <span class="total_update"></span>
                        </td>
                    </tr>
                </table>
                <table id="tableout">
                    <tr><th>Post ID</th><th>Old Title</th><th>Merchant</th><th>New ID</th><th>Found Title</th><th>Affilliate</th><th>Found By</th><th>Updated?</th><th>Sale</th></tr>
                    <tbody>
                    
                    </tbody>
                </table>
                <?php submit_button( 'Save' ); ?>
                <input type="hidden" value="wp_man_save_feed" name="action" />
                <?php wp_nonce_field( 'wp_man_save_feed', $this->option_name . '_nonce', TRUE ); ?>
            </form>
        </div>
<?php }
	
	/**
	* The output for the settings page of the plugin
	*
	* @return echo to screen
	*/ 
	public function settings_page() {
		
	}
	
 }
 
 $feed_man = new WordPress_Affiliate_Shop_Manager();