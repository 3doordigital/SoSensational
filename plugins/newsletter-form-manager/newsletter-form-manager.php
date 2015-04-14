<?php
/*
  Plugin Name: WordPress Newsletter Form Manager
  Plugin URI: 
  Description: Create a shop on your WordPress site using the most popular Affiliate networks.
  Version: 0.1b
  Author: Dan Taylor
  Author URI: http://www.tailoredmarketing.co.uk
  License: GPL V3
 */
class WordPress_Newsletter_Manager {
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
    private $text_domain    = 'wpnewsman';
    private $admin_icon     = 'dashicons-edit';
    private $option_name    = 'wp_newsletter_man';
	/**
	 * Creates or returns an instance of this class.
	 */
	public static function get_instance() {
		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	/**
	 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
	 */
	public function __construct() {
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
        
        $this->option = get_option( $this->option_name );
        
		load_plugin_textdomain( $this->text_domain, false, 'lang' );
        
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
        
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        
		add_action( 'wp_ajax_wp_news_man_cm_clients', array( $this, 'get_cm_clients' ) );
		
		add_action( 'admin_post_wp_news_man_api_settings_save', array( $this, 'save_api_settings' ) ) ;
		
        add_action( 'admin_menu', array( $this, 'create_menu' ) );
        
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
        
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
        
		//$this->run_plugin();
	}
    public function get_option() {
        $option = $this->option;
        return $option;
    }
	
	public function get_plugin_url() {
		return $this->plugin_url;
	}
    
	public function get_plugin_path() {
		return $this->plugin_path;
	}
    
    public function activation() {
	}
    
    public function deactivation() {
	}
    
    public function register_scripts() {
         wp_enqueue_script( 'jquery' );
         wp_enqueue_script( 'wp_newsman_frontend_js', $this->plugin_url . 'js/front-end.js' );
         wp_localize_script( 'wp_newsman_frontend_js', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
    
    public function register_styles() {
        
        wp_enqueue_style( 'wp_newsman_frontend_css', $this->plugin_url . 'css/front-end.css' );
        
	}
     
    public function admin_register_scripts() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'wp_newsman_backend_js', $this->plugin_url . 'js/functions.js' );
	}
    
    public function admin_register_styles() {
        wp_enqueue_style( 'wp_newsman_admin_css', $this->plugin_url . 'css/admin.css' );
        wp_enqueue_style( 'fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
	}
     
    public function create_menu() {
        
        $this->sub_page = add_submenu_page('edit.php?post_type=wp_news_man', 'Settings', 'Settings', 'manage_options', 'news-manager-settings', array( $this, 'settings_page' ));
        
	}
    
    public function register_widgets() {
        
    }
    
    public function register_post_types() {
		$labels = array(
            'name'               => _x( 'Newsletter', 'post type general name', 'your-plugin-textdomain' ),
            'singular_name'      => _x( 'Newsletter', 'post type singular name', 'your-plugin-textdomain' ),
            'menu_name'          => _x( 'Newsletter', 'admin menu', 'your-plugin-textdomain' ),
            'name_admin_bar'     => _x( 'Newsletter', 'add new on admin bar', 'your-plugin-textdomain' ),
            'add_new'            => _x( 'Add New', 'book', 'your-plugin-textdomain' ),
            'add_new_item'       => __( 'Add New Subscriber', 'your-plugin-textdomain' ),
            'new_item'           => __( 'New Subscriber', 'your-plugin-textdomain' ),
            'edit_item'          => __( 'Edit Subscribers', 'your-plugin-textdomain' ),
            'view_item'          => __( 'View Subscribers', 'your-plugin-textdomain' ),
            'all_items'          => __( 'Subscribers', 'your-plugin-textdomain' ),
            'search_items'       => __( 'Search Subscribers', 'your-plugin-textdomain' ),
            'parent_item_colon'  => __( 'Parent Subscriber:', 'your-plugin-textdomain' ),
            'not_found'          => __( 'No Subscribers found.', 'your-plugin-textdomain' ),
            'not_found_in_trash' => __( 'No Subscribers found in Trash.', 'your-plugin-textdomain' )
        );
       	$args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
			'capabilities' => array(
				'create_posts' => false, // Removes support for the "Add New" function
			  ),
			'map_meta_cap' => true,
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon' 		 => $this->admin_icon,
            'supports'           => array( 'title', 'editor', 'thumbnail' )
        );
		register_post_type( 'wp_news_man', $args );
    }
    
    public function register_taxonomies() {
        
    }
    
    public function main_page() {
    ?>
        <div class="wrap">
            <h2>Newsletter Manager <a href="<?php print admin_url('admin.php?page=newsletter-manager&action=add-newsletter'); ?>" class="add-new-h2">Add Newsletter</a></h2>
        </div>
    <?php
    }
	
    public function save_api_settings() {
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_news_man_api_settings_save' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) ); 
		$data = $this->get_option();
		$data['api_settings'] = array();
		foreach( $_POST as $key=>$value ) {
			$data['api_settings'][$key] = $value;
		}
		
		update_option( $this->option_name, $data );
		$url = add_query_arg( 'msg', 3, $_REQUEST['_wp_http_referer'] );
        wp_safe_redirect( $url );
	}
	
    public function settings_page() { ?>
        <div class="wrap">
            <h2>Newsletter Settings</h2>
            <h2 class="nav-tab-wrapper">
            	<a class="nav-tab <?php echo ( !isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] == 0 ? 'nav-tab-active' : '' ); ?>" href="<?php echo admin_url('edit.php?post_type=wp_news_man&page=news-manager-settings&tab=0'); ?>">General Settings</a>
                <a class="nav-tab <?php echo ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 1 ? 'nav-tab-active' : '' ); ?>" href="<?php echo admin_url('edit.php?post_type=wp_news_man&page=news-manager-settings&tab=1'); ?>">API Settings</a>
            </h2>
            <?php if( !isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] == 0 ) { ?>
            	<h3>General Settings</h3>
                <form method="POST" id="wp_news_man" action="<?php echo admin_url('admin-post.php'); ?>">
                    <div class="news_man_right">
                        <div class="wp-box">
                            <h3>Details</h3>
                            <div class="inside">
                                <p>On this page you can set your general settings.</p>
                            </div>
                            <div class="actions">
                                <input type="hidden" value="wp_comp_man_gen_settings_save" name="action" />
                                <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
                                <?php wp_nonce_field( 'wp_comp_man_gen_settings_save', '_wpnonce', FALSE ); ?>
                                <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                                <?php submit_button( 'Save Settings', 'primary', 'save_settings', false ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="news_man_left">
                    <p>The following settings are available.</p>
                    <?php 
						$settings = $this->get_option(); 
						//$settings = $settings['general_settings'];
					?>
                        <table class="form-table">
                    		
                        </table>
                    </div>
                </form>
            <?php 
			} elseif( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 1 ) { 
				require_once ( $this->get_plugin_path().'/campaign-monitor/csrest_lists.php' );
				$option = $this->get_option();
			?>
                <h3>API Settings</h3>
                <form method="POST" id="wp_news_man" action="<?php echo admin_url('admin-post.php'); ?>">
                    <div class="news_man_right">
                    <div class="wp-box">
                        <h3>Details</h3>
                        <div class="inside">
                            <p>On this page you can create the form that visitors use to enter the competitions.</p>
                        </div>
                        <div class="actions">
                            <input type="hidden" value="wp_news_man_api_settings_save" name="action" />
                            <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
                            <?php wp_nonce_field( 'wp_news_man_api_settings_save', '_wpnonce', FALSE ); ?>
                            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                            <?php submit_button( 'Save Settings', 'primary', 'save_settings', false ); ?>
                        </div>
                    </div>
                </div>
                <div class="news_man_left">
                    <table class="form-table">
                    	<tr>
                        	<th>API to use</th>
                            <td>
                            	<select name="api_select">
                                    <!--<option <?php echo ( isset( $option['api_settings']['api_select'] ) && $option['api_settings']['api_select'] == 0 ? ' selected ' : '' ); ?> value="0">None (Database Only)</option>-->
                                    <option <?php echo ( isset( $option['api_settings']['api_select'] ) && $option['api_settings']['api_select'] == 1 ? ' selected ' : '' ); ?> value="1">Campaign Monitor</option>
                                    <!--<option <?php echo ( isset( $option['api_settings']['api_select'] ) && $option['api_settings']['api_select'] == 2 ? ' selected ' : '' ); ?> value="2">Mail Chimp</option>-->
                                </select>
                                <p class="description">All options save the data to the database, API options also send to your provider.</p>
                                <hr>
                                <div id="cm-settings">
                                	<h4>Campaign Monitor Settings</h4>
                                	<table class="form-table">
                                        <tr class="cm_api_row">
                                            <th>API Key</th>
                                            <td><input type="text" value="<?php echo ( isset( $option['api_settings']['cm_api_key'] ) ? $option['api_settings']['cm_api_key'] : '' ); ?>" name="cm_api_key" class="regular-text cm_api_key"></td>
                                        </tr>
                                        <tr class="cm_client_row">
                                            <th>Select Client</th>
                                            <td>
                                                <select name="cm_client" class="cm_client">
                                                    <?php 
														if( isset( $option['api_settings']['cm_api_key'] ) ) {
															$this->get_cm_clients( false ); 
														} else { 
													?>
                                                    <option value="-1'">No API Key Set</option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="cm_list_row">
                                            <th>Select list</th>
                                            <td>
                                                <select name="cm_list" class="cm_list">
                                                    <?php 
														if( isset( $option['api_settings']['cm_client'] ) ) {
															$this->get_cm_lists( false ); 
														} else { 
													?>
                                                    <option value="-1'">No Client Set</option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                </form>
            <?php } ?>
        </div>
    <?php
      //print_var($this->option['form_fields']);          
    }
    public function get_cm_clients( $ajax = true ) {
		require_once ( 'campaign-monitor/csrest_general.php' );
		$option = $this->get_option();
		
		$apikey = ( isset( $option['api_settings']['cm_api_key'] ) ? $option['api_settings']['cm_api_key'] : '' );
		
		$auth = array( 'api_key' => $apikey );
		$wrap = new CS_REST_General( $auth );
		
		$result = $wrap->get_clients();
		
		if($result->was_successful()) {
			foreach( $result->response as $res ) {
				echo '<option '. ( isset($option['api_settings']['cm_client'] ) &&  $option['api_settings']['cm_client'] == $res->ClientID ? ' selected ' : '' ) .'value="'.$res->ClientID.'">'.$res->Name.'</option>';
			}
		}	
		if( $ajax ) die();
	}
	
	public function get_cm_lists( $ajax = true ) {
		require_once ( 'campaign-monitor/csrest_clients.php' );
		$option = $this->get_option();
		
		$apikey = ( isset( $option['api_settings']['cm_api_key'] ) ? $option['api_settings']['cm_api_key'] : '' );
		$client = ( isset( $option['api_settings']['cm_client'] ) ? $option['api_settings']['cm_client'] : '' );
		
		$auth = array( 'api_key' => $apikey );
		$wrap = new CS_REST_Clients( $client, $auth );
		
		$result = $wrap->get_lists();
		
		if($result->was_successful()) {
			print_var($result->response);
			foreach( $result->response as $res ) {
				
				echo '<option '. ( isset($option['api_settings']['cm_client'] ) &&  $option['api_settings']['cm_client'] == $res->ListID ? ' selected ' : '' ) .'value="'.$res->ListID.'">'.$res->Name.'</option>';
			}
		}	
		if( $ajax ) die();
	}
	
    private function run_plugin() {
	
    }
}
$wp_news_man = new WordPress_Newsletter_Manager;