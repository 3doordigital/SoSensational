<?php
/*
  Plugin Name: WordPress Aflliate Shop
  Plugin URI: 
  Description: Create a shop on your WordPress site using the most popular Affiliate networks.
  Version: 0.1b
  Author: Dan Taylor
  Author URI: http://www.tailoredmarketing.co.uk
  License: GPL V3
 */
require_once('inc/base_functions.php');
require_once('classes/walkers.php');
require_once('inc/admin_list_tables.php');
require_once('classes/tag-checklist.php');
require_once('inc/widgets.php');
               
class WordPress_Affiliate_Shop {
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
    private $text_domain    = 'wpaffshop';
    private $admin_icon     = 'dashicons-cart';
    private $option_name    = 'wp_aff_apis';
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
		global $wpdb;
		
		$this->type = 'wp_aff_colours';
		$this->table_name = $wpdb->prefix . $this->type . 'meta';
		
		$variable_name = $this->type . 'meta';
		$this->variable_name = $variable_name;
		$wpdb->$variable_name = $this->table_name;
		$wpdb->tables[] = $variable_name;
		
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
        
        $this->option = get_option( $this->option_name );
        
		load_plugin_textdomain( $this->text_domain, false, 'lang' );
        
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		        
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        add_action( 'init', array( $this, 'custom_rewrite_rule' ) );
        add_action( 'admin_menu', array( $this, 'create_menu' ) );
		        
        add_action( 'admin_post_wp_aff_save_api', array ( $this, 'wp_aff_update_settings' ) );
        add_action( 'admin_post_wp_aff_add_category', array ( $this, 'wp_aff_add_category' ) );
        add_action( 'admin_post_wp_aff_edit_category', array ( $this, 'wp_aff_edit_category' ) );
        add_action( 'admin_post_wp_aff_product_search', array ( $this, 'wp_aff_product_search' ) );
        add_action( 'admin_post_wp_aff_add_products', array ( $this, 'wp_aff_add_products' ) );
		add_action( 'admin_post_wp_aff_edit_colours', array ( $this, 'wp_aff_edit_colours' ) );
		
		add_action( 'admin_post_wp_aff_add_man_product', array ( $this, 'wp_aff_add_man_product' ) );
        
		add_action( 'admin_post_nopriv_wp_aff_size_filter', array( $this, 'wp_aff_size_filter' ) );
		add_action( 'admin_post_wp_aff_size_filter', array( $this, 'wp_aff_size_filter' ) );
		
		add_action( 'admin_post_nopriv_wp_aff_colour_filter', array( $this, 'wp_aff_colour_filter' ) );
		add_action( 'admin_post_wp_aff_colour_filter', array( $this, 'wp_aff_colour_filter' ) );
		
		add_action( 'admin_post_nopriv_wp_aff_price_filter', array( $this, 'wp_aff_price_filter' ) );
		add_action( 'admin_post_wp_aff_price_filter', array( $this, 'wp_aff_price_filter' ) );
		
		add_action( 'admin_post_nopriv_wp_aff_brand_filter', array( $this, 'wp_aff_brand_filter' ) );
		add_action( 'admin_post_wp_aff_brand_filter', array( $this, 'wp_aff_brand_filter' ) );
		
        add_action( 'wp_ajax_nopriv_change_faceted_category', array( $this, 'faceted_cat_ajax' ) );
        add_action( 'wp_ajax_change_faceted_category', array( $this, 'faceted_cat_ajax' ) );
		
		add_action( 'wp_ajax_nopriv_remove_facted_element', array( $this, 'remove_facted_element' ) );
        add_action( 'wp_ajax_remove_facted_element', array( $this, 'remove_facted_element' ) );
        
        
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
        
        add_action( 'template_redirect', array( $this, 'term_group_redirect' ) );
        
        add_filter( 'template_include', array( $this, 'load_shop_template' ) );
        
        add_action( 'wp_logout', array( $this, 'wp_logout' ) );
        
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
        
        if( is_admin() ) {
			ini_set("soap.wsdl_cache_enabled", 0);
			// Constants for AWin
			define('API_VERSION', 3);
			define('API_USER_TYPE', 'affiliate'); // (affiliate || merchant)
	
			define('API_KEY', $this->option['awin'] );
	
			define('PS_WSDL', 'http://v'.API_VERSION.'.core.com.productserve.com/ProductServeService.wsdl');
			define('PS_NAMESPACE', 'http://api.productserve.com/');
			define('PS_SOAP_TRACE', false);	// turn OFF when finished testing
			define('API_WSDL', PS_WSDL);
			define('API_NAMESPACE', PS_NAMESPACE);
			define('API_SOAP_TRACE', PS_SOAP_TRACE);
			define('API', 'PS');
			require_once('classes/class.ClientFactory.php');
			$this->oClient = ClientFactory::getClient();
		}
		$this->run_plugin();
		
