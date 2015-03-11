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
	private function __construct() {
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
        
        add_action( 'admin_menu', array( $this, 'create_menu' ) );
        
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
        
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
        
		$this->run_plugin();
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
         wp_enqueue_script( 'wp_aff_functions', $this->plugin_url . 'js/front-end.js' );
         wp_localize_script( 'wp_news-man-functions', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
    
    public function register_styles() {
        
        wp_enqueue_style( 'wp_news-man-style', $this->plugin_url . 'css/front-end.css' );
        
	}
     
    public function admin_register_scripts() {
        
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'wp_news-man-admin-functions', $this->plugin_url . 'js/functions.js' );
	}
    
    public function admin_register_styles() {
        wp_enqueue_style( 'wp_news-man-admin-style', $this->plugin_url . 'css/admin.css' );
        wp_enqueue_style( 'fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
	}
     
    public function create_menu() {
        
        add_object_page( 'Newsletter Manager', 'Newsletter Manager', 'manage_options', 'newsletter-manager', array( $this, 'main_page' ), $this->admin_icon );
        
        $this->main_page = add_submenu_page('newsletter-manager', 'Newsletter Manager', 'View All', 'manage_options', 'newsletter-manager', array( $this, 'main_page' ));
        $this->sub_page = add_submenu_page('newsletter-manager', 'Settings', 'Settings', 'manage_options', 'newsletter-manager-2', array( $this, 'settings_page' ));
        
	}
    
    public function register_widgets() {
        
    }
    
    public function register_post_types() {
        
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
    
    public function settings_page() {
        echo 'hello';
    }
    
    private function run_plugin() {
	
    }
}
WordPress_Newsletter_Manager::get_instance();