		update_metadata('wp_aff_colours', 876, 'new_metadata', 'test');
	}
	public function get_plugin_url() {
		return $this->plugin_url;
	}
	public function get_plugin_path() {
		return $this->plugin_path;
	}
    public function wp_logout() {
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy(); 
    }
	
	public function create_metadata_table($table_name, $type) {
			global $wpdb;
		 
			if (!empty ($wpdb->charset))
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			if (!empty ($wpdb->collate))
				$charset_collate .= " COLLATE {$wpdb->collate}";
					 
			  $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
				meta_id bigint(20) NOT NULL AUTO_INCREMENT,
				{$type}_id bigint(20) NOT NULL default 0,
			 
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext DEFAULT NULL,
						 
				UNIQUE KEY meta_id (meta_id)
			) {$charset_collate};";
			 
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	
    /**
     * Place code that runs at plugin activation here.
     */
    public function activation() {
		$this->create_metadata_table($this->table_name, $this->type);
	}
    /**
     * Place code that runs at plugin deactivation here.
     */
    public function deactivation() {
	}
    /**
     * Enqueue and register JavaScript files here.
     */
    public function register_scripts() {
         wp_enqueue_script( 'jquery' );
         wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-slider' );
         wp_enqueue_script( 'wp_aff_functions', $this->plugin_url . 'js/front-end.js' );
         wp_localize_script( 'wp_aff_functions', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
    /**
     * Enqueue and register CSS files here.
     */
    public function register_styles() {
        
        wp_enqueue_style( 'wp_aff_style', $this->plugin_url . 'css/front-end.css' );
        
	}
     /**
     * Place Menu Item Here
     */
    /**
     * Enqueue and register Admin JavaScript files here.
     */
    public function admin_register_scripts() {
        wp_enqueue_script( 'ajaxcat' );
        wp_enqueue_script( 'admin-categories' );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-effects-highlight' );
        wp_enqueue_script( 'wp_aff_functions', $this->plugin_url . 'js/functions.js' );
	}
    /**
     * Enqueue and register Admin CSS files here.
     */
    public function admin_register_styles() {
        wp_enqueue_style( 'wp_aff_style', $this->plugin_url . 'css/admin.css' );
        wp_enqueue_style( 'fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
	}
     /**
     * Place Menu Item Here
     */
    public function create_menu() {
        
        add_object_page( 'Affiliate Shop', 'Affiliate Shop', 'manage_options', 'affiliate-shop', array( $this, 'admin_page' ), $this->admin_icon );
        //add_menu_page( 'Affiliate Shop', 'Affiliate Shop', 'manage_options', 'affiliate-shop', array( $this, 'admin_page' ), $this->admin_icon, '60' );
		//$this->main_page = add_menu_page( 'Affiliate Shop', 'Affiliate Shop', 'manage_options', 'affiliate-shop', array( $this, 'admin_page' ), $this->admin_icon, 58 ); 
        $this->main_page = add_submenu_page('affiliate-shop', 'Categories', 'Categories', 'manage_options', 'affiliate-shop', array( $this, 'admin_page' ));
		$this->add_products = add_submenu_page('affiliate-shop', 'Add Products', 'Add Products', 'manage_options', 'affiliate-shop/add-products', array( $this, 'add_products' ));
        $this->brands = add_submenu_page('affiliate-shop', 'Brands', 'Brands', 'manage_options', 'affiliate-shop/brands', array( $this, 'list_brands' ));
        
		
		$this->colours = add_submenu_page('affiliate-shop', 'Colours', 'Colours', 'manage_options', 'affiliate-shop/colours', array( $this, 'colours_page' ));
		$this->sizes = add_submenu_page('affiliate-shop', 'Sizes', 'Sizes', 'manage_options', 'affiliate-shop/sizes', array( $this, 'sizes_page' ));
        
		$this->settings = add_submenu_page('affiliate-shop', 'Settings', 'Settings', 'manage_options', 'affiliate-shop/settings', array( $this, 'settings_page' ));
        $this->add_ons = add_submenu_page('affiliate-shop', 'Add Ons', 'Add Ons', 'manage_options', 'affiliate-shop/add-ons', array( $this, 'addons_page' ));
        
        add_action( "load-$this->settings", array ( $this, 'parse_message' ) );
		add_action( "load-$this->add_products", array ( $this, 'parse_message' ) );
		add_action( "load-$this->brands", array ( $this, 'parse_message' ) );
		add_action( "load-$this->colours", array ( $this, 'parse_message' ) );
        add_action( "load-$this->main_page", array ( $this, 'parse_message' ) );
	}
    
    public function register_widgets() {
        register_widget( 'aff_category_widget' );
        register_widget( 'aff_brand_widget' );
        register_widget( 'aff_price_widget' );
        register_widget( 'aff_colour_widget' );
        register_widget( 'aff_size_widget' );
        register_widget( 'aff_active_widget' );
    }
    
    public function custom_rewrite_rule() {
        add_rewrite_tag('%shop-cat%', '([^&]+)');
        add_rewrite_tag('%shop-brand%', '([^&]+)');
        
        add_rewrite_rule('^shop/([^/]+)/?$','index.php?page_id=37&shop-cat=$matches[1]');
        add_rewrite_rule('^shop/brand/([^/]+)/?$','index.php?page_id=37&shop-brand=$matches[1]');
        add_rewrite_rule('^shop/([^/]+)/page/?([0-9]+)/?$','index.php?page_id=37&shop-cat=$matches[1]&paged=$matches[2]');
		
		
    }
    
    public function term_group_redirect() {
        global $wp_query;
        if( isset( $wp_query->query_vars['shop-cat'] ) ) {
            $term = get_term_by( 'slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories' );
            if( $term->term_group > 1 ) {
                $alias = get_term_by( 'id', $term->term_group, 'wp_aff_categories' );
                wp_redirect( get_site_url().'/shop/'.$alias->slug, 301);
                exit;
            } 
        }
    }
    
    public function load_shop_template($template) {
         if( get_the_ID() == $this->option['shop_page'] || is_page( 'search' ) ) {
             if ( $overridden_template = locate_template( 'shop-template.php' ) ) {
               load_template( $overridden_template );
             } else {
               load_template( dirname( __FILE__ ) . '/templates/shop-template.php' );
             }
         } else {
             return $template;
         }
    }
    
    public function parse_message()
    {
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
			
        }
        
        if ( $this->msg_text )
            add_action( 'admin_notices', array ( $this, 'render_msg' ) );
    }

    public function render_msg()
    {
        echo '<div id="message" class="' . $this->msg_class . '"><p>'
            . $this->msg_text . '</p></div>';
    }
    public function register_post_type() {
        
        session_start();
        
        $labels = array(
            'name'               => _x( 'WP Affiliate Shop Products', 'post type general name', 'your-plugin-textdomain' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'products' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => true,
            'taxonomies'         => array( 'wp_aff_categories' ),
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields','page-attributes'  )
        );

        register_post_type( 'wp_aff_products', $args );   
    }
    public function register_taxonomies() {
        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name'              => _x( 'Aff Categories', 'taxonomy general name' ),
            'singular_name'     => _x( 'Aff Category', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Categories' ),
            'all_items'         => __( 'Product Categories' ),
            'parent_item'       => __( 'Parent Category' ),
            'parent_item_colon' => __( 'Parent Category:' ),
            'edit_item'         => __( 'Edit Category' ),
            'update_item'       => __( 'Update Category' ),
            'add_new_item'      => __( 'Add New Category' ),
            'new_item_name'     => __( 'New Category Name' ),
            'menu_name'         => __( 'Category' ),
        );

        $args = array(
            'hierarchical'      => true,
            'public'            => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'category', 'with_front' => false ),
        );
        
        $labels2 = array(
            'name'              => _x( 'Colours', 'taxonomy general name' ),
            'all_items'         => __( 'Product Colours' ),
        );

        $args2 = array(
            'hierarchical'      => true,
            'public'            => false,
            'labels'            => $labels2,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'aff-colours', 'with_front' => false ),
        );
        
        $labels3 = array(
            'name'              => _x( 'Sizes', 'taxonomy general name' ),
            'all_items'         => __( 'Product Sizes' ),
        );

        $args3 = array(
            'hierarchical'      => true,
            'public'            => false,
            'labels'            => $labels3,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'aff-colours', 'with_front' => false ),
        );
        
        $labels4 = array(
            'name'              => _x( 'Brand', 'taxonomy general name' ),
            'all_items'         => __( 'Brands' ),
        );

        $args4 = array(
            'hierarchical'      => true,
            'public'            => false,
            'labels'            => $labels4,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'aff-brands', 'with_front' => false ),
        );

	   register_taxonomy( 'wp_aff_categories', array( 'wp_aff_products' ), $args );
       register_taxonomy( 'wp_aff_colours', array( 'wp_aff_products' ), $args2 );
       register_taxonomy( 'wp_aff_sizes', array( 'wp_aff_products' ), $args3 );
       register_taxonomy( 'wp_aff_brands', array( 'wp_aff_products' ), $args4 );
    }
	
	public function shop_args( $all = false ) {
		global $wp_query;
		$terms = get_terms('wp_aff_categories', array( 'orderby' => 'term_group', 'order'=>'DESC' ));
                //print_var($terms);
                //print_var($wp_query->query_vars);
                if( isset( $wp_query->query_vars['shop-cat'] ) ) {
                    $term = get_term_by( 'slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories' );
                    if( $term->term_group < 2 ) {
                        $catID = $term->term_id;
                        $parent = $catID;
                    } else {
                        $catID = $term->term_group;
                        $parent = $catID;
                    }
                } elseif( isset( $wp_query->query_vars['shop-brand'] ) ) {
                    $term = get_term_by( 'slug', $wp_query->query_vars['shop-brand'], 'wp_aff_brands' );
                    $catID = $term->term_id;
                    $parent = $catID;
                } else {
                    $catID = 0;
                    $parent = '0';
                }
		if( isset ( $_REQUEST['per_page'] ) && $_REQUEST['per_page'] != 'all' ) {
                        $per_page = $_REQUEST['per_page'];
                    } elseif( ( isset( $_REQUEST['per_page'] ) && $_REQUEST['per_page'] == 'all' ) || $all == true  ) {
                        $per_page= -1;
                    } else {
                        $per_page = 18;   
                    }
                    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
					$args = array( 'post_type' => 'wp_aff_products',
                            'posts_per_page' => $per_page,
                            'paged' => $paged
							);
                    if( isset( $wp_query->query_vars['shop-cat'] ) ) {
                        $args['tax_query'] = array(
                                array(
                                    'taxonomy' => 'wp_aff_categories',
                                    'field'    => 'id',
                                    'terms'    => $catID,
                                ),
                            );
                    } elseif( isset( $wp_query->query_vars['shop-brand'] ) ) {
                        $args['tax_query'] = array(
								'relation' => 'AND',
                                array(
                                    'taxonomy' => 'wp_aff_brands',
                                    'field'    => 'id',
                                    'terms'    => $catID,
                                ),
                        );
                    } 
					if( 
							isset( $_GET['colour'] ) || 
							isset( $_GET['size'] ) || 
							isset( $_GET['brand'] ) || 
							isset( $_GET['category'] ) || 
							isset( $_GET['price-min'] ) || 
							isset( $_GET['price-max'] ) 
						) {
						foreach( $_GET as $key=>$value ) {
							if( $value != -1 ) {
								if( $key != 'price-min' && $key != 'price-max' ) {
									switch ( $key ) {
										case 'colour' :
											$cols = explode(',', $_REQUEST['colour']);
											$args['tax_query'][] = array(
												'relation' => 'OR',
												array(
													'taxonomy' => 'wp_aff_colours',
													'field'    => 'id',
													'terms'    => $cols,
													)
											);
											break;
										case 'size' :
											$args['tax_query'][] = array(
												'taxonomy' => 'wp_aff_sizes',
												'field'    => 'id',
												'terms'    => $_REQUEST['size'],
											);
											break;
										case 'brand' :
											$args['tax_query'][] = array(
												'taxonomy' => 'wp_aff_brands',
												'field'    => 'slug',
												'terms'    => $_REQUEST['brand'],
											);
											break;
										case 'category' :
											$args['tax_query'][] = array(
												'taxonomy' => 'wp_aff_categories',
												'field'    => 'slug',
												'terms'    => $_REQUEST['category'],
											);
											break;
									} 
								} else {
									$args['meta_query'][] = array(
										'key' => 'wp_aff_product_price',
										'value'   => array( $_REQUEST['price-min'], $_REQUEST['price-max'] ),
										'type'    => 'numeric',
										'compare' => 'BETWEEN',
									);	
								}
							}
						}
					} else {
						if( !isset( $args ) ) {
							$args = array(
								'post_type' => 'wp_aff_products',
								'posts_per_page' => $per_page,
								'paged' => $paged
							);
						}
                    }
				return $args;
	}
	
	public function get_product_terms( $taxonomy ) {
		$arg = $this->shop_args( true );
		$query = new WP_Query( $arg );
		$fn_cats = array();
		foreach($query->posts AS $post) {
			$fn_temp_cats = wp_get_post_terms( $post->ID, $taxonomy );
			foreach( $fn_temp_cats as $fn_temp_cat ) {
				$fn_cats[] = $fn_temp_cat->term_id;	
				$parent  = get_term_by( 'id', $fn_temp_cat->term_id, $taxonomy);
				while ($parent->parent != '0'){
					$term_id = $parent->parent;
					$fn_cats[] = $term_id;
					$parent  = get_term_by( 'id', $term_id, $taxonomy);
				}
					
			}
		}
		$fn_cats = array_unique( $fn_cats );
		$fn_cats = implode( ',', $fn_cats );
		if( $fn_cats == '') {
			return 'none';
		} else {
			//print_var( $fn_cats );
			return $fn_cats;
		}
	}
	
	public function wp_aff_size_filter() {
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_size_filter' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
		if( is_array( $_REQUEST['wp_aff_sizes'] ) ) {
			$sizes = $_REQUEST['wp_aff_sizes'];	
		} else {
			$sizes[] = $_REQUEST['wp_aff_sizes'];
		}
		
		$url = preg_replace( '#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer'] );	
		
		if( empty( $sizes[0] ) ) {
			$url = remove_query_arg( 'size', $url );
		} else {
			$sizes = implode( $sizes, ',' );
			$url = add_query_arg( 'size', $sizes, $url );
		}
		
		$sizes = array_unique( $sizes );
		$sizes = implode( $sizes, ',' );
		
		$url = add_query_arg( 'size', $sizes, $url );
		wp_safe_redirect( $url );
	}
	
	public function wp_aff_colour_filter() {
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_colour_filter' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
			
		$url = preg_replace( '#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer'] );	
		
		if( is_array( $_REQUEST['wp_aff_colours'] ) ) {
			$colours = $_REQUEST['wp_aff_colours'];	
		} else {
			$colours[] = $_REQUEST['wp_aff_colours'];
		}
		$colours = array_unique( $colours );
		if( empty( $colours[0] ) ) {
			$url = remove_query_arg( 'colour', $url );
		} else {
			$colours = implode( $colours, ',' );
			$url = add_query_arg( 'colour', $colours, $url );
		}
		
		
		$url = add_query_arg( 'colour', $colours, $url );
		wp_safe_redirect( $url );
	}
	
	public function wp_aff_price_filter() {
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_price_filter' ) )
            die( 'Invalid nonce. ' . var_export( $_POST, true ) );
		$url = preg_replace( '#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer'] );
		$url = add_query_arg( 'price-min', $_REQUEST['price-min'], $url );
		$url = add_query_arg( 'price-max', $_REQUEST['price-max'], $url );
		wp_safe_redirect( $url );
	}
	
	public function wp_aff_brand_filter() {
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_brand_filter' ) )
            die( 'Invalid nonce. ' . var_export( $_POST, true ) );
		$url = preg_replace( '#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer'] );
		
		if( is_array( $_REQUEST['brands'] ) ) {
			$brands = $_REQUEST['brands'];	
		} else {
			$brands[] = $_REQUEST['brands'];
		}
		$brands = array_unique( $brands );
		if( empty( $brands[0] ) ) {
			$url = remove_query_arg( 'brand', $url );
		} else {
			$brands = implode( $brands, ',' );
			$url = add_query_arg( 'brand', $brands, $url );
		}
		wp_safe_redirect( $url );
	}
	
	public function remove_facted_element() {
		if( $_REQUEST['type'] == 'price' ) {
			$url = remove_query_arg( 'price-min', $_REQUEST['redirect'] );
			$url = remove_query_arg( 'price-max', $url );
		} else {
			$url = remove_query_arg( $_REQUEST['type'], $_REQUEST['redirect'] );
		}
		echo $url;
		die();	
	}
    public function faceted_cat_ajax() {
        $walker = new Faceted_Category_Walker;
        $args = array(
            'orderby'            => 'name',
            'order'              => 'ASC',
            'style'              => 'list',
            'hide_empty'         => 0,
            'use_desc_for_title' => 0,
            'child_of'           => $_POST['cat'],
            'exclude'            => '',
            'hierarchical'       => 1,
            'title_li'           => __( '' ),
            'show_option_none'   => __( '' ),
            'number'             => null,
            'echo'               => 1,
            'depth'              => 1,
            'taxonomy'           => 'wp_aff_categories',
            'walker'             => $walker
            );
        wp_list_categories( $args ); 
        die();
    }
    public function wp_aff_add_products() {
        if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_add_products' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
        
       
        
                
        $count = count( $_POST['product_image'] );
        
        $data = array();
        
        if( isset( $_POST['wp_aff_categories']['all'] )) {
            $data['categories']['all'] = $_POST['wp_aff_categories']['all'];
        } else {
            $data['categories']['all'] = array();
        }
        if( isset( $_POST['wp_aff_colours']['all'] )) {
            $data['colours']['all'] = $_POST['wp_aff_colours']['all'];
        } else {
            $data['colours']['all'] = array();
        }
        if( isset( $_POST['wp_aff_sizes']['all'] )) {
            $data['sizes']['all'] = $_POST['wp_aff_sizes']['all'];
        } else {
            $data['sizes']['all'] = array();
        }
        //print_var($data);
        unset( $_POST['wp_aff_categories']['all'] );
        unset( $_POST['wp_aff_colours']['all'] );
        unset( $_POST['wp_aff_sizes']['all'] );
         //print_var($_POST);
        for($i=0; $i<$count; $i++) {
            
            if( isset( $_POST['wp_aff_categories'][$i] ) ) {
                $data['categories'][$i] = array_merge($data['categories']['all'], $_POST['wp_aff_categories'][$i]);
                $data['categories'][$i] = array_unique( $data['categories'][$i] );
                $data['categories'][$i] = array_values( $data['categories'][$i] );
            } else {
                $data['categories'][$i] = $data['categories']['all'];
            }
            if( isset( $_POST['wp_aff_colours'][$i] ) ) {
                $data['colours'][$i] = array_merge($data['colours']['all'], $_POST['wp_aff_colours'][$i]); 
                $data['colours'][$i] = array_unique( $data['colours'][$i] );
                $data['colours'][$i] = array_values( $data['colours'][$i] );
            } else {
                $data['colours'][$i] = $data['colours']['all'];
            }
            if( isset( $_POST['wp_aff_sizes'][$i] ) ) {
                $data['sizes'][$i] = array_merge($data['sizes']['all'], $_POST['wp_aff_sizes'][$i]); 
                $data['sizes'][$i] = array_unique( $data['sizes'][$i] );
                $data['sizes'][$i] = array_values( $data['sizes'][$i] );
            } else {
                $data['sizes'][$i] = $data['sizes']['all'];
            }
            /*if(($key = array_search(0, $data['categories'][$i])) !== false) {
                unset($data['categories'][$i][$key]);
            }
            if(($key = array_search(0, $data['colours'][$i])) !== false) {
                unset($data['colours'][$i][$key]);
            }
            if(($key = array_search(0, $data['sizes'][$i])) !== false) {
                unset($data['sizes'][$i][$key]);
            }
            */
            
            if( $term_id = term_exists( $_POST['product_brand'][$i], 'wp_aff_brands' ) ) {
                $brand = $term_id['term_id'];
            } else {
                $term = wp_insert_term( $_POST['product_brand'][$i], 'wp_aff_brands' );
                $brand = $term['term_id'];
            }
            //echo $_POST['product_brand'][$i].' :: '.$brand;
            $my_post = array(
              'post_title'    => $_POST['product_name'][$i],
              'post_status'   => 'publish',
              'post_type'     => 'wp_aff_products',
              'tax_input' => array(
                'wp_aff_categories' => $data['categories'][$i],
                'wp_aff_sizes' => $data['sizes'][$i],
                'wp_aff_colours' => $data['colours'][$i],
                'wp_aff_brands' => $brand
               )
            );
            
            // Insert the post into the database
            
            
            $insID = wp_insert_post( $my_post );   
            add_post_meta($insID, 'wp_aff_product_id', $_POST['product_id'][$i], true);
            add_post_meta($insID, 'wp_aff_product_link', $_POST['product_link'][$i], true);
            add_post_meta($insID, 'wp_aff_product_price', $_POST['product_price'][$i], true);
            //add_post_meta($insID, 'wp_aff_product_brand', , true);
            add_post_meta($insID, 'wp_aff_product_desc', $_POST['product_desc'][$i], true);
            add_post_meta($insID, 'wp_aff_product_image', $_POST['product_image'][$i], true);
        }
        //print_var($data);
        unset( $_SESSION['products'] );
        session_regenerate_id();
        $url = add_query_arg( 'msg', 6, admin_url('admin.php?page=affiliate-shop' ) );

        wp_safe_redirect( $url );
    }
    
    public function wp_aff_update_settings() {
        if ( ! wp_verify_nonce( $_POST[ $this->option_name . '_nonce' ], 'wp_aff_save_api' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );

        if ( isset ( $_POST[ $this->option_name ] ) )
        {
            foreach( $_POST[ $this->option_name ] AS $key => $value ) {
              $array[$key] = $value;
            }
            update_option( $this->option_name, $array );
            $msg = 1;
        }
        else
        {
            delete_option( $this->option_name );
            $msg = 3;
        }

        if ( ! isset ( $_POST['_wp_http_referer'] ) )
            die( 'Missing target.' );

        $url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );

        wp_safe_redirect( $url );
        exit;
    }
    
    public function wp_aff_add_category() {
        if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_add_category' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );

            $term = array();
            $term['name']   = sanitize_text_field( $_POST['wp_term_name'] );
            $term['desc']   = htmlspecialchars( $_POST['wp_term_desc'] );
            $term['parent'] = intval( $_POST['wp_term_parent'] );
            $term['alias'] = intval( $_POST['wp_term_alias'] );
            $term['slug'] = sanitize_text_field( $_POST['wp_term_slug'] );
        
            if( $term['alias'] == -1 ) {
                $termarray = wp_insert_term( $term['name'], 'wp_aff_categories', $args = array( 'slug' => $term['slug'], 'description' => $term['desc'], 'parent' => $term['parent'] ) );
            } else {
                $alias = get_term( $term['alias'], 'wp_aff_categories');
                $term['alias'] = $alias->slug;
                $term['alias_id'] = $alias->term_id;
                //print_var($term);
                $termarray = wp_insert_term( $term['name'], 'wp_aff_categories', $args = array( 'slug' => $term['slug'], 'alias_of' => $term['alias'], 'description' => $term['desc'], 'parent' => $term['parent'] ) );
                //print_var( $termarray );
                global $wpdb;
                $wpdb->query( $wpdb->prepare(
                    "
                    UPDATE $wpdb->terms 
                    SET term_group = %s
                    WHERE term_id = %d 
                    ",
                    $term['alias_id'],
                    $termarray['term_id']
                ) );
            }
        
            if(is_wp_error($termarray)) {
                echo $termarray->get_error_message();
                $msg = 5; 
                $url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
            } else {
                $msg = 4;
                $url = add_query_arg( 'msg', $msg, admin_url('admin.php?page=affiliate-shop' ) );
            }
        if ( ! isset ( $_POST['_wp_http_referer'] ) )
            die( 'Missing target.' );


        wp_safe_redirect( $url );
        exit;
    }
    public function wp_aff_edit_colours() {
        if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_edit_colours' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );

            $term = array();
            $term['id']     = sanitize_text_field( $_POST['wp_term_id'] );
            $term['name']   = sanitize_text_field( $_POST['wp_term_name'] );
            $term['slug']   = sanitize_text_field( $_POST['wp_term_slug'] );
            $term['colour'] = sanitize_text_field( $_POST['wp_term_colour'] );
			$term['css'] 	= sanitize_text_field( $_POST['wp_term_colour_css'] );
            
            $termarray = wp_update_term( 
							$term['id'], 
							'wp_aff_colours', 
							$args = array( 
								'name' => $term['name'], 
								'slug' => $term['slug'], 
							) 
						);
			
            if(is_wp_error($termarray)) {
                $msg = 2; 
                $url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
            } else {
				update_metadata( 'wp_aff_colours', $term['id'], 'colour_code', $term['colour'] );
				update_metadata( 'wp_aff_colours', $term['id'], 'colour_code_css', $term['css'] );
                $msg = 8;
                $url = add_query_arg( 'msg', $msg, admin_url('admin.php?page=affiliate-shop/colours' ) );
            }
        if ( ! isset ( $_POST['_wp_http_referer'] ) )
            die( 'Missing target.' );

        //print_var($term);
        wp_safe_redirect( $url );
        exit;
    }
    public function wp_aff_edit_category() {
        if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_edit_category' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );

            $term = array();
            $term['id']     = sanitize_text_field( $_POST['wp_term_id'] );
            $term['name']   = sanitize_text_field( $_POST['wp_term_name'] );
            $term['slug']   = sanitize_text_field( $_POST['wp_term_slug'] );
            $term['desc']   = htmlspecialchars( $_POST['wp_term_desc'] );
            $term['alias'] = intval( $_POST['wp_term_alias'] );
            $term['parent'] = intval( $_POST['wp_term_parent'] );
            if( $term['alias'] == -1 ) {
                $termarray = wp_update_term( $term['id'], 'wp_aff_categories', $args = array( 'name' => $term['name'], 'slug' => $term['slug'], 'description' => $term['desc'], 'parent' => $term['parent'] ) );
				global $wpdb;
                $wpdb->query( $wpdb->prepare(
                    "
                    UPDATE $wpdb->terms 
                    SET term_group = %s
                    WHERE term_id = %d 
                    ",
                    0,
                    $termarray['term_id']
                ) );
            } else {
                $alias = get_term( $term['alias'], 'wp_aff_categories');
                $term['alias'] = $alias->slug;
                $term['alias_id'] = $alias->term_id;
                $termarray = wp_update_term( $term['id'], 'wp_aff_categories', $args = array( 'name' => $term['name'], 'slug' => $term['slug'], 'description' => $term['desc'], 'parent' => $term['parent'] ) );
                global $wpdb;
                $wpdb->query( $wpdb->prepare(
                    "
                    UPDATE $wpdb->terms 
                    SET term_group = %s
                    WHERE term_id = %d 
                    ",
                    $term['alias_id'],
                    $termarray['term_id']
                ) );
            }
            if(is_wp_error($termarray)) {
                $msg = 5; 
                $url = add_query_arg( 'msg', $msg, urldecode( $_POST['_wp_http_referer'] ) );
            } else {
                $msg = 7;
                $url = add_query_arg( 'msg', $msg, admin_url('admin.php?page=affiliate-shop' ) );
            }
        if ( ! isset ( $_POST['_wp_http_referer'] ) )
            die( 'Missing target.' );

        //print_var($term);
        wp_safe_redirect( $url );
        exit;
    }
    
    public function wp_aff_product_search() {
        if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_product_search' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) ); 
        
        $url = add_query_arg( 'q', $_POST['q'], $_POST['_wp_http_referer'] );
        $url = add_query_arg( 'wp_aff_merch', $_POST['wp_aff_merch'], $url );
        if ( ! isset ( $_POST['_wp_http_referer'] ) )
            die( 'Missing target.' );

        wp_safe_redirect( $url );
        exit;
    }
    
    public function add_products() { ?>
        <div class="wrap">
    <?php if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'manual' ) { wp_enqueue_media(); ?>
    	<h2>Affiliate Shop <a href="<?php print admin_url('admin.php?page=affiliate-shop/add-products'); ?>" class="add-new-h2">Add Product(s) from API</a></h2>
    	<h3>Add Product Manually</h3>
        <form method="POST" id="wp_add_prod_manual" action="<?php echo admin_url('admin-post.php'); ?>">
            <div id="titlediv">
                <div id="titlewrap">
                    <input type="text" placeholder="Product name" name="product_name" size="30" value="" id="title" spellcheck="true" autocomplete="off">
                </div>
            </div>
        	<table class="form-table">
            	<tr>
                	<th>Brand</th>
                    <td>
                    	<?php 
							$arg = array(
									'show_option_none'   => 'Select Brand',
									'orderby'            => 'NAME', 
									'order'              => 'ASC',
									'hide_empty'         => 0, 
									'name'               => 'brand',
									'taxonomy'           => 'wp_aff_brands',
								);
							wp_dropdown_categories( $arg ); ?> <br>
                           	<input class="regular-text" type="text" name="product_brand_new" placeholder="Or type a new one">
                            <p class="description">If adding a new category, none should be selected from the dropdown.</p>
                    </td>
                </tr>
                <tr>
                	<th>Price</th>
                    <td><input class="regular-text" type="number" min="0" name="product_price" placeholder="0.00" value=""><p class="description">&pound; sign not needed.</p></td>
                </tr>
                <tr>
                	<th>Description</th>
                    <td>
                    	<textarea class="large-text" cols="46" rows="4" name="product_desc"></textarea>
                        <p class="description">Not currently used on site but may be in future.</p>
                    </td>
                </tr>
                <tr>
                	<th>Product Link</th>
                    <td>
                    	<input class="large-text" type="url" name="product_url" placeholder="http://" value="">
                        <p class="description">Affiliate link pasted here.</p>
                    </td>
                </tr>
                <tr>
                	<th>Image</th>
                    <td>
                    	<input id="upload_image_button" type="button" class="button button-secondary" value="Upload Image" /><input type="hidden" id="product_image" name="product_image">
                        <p class="description">The image should be at least 300px x 300px.</p>
                    </td>
                    
                </tr>
                </table>
                <table class="form-table">
                <tr class="form-table">
                	<td width="33%" valign="top">
                       <div style="">
                           <?php $categories = new Tag_Checklist('wp_aff_categories', 'all' ); ?>
                        
                        </div> 
                    </td> 
                    
                   <td width="33%" valign="top">
                        <div style="">
                            <?php $colours = new Tag_Checklist('wp_aff_colours', 'all' ); ?>
                        
                        </div>
                   </td>
                   <td width="33%" valign="top">
						<?php $sizes = new Tag_Checklist('wp_aff_sizes', 'all' ); ?>
                   </td>
                </tr>
            </table>
            <input type="hidden" value="wp_aff_add_man_product" name="action" />
			<?php wp_nonce_field( 'wp_aff_add_man_product', '_wpnonce', FALSE ); ?>
            <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
            <?php submit_button(); ?>
        </form>
    <?php } else { ?>
    <?php 
        if(!isset($_REQUEST['step'])) {
    ?>
    <h2>Affiliate Shop <a href="<?php print admin_url('admin.php?page=affiliate-shop/add-products&action=manual'); ?>" class="add-new-h2">Add Product Manually</a></h2>
    <h3>Add Products from API</h3>
                <form method="POST" id="wp_aff_add_cat" class="searchtable" action="<?php echo admin_url('admin-post.php'); ?>">
                        <table class="form-table" >
                            <tr>
                                <th>Search Query</th>
                                <td>
                                    <input class="regular-text" type="text" name="q" value="<?php echo (isset( $_GET['q'] ) ? $_GET['q'] : '' ); ?>" id="wp_aff_search">
                                    <p class="description">Search for products, such as <code>Black Dress</code>.</p>
                                </td>
                            
                                <th>Affiliate</th>
                                <td>
                                    <select name="wp_aff_aff">
                                        <option selected>Affiliate Window</option>
                                    </select>
                                </td>
                            
                                <th>Merchant</th>
                                <td>
                                    <?php
                                        $aParams9 = array('iMaxResult' => 1000);
                                        $merc_result = $this->oClient->call('getMerchantList', $aParams9);
            $i =1;
                                    ?>
                                    <select name="wp_aff_merch">
                                        <option selected value="0">All Merchants</option>
                                        <?php
                                        foreach($merc_result->oMerchant AS $merchant) {
                                            echo '<option value="'.$merchant->iId.'">'.$i.': '.$merchant->sName.'</option>'; 
                                            $i++;
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" value="wp_aff_product_search" name="action" />
                        <?php wp_nonce_field( 'wp_aff_product_search', '_wpnonce', FALSE ); ?>
                        <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                        <?php submit_button( 'Search' ); ?>
                    </form>
            <hr>
            
                <div id="wp_aff_prod_list">
                    <?php
                        if(isset($_GET['q']) && $_GET['q'] != '' || isset($_GET['paged'])) {
                            
                            if( $_GET['wp_aff_merch'] == 0 ) {
                                $aParams7 = array("sQuery"	=> $_GET['q'], "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription'));
                            } else {
                                $oRefineBy = new stdClass();
                                $oRefineBy->iId = 3;
                                $oRefineBy->sName = '';

                                $oRefineByDefinition = new stdClass();
                                $oRefineByDefinition->sId = $_GET['wp_aff_merch'];
                                $oRefineByDefinition->sName = '';

                                $oRefineBy->oRefineByDefinition = $oRefineByDefinition;
                                
                                $aParams7 = array("sQuery"	=> $_GET['q'], "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription'), "oActiveRefineByGroup"	=>	$oRefineBy);
                            }

                            
                            $ListProductSearch = new ListProductSearch($aParams7);
                                        $ListProductSearch->prepare_items();
                        }
                            ?>
                            
                            <div id="productSelects">
                                <h4>Selected Products</h4>
                                <?php
                                        
                                        //echo $ListProductSearch->current_action();
                                       // print_r($_GET['product']);
                                    
                                        if( isset( $ListProductSearch) && 'add'===$ListProductSearch->current_action() ) {
        
                                            if( is_array( $_GET['product'] ) ) {
                                                foreach( $_GET['product'] AS $prod ) {
                                                    if( @!in_array( $prod, $_SESSION['products'] ) || !isset( $_SESSION['products'] ) ) {
                                                        $_SESSION['products'][$prod] = $prod; 
                                                    }
                                                }
                                            } else {
                                                if( !@in_array( $_GET['product'], $_SESSION['products'] ) ) {
                                                     $_SESSION['products'][$_GET['product']] = $_GET['product']; 
                                                 }
                                            }    
                                        } elseif( isset( $ListProductSearch) && 'clear-products'===$ListProductSearch->current_action() ) {
                                            $_SESSION['products'] = '';
                                        } elseif( isset( $ListProductSearch) && 'remove-product'===$ListProductSearch->current_action() ) {
                                            if(isset($_GET['product'])) {
                                                unset($_SESSION['products'][$_GET['product'] ]);
                                            }
                                        }
                                        
                                if( isset( $_SESSION['products'] ) && is_array($_SESSION['products']) && !empty( $_SESSION['products'] )) {
                                    //print_r($_SESSION['products']);
                            
                                        $products = array_unique( $_SESSION['products'] );
                                        foreach( $products AS $key=>$value ) {
                                            echo '<a href="';
                                            echo admin_url('admin.php?page='.$_REQUEST['page'].'&q='.$_REQUEST['q'].'&wp_aff_merch='.$_REQUEST['wp_aff_merch'].'&action=remove-product&product='.$key); 
                                            echo '" class="productButton button" rel="'.$key.'">'.$value.' <span class="fa fa-times-circle"></span></a>';   
                                        }
                                ?>
                                <div><a href="<?php echo admin_url('admin.php?page='.$_REQUEST['page'].(isset($_REQUEST['wp_aff_categories']) ? '&wp_aff_categories='.$_REQUEST['wp_aff_categories'] : '').'&step=2'); ; ?>" class="button button-primary">Next Step</a> <a class="button" href="<?php echo @admin_url('admin.php?page='.$_REQUEST['page'].'&q='.$_REQUEST['q'].'&wp_aff_merch='.$_REQUEST['wp_aff_merch'].'&action=clear-products'); ?>">Clear all</a></div>
                                
                                
                            </div>
                            
                    <?php }  else {?>
                        <p>No products currently selected.</p>
                    <?php } ?>
                    <hr>
                    <?php if(isset($_GET['q']) && $_GET['q'] != '' || isset($_GET['paged'])) { ?>
                            <form id="product-table" method="get">
                                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                                <input type="hidden" name="page" value="<?php echo urldecode($_REQUEST['page']); ?>" />
                                <input type="hidden" name="q" value="<?php echo $_REQUEST['q'] ?>" />
                                <input type="hidden" name="wp_aff_merch" value="<?php echo @$_REQUEST['wp_aff_merch'] ?>" />
                                <?php
                                    if( isset( $_REQUEST['wp_aff_categories'] ) ) {
                                       echo '<input type="hidden" name="wp_aff_categories" value="'.$_REQUEST['wp_aff_categories'].'" />';
                                    }
                                ?>
                                <!-- Now we can render the completed list table -->
                                <?php $ListProductSearch->display(); ?>
                            </form>
                            
                    <?php }  ?>
                </div>
        
    <?php } elseif( isset( $_REQUEST['step'] ) && $_REQUEST['step'] == 2) { 
            if( isset( $_REQUEST['action'] ) && 'remove-product'=== $_REQUEST['action'] ) {
                if(isset($_GET['product'])) {
                    unset($_SESSION['products'][$_GET['product'] ]);
                }
            }
    ?>
            <?php
                                $products = array_unique( $_SESSION['products'] );
                                
                            ?> 
           <form method="POST" id="wp_aff_add_products" class="searchtable" action="<?php echo admin_url('admin-post.php'); ?>">
               <input type="hidden" value="wp_aff_add_products" name="action" />
                <?php wp_nonce_field( 'wp_aff_add_products', '_wpnonce', FALSE ); ?>
                <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
                <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
              <div id="poststuff">
                  <div id="post-body" class="metabox-holder columns-1">
                      
               <div id="postbox-container-2" class="postbox-container">
<div class="postbox">
<h3 class="hndle "><span>Apply to all products</span></h3>
<div class="inside">
               <table class="form-table" >
                   
                   
                   <tr>
                        <th>Product Tagging</th>
                        <td>
                            <div style="width: 33%; float: left; ">
                            <?php 
                                if( isset( $_REQUEST['wp_aff_categories'] ) ) {
                                    $categories = new Tag_Checklist('wp_aff_categories', 'all', $_REQUEST['wp_aff_categories'] ); 
                                } else {
                                   $categories = new Tag_Checklist('wp_aff_categories', 'all' );  
                                }
                            ?>
                            </div>
                           <div style="width: 33%; float: left;">
                            <?php $categories = new Tag_Checklist('wp_aff_colours', 'all' ); ?>
                            </div>
                            <div style="width: 33%; float: left; ">
                            <?php $categories = new Tag_Checklist('wp_aff_sizes', 'all' ); ?>
                            </div>
                        </td> 
                    </tr>
                   
                    <tr>
                        <th>Confirm</th>
                        <td><input type="checkbox" class="add_product_confirm"> <i>Tick to confirm you have updated all details for the products below.</i></td>
                   </tr>
                   <tr>
                   <th></th>
                   <td><?php submit_button( 'Add All Products', 'primary', '', false, array('disabled' => 'disabled', 'id' => 'add_products_submit') ); ?></td>
                   </tr>
               </table>
                <p></p>
                
                   </div>
                  
                  </div>
                   <?php 
                        foreach( $products AS $key=>$value ) {
                                $proda[] = $key;
                            }
                        $aParams5 = array('iProductId'	=> $proda, 'iAdult' => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'iCategoryId', 'bHotPick', 'sSpecification', 'sPromotion', 'sModel') ); 
                        $productArray= $this->oClient->call('getProduct', $aParams5);
                        //echo '<pre>'.print_r($productArray, true).'</pre>';
                    ?>
                   <?php 
                        //print_var($aParams5);
                        if( !is_array( $productArray->oProduct ) ) {
                            $test = $productArray->oProduct;
                            $productArray->oProduct = array( $productArray->oProduct );
                        }
                            $i = 0;
							
                            foreach( $productArray->oProduct AS $product ) { 
                   ?>
                   <?php // echo '<pre>'.print_r($producta, true).'</pre>'; ?>
                   <div class="postbox">
                       <?php
                            $aParams8 = array('iMerchantId'	=> $product->iMerchantId);
                            $merch = $this->oClient->call('getMerchant', $aParams8);
                        ?>
<h3 class=" "><span><?php echo ucwords(($product->sName)); ?> (ID:<?php echo $product->iId; ?>)<br>Brand: <?php echo ucwords($merch->oMerchant->sName); ?><br><a class="button" target="_blank" href="<?php echo $product->sAwDeepLink; ?>">Visit URL</a></span> <?php submit_button( 'Remove Product', 'delete', '', false, array('disabled' => 'disabled') ); ?></h3>
<div class="inside">
                <input type="hidden" value="<?php echo $product->sAwDeepLink; ?>" name="product_link[<?php echo $i; ?>]">
                <input type="hidden" value="<?php echo $product->iId; ?>" name="product_id[<?php echo $i; ?>]">
                
                <input type="hidden" value="<?php echo ucwords($merch->oMerchant->sName); ?>" name="product_brand[<?php echo $i; ?>]">
               <table class="widefat productList" >
                   <tbody>
                   <tr>
                    <th width="200"></th><th colspan="2">Product Name</th><th>Price</th><th></th>
                   </tr>
                   <tr>
                        <td rowspan="6"><img style="width: 175px; height: auto;" src="<?php echo $product->sAwImageUrl; ?>"><input type="hidden" value="<?php echo $product->sAwImageUrl; ?>" name="product_image[<?php echo $i; ?>]" </td>
                   
                        <td colspan="2">
                            <input class="large-text" type="text" name="product_name[<?php echo $i; ?>]" placeholder="" value="<?php echo $product->sName; ?>" id="">
                        </td> 
                       <td>
                            <input class="large-text" type="text" name="product_price[<?php echo $i; ?>]" placeholder="" value="<?php echo number_format($product->fPrice, 2); ?>" id="">
                        </td> 
                        
                       
                    </tr>
                       <?php if(isset($product->sSpecification)) { ?>
                   <tr>
                        <td colspan="3">Merchant specification: <?php echo $product->sSpecification; ?></td>
                   </tr>
                <?php } ?>
                   <tr>
                        <td width="33%">
                           <div style="">
                               <?php $categories = new Tag_Checklist('wp_aff_categories', $i ); ?>
                            
                            </div> 
                        </td> 
                        
                       <td width="33%">
                            <div style="">
                                <?php $colours = new Tag_Checklist('wp_aff_colours', $i ); ?>
                            
                            </div>
                       </td>
                       <td width="33%">
                            <div style="">
                                <?php $sizes = new Tag_Checklist('wp_aff_sizes', $i ); ?>
                           
                            </div>
                       </td>
                    </tr>
                   <tr>
                    <th colspan="2">Description</th>
                   </tr>
                   <tr>
                        <td colspan="3">
                            
                            <textarea class="large-text" rows="3" type="text" name="product_desc[<?php echo $i; ?>]" placeholder=""><?php echo $product->sDescription; ?></textarea>
                       </td>
                       
                    </tr>
                
                   
                       </tbody>
                   
               </table>
                
                   </div>
                  
                  </div>
                   <?php   $i++; } ?>
                         
                  </div>
                  </div>
            </form>
    <?php } 
	} 
	?>
            </div>
    <?php } 
    
    public function settings_page() { 
            $redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
            $redirect = urlencode( $_SERVER['REQUEST_URI'] );
        ?>
        <div class="wrap">
            <h2>Settings</h2>
            <form method="POST" id="wp_aff_prod_search" action="<?php echo admin_url('admin-post.php'); ?>">
                <table class="form-table" >
                    <tr>
                        <th>Affiliate Window API Key</th>
                        <td>
                            <input class="regular-text" type="text" name="<?php echo $this->option_name; ?>[awin]" value="<?php echo $this->option['awin']; ?>" id="<?php echo $this->option_name; ?>[awin]">
                            <input type="hidden" value="wp_aff_save_api" name="action" />
                            <?php wp_nonce_field( 'wp_aff_save_api', $this->option_name . '_nonce', FALSE ); ?>
                            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                            <p class="description">Please enter your Affiliate Window product search API key.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Shop Page</th>
                        <td>
                            <?php 
                                 wp_dropdown_pages(
                                    array(
                                         'name' => $this->option_name.'[shop_page]',
                                         'echo' => 1,
                                         'show_option_none' => __( '&mdash; Select &mdash;' ),
                                         'option_none_value' => '0',
                                         'selected' => ( isset( $this->option['shop_page'] ) ? $this->option['shop_page'] : '' ) 
                                    )
                                );
                            ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button( 'Save' ); ?>
            </form>
        </div>
    <?php } 
    public function addons_page() { ?>
        <div class="wrap">
            <h2>Add Ons <a href="<?php print admin_url('admin.php?page=affiliate-shop&action=add-category'); ?>" class="add-new-h2">Search Add Ons</a></h2>
            <?php   
                    $listAddOns = new AddOnsTable();
                    $listAddOns->prepare_items();
            ?>
                            <form id="product-table" method="get">
                                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                                <input type="hidden" name="q" value="<?php echo $_REQUEST['q'] ?>" />
                                <input type="hidden" name="wp_aff_merch" value="<?php echo $_REQUEST['wp_aff_merch'] ?>" />
                                <?php
                                    if( isset( $_REQUEST['category'] ) ) {
                                       echo '<input type="hidden" name="category" value="'.$_REQUEST['category'].'" />';
                                    }
                                ?>
                                <!-- Now we can render the completed list table -->
                                <?php $listAddOns->display(); ?>
                            </form>
        </div>
    <?php }
    public function list_brands() {
        $CategoryTable = new WP_Terms_List_Tables( array( 'taxonomy' =>  'wp_aff_brands' ) );
        $CategoryTable->prepare_items();
    ?>
    <div class="wrap">
        <h2>Affiliate Shop</h2>
        <h3>Shop Brands <a href="<?php print admin_url('admin.php?page=affiliate-shop&action=add-category'); ?>" class="add-new-h2">Add Brand</a></h3>
        <form id="category-table" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $CategoryTable->display() ?>
        </form>
    </div>
    <?php    
    }
	
	public function colours_page() {
        $CategoryTable = new WP_Terms_List_Tables( array( 'taxonomy' =>  'wp_aff_colours' ) );
        $CategoryTable->prepare_items();
    ?>
    <div class="wrap">
        <h2>Affiliate Shop</h2>
        <h3>Shop Colours <a href="<?php print admin_url('admin.php?page=affiliate-shop&action=add-colours'); ?>" class="add-new-h2">Add Colour</a></h3>
        <form id="category-table" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $CategoryTable->display() ?>
        </form>
    </div>
    <?php    
    }
	
	public function sizes_page() {
        $CategoryTable = new WP_Terms_List_Tables( array( 'taxonomy' =>  'wp_aff_sizes' ) );
        $CategoryTable->prepare_items();
    ?>
    <div class="wrap">
        <h2>Affiliate Shop</h2>
        <h3>Shop Sizes <a href="<?php print admin_url('admin.php?page=affiliate-shop&action=add-size'); ?>" class="add-new-h2">Add Size</a></h3>
        <form id="category-table" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $CategoryTable->display() ?>
        </form>
    </div>
    <?php    
    }
    /**
     * Place code for admin page here
     */
    public function admin_page() { ?>
    <div class="wrap">
        <h2>Affiliate Shop</h2>
        <?php if(!isset($_GET['action']) || $_GET['action'] == 'delete'): ?>
        
        <?php
            $CategoryTable = new WP_Terms_List_Tables(array('taxonomy' => 'wp_aff_categories'));
            $CategoryTable->prepare_items();
        ?>
            <h3>Shop Categories <a href="<?php print admin_url('admin.php?page=affiliate-shop&action=add-category'); ?>" class="add-new-h2">Add Category</a></h3>
            <form id="category-table" method="get">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <!-- Now we can render the completed list table -->
                <?php $CategoryTable->display() ?>
            </form>
        <?php else: // Else if(!isset($_GET['action'])) ?>
            <?php if($_GET['action'] == 'view') : ?>
                <?php
                    $ProductTable = new ProductTable();
                    $ProductTable->prepare_items();
                    $term = get_term( $_GET['wp_aff_categories'], 'wp_aff_categories' );
                ?>
                <h3><?php echo ucwords($term->name); ?> Products <a href="<?php print admin_url('admin.php?page=affiliate-shop/add-products&wp_aff_categories='.$_REQUEST['wp_aff_categories']); ?>" class="add-new-h2">Add Product(s)</a></h3>
                <form id="product-table" method="get">
                    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <!-- Now we can render the completed list table -->
                    <?php $ProductTable->display(); ?>
                </form>
            <?php elseif($_GET['action'] == 'add-category') : 
                $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] );
            ?>
                <h3>Add Shop Category</h3>
                <form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                    <table class="form-table" >
                        <tr class="form-field">
                            <th>Category Name</th>
                            <td>
                                <input class="regular-text" type="text" name="wp_term_name" placeholder="Category Name">
                                <p class="description">The name is how it appears on your site.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Category Slug</th>
                            <td>
                                <input class="regular-text" type="text" name="wp_term_slug" placeholder="Category Slug">
                                <p class="description">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Parent Category</th>
                            <td>
                                <?php
                                    wp_dropdown_categories( array( 'show_count' => 0,
                                                                   'hierarchical' => 1,
                                                                   'taxonomy' => 'wp_aff_categories',
                                                                   'depth' => 999,
                                                                   'show_option_none' => 'No parent',
                                                                   'name' => 'wp_term_parent',
                                                                   'hide_empty' => 0,
                                                                  'orderby' => 'NAME'
                                                                 ));
                                ?>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Alias Category</th>
                            <td>
                                <?php
                                    wp_dropdown_categories( array( 'show_count' => 0,
                                                                   'hierarchical' => 1,
                                                                   'taxonomy' => 'wp_aff_categories',
                                                                   'depth' => 999,
                                                                   'show_option_none' => 'No alias',
                                                                   'name' => 'wp_term_alias',
                                                                   'hide_empty' => 0,
                                                                  'orderby' => 'NAME'
                                                                 ));
                                ?>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Description</th>
                            <td>
                                <?php 
                                $settings = array (
                                    'textarea_name' => 'wp_term_desc',
                                );
                                wp_editor( '', 'wp_term_desc', $settings ); 
                            ?>
                                
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" value="wp_aff_add_category" name="action" />
                    <?php wp_nonce_field( 'wp_aff_add_category', '_wpnonce', FALSE ); ?>
                    <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button( 'Add Category' ); ?>
                </form>
            <?php elseif( $_GET['action'] == 'edit' && isset( $_GET['wp_aff_colours'] ) ) : ?>
            <?php 
				$term = get_term( $_GET['wp_aff_colours'], 'wp_aff_colours', 'OBJECT' ); 
				$colour_code = get_metadata('wp_aff_colours', $term->term_id, 'colour_code', true);
				$colour_code_css = get_metadata('wp_aff_colours', $term->term_id, 'colour_code_css', true);
			?>	
                <h3>Edit Shop Colour</h3>
            	<form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                	<table class="form-table" >
                        <tr class="form-field">
                            <th>Colour Name</th>
                            <td>
                                <input type="hidden" value="<?php echo $term->term_id; ?>" name="wp_term_id">
                                <input class="regular-text" type="text" name="wp_term_name" placeholder="Category Name" value="<?php echo $term->name; ?>">
                                <p class="description">The name is how it appears on your site.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Colour Slug</th>
                            <td>
                                <input class="regular-text" type="text" name="wp_term_slug" placeholder="Category Slug" value="<?php echo $term->slug; ?>">
                                <p class="description">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Colour</th>
                            <td>
                                <input class="regular-text" type="color" name="wp_term_colour" value="<?php echo $colour_code; ?>" style="width: 50px; height: 50px;">
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Or Use Custom CSS?</th>
                            <td>
                                <textarea rows="10" class="regular-text" name="wp_term_colour_css"><?php echo $colour_code_css; ?></textarea>
                                <p class="description">Use <a target="_blank" href="http://www.colorzilla.com/gradient-editor/">Gradient Generator</a> and copy and paste the code.</p>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" value="wp_aff_edit_colours" name="action" />
                    <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
                    <?php wp_nonce_field( 'wp_aff_edit_colours', '_wpnonce', FALSE ); ?>
                    <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button( 'Edit Colour' ); ?>
                </form>
            <?php elseif( $_GET['action'] == 'edit' && isset( $_GET['wp_aff_categories'] ) ) : ?>
                <?php 
					$term = get_term( $_GET['wp_aff_categories'], 'wp_aff_categories', 'OBJECT' ); 
					
				?>
                
                <h3>Edit Shop Category</h3>
                <form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                    <table class="form-table" >
                        <tr class="form-field">
                            <th>Category Name</th>
                            <td>
                                <input type="hidden" value="<?php echo $term->term_id; ?>" name="wp_term_id">
                                <input class="regular-text" type="text" name="wp_term_name" placeholder="Category Name" value="<?php echo $term->name; ?>">
                                <p class="description">The name is how it appears on your site.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Category Slug</th>
                            <td>
                                <input class="regular-text" type="text" name="wp_term_slug" placeholder="Category Slug" value="<?php echo $term->slug; ?>">
                                <p class="description">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Parent Category</th>
                            <td>
                                <?php
                                    wp_dropdown_categories( array( 'show_count' => 0,
                                                                   'hierarchical' => 1,
                                                                   'taxonomy' => 'wp_aff_categories',
                                                                   'depth' => 5,
                                                                   'show_option_none' => 'No parent',
                                                                   'name' => 'wp_term_parent',
                                                                   'selected' => $term->parent,
                                                                  'hide_empty' => 0,
                                                                  'orderby' => 'NAME'
                                                                 ));
                                ?>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Alias Category</th>
                            <td>
                                <?php
                                    if( $term->term_group > 1 ) {
                                        $selected_alias = $term->term_group;
                                    } else {
                                        $selected_alias = '';
                                    }
                                    wp_dropdown_categories( array( 'show_count' => 0,
                                                                   'hierarchical' => 1,
                                                                   'taxonomy' => 'wp_aff_categories',
                                                                   'depth' => 999,
                                                                   'show_option_none' => 'No alias',
                                                                   'name' => 'wp_term_alias',
                                                                   'selected' => $term->term_group,
                                                                   'hide_empty' => 0,
                                                                  'orderby' => 'NAME'
                                                                 ));
                                ?>
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th>Description</th>
                            <td>
                                <?php 
                                $settings = array (
                                    'textarea_name' => 'wp_term_desc',
                                );
                                wp_editor( htmlspecialchars_decode( $term->description ), 'wp_term_desc', $settings ); 
                            ?>
                                
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" value="wp_aff_edit_category" name="action" />
                    <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
                    <?php wp_nonce_field( 'wp_aff_edit_category', '_wpnonce', FALSE ); ?>
                    <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button( 'Edit Category' ); ?>
                </form>
            <?php endif; // End if($_GET['action'] == 'view') ?>
        <?php endif; //End if(!isset($_GET['action'])) ?>
    </div>
    <?php }
	
	public function wp_aff_add_man_product() {
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_aff_add_man_product' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
		if( isset( $_POST['product_brand_new'] ) && $_POST['product_brand_new'] != '' ) {
			$brand = wp_insert_term( $_POST['product_brand_new'], 'wp_aff_brands' );
		} else {
			$brand == $_POST['product_brand'];	
		}
		print_var( $_POST );
		$my_post = array(
              'post_title'    => $_POST['product_name'],
              'post_status'   => 'publish',
              'post_type'     => 'wp_aff_products',
              'tax_input' => array(
                'wp_aff_categories' => $_POST['wp_aff_categories']['all'],
                'wp_aff_sizes' => $_POST['wp_aff_sizes']['all'],
                'wp_aff_colours' => $_POST['wp_aff_colours']['all'],
                'wp_aff_brands' => $brand
               )
            );
            
            // Insert the post into the database
            
            
		$insID = wp_insert_post( $my_post );   
		add_post_meta($insID, 'wp_aff_product_id', $_POST['product_id'], true);
		add_post_meta($insID, 'wp_aff_product_link', $_POST['product_url'], true);
		add_post_meta($insID, 'wp_aff_product_price', $_POST['product_price'], true);
		//add_post_meta($insID, 'wp_aff_product_brand', , true);
		add_post_meta($insID, 'wp_aff_product_desc', $_POST['product_desc'], true);
		add_post_meta($insID, 'wp_aff_product_image', $_POST['product_image'], true);
		add_post_meta($insID, 'wp_aff_product_manual', 1, true);
		$url = add_query_arg( 'msg', 1, $_POST['_wp_http_referer'] );
		wp_safe_redirect( $url );
	}
	
	
    /**
     * Place code for your plugin's functionality here.
     */
    private function run_plugin() {
	}
}
$wp_aff = new WordPress_Affiliate_Shop;
