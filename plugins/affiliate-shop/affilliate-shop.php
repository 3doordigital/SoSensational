<?php
/*
  Plugin Name: Affiliate Shop
  Plugin URI:
  Description: Create a shop on your WordPress site using the most popular Affiliate networks.
  Version: 0.1b
  Author: 3 Door Digital
  Author URI: http://www.3doordigital.com
  License: GPL V3
 */
require_once('inc/base_functions.php');
require_once('classes/walkers.php');
require_once('inc/admin_list_tables.php');
require_once('classes/tag-checklist.php');
require_once('inc/widgets.php');
require_once('classes/class.api.php');

class WordPress_Affiliate_Shop {

    private static $instance = null;
    private $plugin_path;
    private $plugin_url;
    private $text_domain = 'wpaffshop';
    private $admin_icon = 'dashicons-cart';
    public $option_name = 'wp_aff_apis';
    // Feed manager options
    private $man_admin_icon = 'dashicons-admin-generic';
    private $man_option_name = 'wp_aff_man';
    private $man_page_title = 'Affiliate Feed Manager';
    private $db_version = '1.1';

    /**
     * Creates or returns an instance of this class.
     */
    public static function get_instance() {
        // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
     */
    public function __construct() {

        global $wpdb;
        session_start();
        $this->create_metadata_table();


        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        $this->option = get_option($this->option_name);
        $this->man_option = get_option($this->man_option_name);

        if ($this->option == FALSE) {
            $array = array(
                'new_days' => 14
            );
            update_option($this->option_name, $array);
        }

        if (!isset($this->option['new_days'])) {
            $array = $this->get_option();
            $array['new_days'] = 14;
            update_option($this->option_name, $array);
        }

        load_plugin_textdomain($this->text_domain, false, 'lang');

        add_action('admin_enqueue_scripts', array($this, 'admin_register_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_register_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'register_styles'));

        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('init', array($this, 'custom_rewrite_rule'));

        add_action('template_redirect', array($this, 'sitemaps'));

        add_action('admin_menu', array($this, 'create_menu'));

        add_action('admin_post_wp_aff_save_api', array($this, 'wp_aff_update_settings'));
        add_action('admin_post_wp_aff_add_category', array($this, 'wp_aff_add_category'));
        add_action('admin_post_wp_aff_edit_category', array($this, 'wp_aff_edit_category'));
        add_action('admin_post_wp_aff_product_search', array($this, 'wp_aff_product_search'));
        add_action('admin_post_wp_aff_add_products', array($this, 'wp_aff_add_products'));

        add_action('admin_post_wp_aff_add_colours', array($this, 'wp_aff_add_colours'));
        add_action('admin_post_wp_aff_edit_colours', array($this, 'wp_aff_edit_colours'));

        add_action('admin_post_wp_aff_add_sizes', array($this, 'wp_aff_add_sizes'));
        add_action('admin_post_wp_aff_edit_sizes', array($this, 'wp_aff_edit_sizes'));

        add_action('admin_post_wp_aff_add_man_product', array($this, 'wp_aff_add_man_product'));
        add_action('admin_post_wp_aff_edit_man_product', array($this, 'wp_aff_edit_man_product'));

        add_action('admin_post_nopriv_wp_aff_size_filter', array($this, 'wp_aff_size_filter'));
        add_action('admin_post_wp_aff_size_filter', array($this, 'wp_aff_size_filter'));

        add_action('admin_post_nopriv_wp_aff_colour_filter', array($this, 'wp_aff_colour_filter'));
        add_action('admin_post_wp_aff_colour_filter', array($this, 'wp_aff_colour_filter'));

        add_action('admin_post_nopriv_wp_aff_price_filter', array($this, 'wp_aff_price_filter'));
        add_action('admin_post_wp_aff_price_filter', array($this, 'wp_aff_price_filter'));

        add_action('admin_post_nopriv_wp_aff_brand_filter', array($this, 'wp_aff_brand_filter'));
        add_action('admin_post_wp_aff_brand_filter', array($this, 'wp_aff_brand_filter'));

        add_action('admin_post_nopriv_wp_aff_sale_filter', array($this, 'wp_aff_sale_filter'));
        add_action('admin_post_wp_aff_sale_filter', array($this, 'wp_aff_sale_filter'));

        add_action('wp_ajax_ajax_update_sticker', array($this, 'ajax_update_sticker'));
        add_action('wp_ajax_ajax_new_in_single_product', array($this, 'ajax_new_in_single_product'));

        add_action('wp_ajax_ajax_new_in', array($this, 'ajax_new_in'));
        add_action('wp_ajax_nopriv_ajax_new_in', array($this, 'ajax_new_in'));

        add_action('wp_ajax_admin_product_filter', array($this, 'admin_product_filter'));

        add_action('wp_ajax_nopriv_change_faceted_category', array($this, 'faceted_cat_ajax'));
        add_action('wp_ajax_change_faceted_category', array($this, 'faceted_cat_ajax'));

        add_action('wp_ajax_nopriv_remove_facted_element', array($this, 'remove_facted_element'));
        add_action('wp_ajax_remove_facted_element', array($this, 'remove_facted_element'));

        add_action('wp_ajax_nopriv_sort_shop', array($this, 'sort_shop'));
        add_action('wp_ajax_sort_shop', array($this, 'sort_shop'));

        add_action('wp_ajax_ajax_update_get_count', array($this, 'ajax_update_get_count'));
        add_action('wp_ajax_ajax_update_product', array($this, 'ajax_update_product'));

        add_action('widgets_init', array($this, 'register_widgets'));

        add_action('template_redirect', array($this, 'term_group_redirect'));

        add_action('wp_ajax_get_api_merchants', array($this, 'get_api_merchants'));
        add_action('wp_ajax_update_merchant_feed', array($this, 'update_merchant_feed'));

        add_filter('template_include', array($this, 'load_shop_template'));

        add_action('wp_logout', array($this, 'wp_logout'));

        add_filter('wp_title', array($this, 'some_callback'), 100, 2);
        add_filter('wpseo_canonical', array($this, 'canonical'));
        add_filter('wpseo_metadesc', array($this, 'description'), 100, 2);

        register_activation_hook(__FILE__, array($this, 'activation'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation'));

        add_action('trashed_post', array($this, 'my_trash_action'));

        $this->run_plugin();

        //update_metadata('wp_aff_colours', 876, 'new_metadata', 'test');
    }

    function my_trash_action($post_id) {
        if ('wp_aff_products' != get_post_type($post_id))
            return;

        $notfound = get_post_meta($post_id, 'wp_aff_product_notfound', true);
        if ($notfound == 0) {
            wp_delete_post($post_id, true);
        } else {
            return;
        }
    }

    function some_callback($title, $sep) {
        global $wp_query;
        //print_var( $wp_query );
        $shopcat = 0;
        if (get_query_var('shop-cat') != '') {
            $term = get_query_var('shop-cat');
            $tax = 'wp_aff_categories';
            $shopcat = 1;
        } elseif (get_query_var('shop-brand') != '') {
            $term = get_query_var('shop-brand');
            $tax = 'wp_aff_brands';
            $shopcat = 1;
        }
        if ($shopcat == 1 && is_page() && $wp_query->query['page_id'] == 37) {
            $cat = get_term_by('slug', $term, $tax);
            $seo_title = get_metadata('wp_aff_categories', $cat->term_id, 'aff_seo_title', true);
            if ($seo_title != '') {
                $title = $seo_title . ' |';
            } else {
                $title = $cat->name;
                if ($cat->parent != 0) {
                    $cat2 = get_term_by('id', $cat->parent, $tax);
                    $title .= ' - ' . $cat2->name;
                }
                $title .= ' for Women |';
            }

            if (is_paged()) {
                $title .= ' Page ' . get_query_var('paged') . ' |';
            }

            $title .= ' ' . get_bloginfo('name');
        } elseif ($_SERVER['REQUEST_URI'] == '/shop/') {
            $option = $this->get_option();
            $title = (isset($option['faceted']['home']['meta_title']) ? $option['faceted']['home']['meta_title'] : 'Shop for Women | ' . get_bloginfo('name'));
        }

        return $title;
    }

    function description($desc) {
        global $wp_query;
        $shopcat = 0;
        if (get_query_var('shop-cat') != '') {
            $term = get_query_var('shop-cat');
            $tax = 'wp_aff_categories';
            $shopcat = 1;
        }
        if ($shopcat == 1 && is_page() && $wp_query->query['page_id'] == 37) {
            $cat = get_term_by('slug', $term, $tax);
            $seo_desc = get_metadata('wp_aff_categories', $cat->term_id, 'aff_seo_desc', true);
            if ($seo_desc != '') {
                $desc = $seo_desc;
            }
        } elseif ($_SERVER['REQUEST_URI'] == '/shop/') {
            $option = $this->get_option();
            $desc = (isset($option['faceted']['home']['meta_desc']) ? $option['faceted']['home']['meta_desc'] : '');
        }
        return $desc;
    }

    function canonical($data) {

        global $wp_query;
        $shopcat = 0;
        if (get_query_var('shop-cat') != '') {
            $term = get_query_var('shop-cat');
            $tax = 'wp_aff_categories';
            $shopcat = 1;
        }
        if ($shopcat == 1 && is_page() && $wp_query->query['page_id'] == 37) {
            $cat = get_term_by('slug', $term, $tax);
            $seo_canonical = get_metadata('wp_aff_categories', $cat->term_id, 'aff_seo_canonical', true);
            if ($seo_canonical != '') {
                $data = $seo_canonical;
                return $data;
            }
        }

        $currentUrl = get_bloginfo('url') . $_SERVER['REQUEST_URI'];

        if (preg_match('#/shop/#', $_SERVER['REQUEST_URI'])) {

            return get_bloginfo('url') . strtok($_SERVER["REQUEST_URI"], '?');
        } elseif (preg_match('#/brands-and-boutiques/#', $_SERVER['REQUEST_URI'])) {

            global $query_string;
            parse_str($query_string, $argumentsArray);

            if (!key_exists('ss_sub_cat', $argumentsArray) && key_exists('ss_cat', $argumentsArray)) {
                $currentTerm = get_term_by('slug', $argumentsArray['ss_cat'], 'ss_category');
            } elseif (key_exists('ss_sub_cat', $argumentsArray) && key_exists('ss_cat', $argumentsArray)) {
                $currentTerm = get_term_by('slug', $argumentsArray['ss_sub_cat'], 'ss_category');
            } else {
                return $data = $currentUrl;
            }

            $termId = $currentTerm->term_id;

            if ($termId) {
                $termMeta = get_option("taxonomy_$termId");
                $seoCanonical = $termMeta['seo-canonical'];
            } else {
                $args = array(
                    'post_type' => array('brands', 'boutiques'),
                    'name' => $argumentsArray['ss_cat'],
                );
                $currentAdvertiser = get_posts($args);

                $currentSeoData = get_post_meta($currentAdvertiser[0]->ID, '_seo_metadata', true);
                $seoCanonical = $currentSeoData['seo-canonical'];
            }

            return $canonical = $seoCanonical ? $seoCanonical : $currentUrl;
        }

        return $data = $currentUrl;
    }

    public function get_option() {
        return $this->option;
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
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }

    /**
     * Creates table for feed data if it doesn't exist
     *
     * @return nothing
     */
    public function create_feedman_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . "feed_data";

        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE {$wpdb->collate}";

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			product_id varchar(255) NOT NULL,
			product_aff varchar(255) DEFAULT NULL,
			product_merch varchar(255) DEFAULT NULL,
			product_title longtext DEFAULT NULL,
			product_brand varchar(255) DEFAULT NULL,
			product_image longtext DEFAULT NULL,
			product_desc longtext DEFAULT NULL,
			product_price decimal(12,2) DEFAULT NULL,
			product_rrp decimal(12,2) DEFAULT NULL,
			product_link longtext DEFAULT NULL,
			UNIQUE KEY product_id (product_id),
			PRIMARY KEY (product_id ),
			FULLTEXT KEY product_title (product_title)
		) {$charset_collate};";

        require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_metadata_table() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $types = array(
            'wp_aff_colours',
            'wp_aff_categories'
        );

        foreach ($types as $type) {
            $table_name = $wpdb->prefix . $type . 'meta';

            $variable_name = $type . 'meta';
            $wpdb->$variable_name = $table_name;
            $wpdb->tables[] = $variable_name;

            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE {$wpdb->collate}";

            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
					meta_id bigint(20) NOT NULL AUTO_INCREMENT,
					{$type}_id bigint(20) NOT NULL default 0,

					meta_key varchar(255) DEFAULT NULL,
					meta_value longtext DEFAULT NULL,

					UNIQUE KEY meta_id (meta_id)
				) {$charset_collate};";

            dbDelta($sql);
        }
    }

    /**
     * Place code that runs at plugin activation here.
     */
    public function activation() {
        $this->create_metadata_table();
        $this->create_feedman_table();

        if ($this->man_option == FALSE) {
            $array = array(
                'schedule' => 1
            );
            update_option($this->man_option_name, $array);
        }
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
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('wp_aff_functions', $this->plugin_url . 'js/front-end.js');
        wp_localize_script('wp_aff_functions', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    /**
     * Enqueue and register CSS files here.
     */
    public function register_styles() {

        wp_enqueue_style('wp_aff_style', $this->plugin_url . 'css/front-end.css');
    }

    /**
     * Place Menu Item Here
     */

    /**
     * Enqueue and register Admin JavaScript files here.
     */
    public function admin_register_scripts() {
        wp_enqueue_script('ajaxcat');
        wp_enqueue_script('admin-categories');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-effects-core');
        wp_enqueue_script('jquery-effects-highlight');
        wp_enqueue_script('wp_aff_functions', $this->plugin_url . 'js/shop-admin.js');
        wp_enqueue_script('wp_aff_feed_functions', $this->plugin_url . 'js/man-admin.js');
    }

    /**
     * Enqueue and register Admin CSS files here.
     */
    public function admin_register_styles() {
        wp_enqueue_style('wp_aff_style', $this->plugin_url . 'css/admin.css', array(), '1.0.1');
        wp_enqueue_style('fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
    }

    /**
     * Place Menu Item Here
     */
    public function create_menu() {

        add_object_page('Affiliate Shop', 'Affiliate Shop', 'manage_options', 'affiliate-shop', array($this, 'admin_page'), $this->admin_icon);
        //add_menu_page( 'Affiliate Shop', 'Affiliate Shop', 'manage_options', 'affiliate-shop', array( $this, 'admin_page' ), $this->admin_icon, '60' );
        //$this->main_page = add_menu_page( 'Affiliate Shop', 'Affiliate Shop', 'manage_options', 'affiliate-shop', array( $this, 'admin_page' ), $this->admin_icon, 58 );
        $this->main_page = add_submenu_page('affiliate-shop', 'Categories', 'Categories', 'manage_options', 'affiliate-shop', array($this, 'admin_page'));
        $this->products = add_submenu_page('affiliate-shop', 'Products', 'Products', 'manage_options', 'affiliate-shop/products', array($this, 'products'));
        $this->add_products = add_submenu_page('affiliate-shop', 'Add Products', 'Add Products', 'manage_options', 'affiliate-shop/add-products', array($this, 'add_products'));
        $this->brands = add_submenu_page('affiliate-shop', 'Brands', 'Brands', 'manage_options', 'affiliate-shop/brands', array($this, 'list_brands'));


        $this->colours = add_submenu_page('affiliate-shop', 'Colours', 'Colours', 'manage_options', 'affiliate-shop/colours', array($this, 'colours_page'));
        $this->sizes = add_submenu_page('affiliate-shop', 'Sizes', 'Sizes', 'manage_options', 'affiliate-shop/sizes', array($this, 'sizes_page'));

        $this->settings = add_submenu_page('affiliate-shop', 'Settings', 'Settings', 'manage_options', 'affiliate-shop/settings', array($this, 'settings_page'));


        add_action("load-$this->man_settings", array($this, 'parse_message'));
        add_action("load-$this->man_main_page", array($this, 'parse_message'));

        add_action("load-$this->settings", array($this, 'parse_message'));
        add_action("load-$this->add_products", array($this, 'parse_message'));
        add_action("load-$this->brands", array($this, 'parse_message'));
        add_action("load-$this->colours", array($this, 'parse_message'));
        add_action("load-$this->sizes", array($this, 'parse_message'));
        add_action("load-$this->main_page", array($this, 'parse_message'));
        add_action("load-$this->products", array($this, 'parse_message'));
    }

    private function man_page_title() {
        echo '<h2>' . $this->man_page_title . '</h2>';
    }

    public function register_widgets() {
        register_widget('aff_category_widget');
        register_widget('aff_brand_widget');
        register_widget('aff_price_widget');
        register_widget('aff_colour_widget');
        register_widget('aff_size_widget');
        register_widget('aff_active_widget');
        register_widget('aff_sale_widget');
    }

    public function custom_rewrite_rule() {
        add_rewrite_tag('%shop-option%', '([^&]+)');
        add_rewrite_tag('%shop-cat%', '([^&]+)');
        add_rewrite_tag('%shop-brand%', '([^&]+)');
        add_rewrite_tag('%shop-sitemap%', '([^&]+)');

        add_rewrite_rule('shop/sitemaps/([^/]+?)$', 'index.php?shop-sitemap=$matches[1]', 'top');

        add_rewrite_rule('shop/new-in/?$', 'index.php?page_id=37&shop-option=new', 'top');
        add_rewrite_rule('shop/sale/?$', 'index.php?page_id=37&shop-option=sale', 'top');
        add_rewrite_rule('shop/our-picks/?$', 'index.php?page_id=37&shop-option=picks', 'top');
        add_rewrite_rule('shop/new-in/page/?([0-9]+)/?$', 'index.php?page_id=37&shop-option=new&paged=$matches[1]', 'top');
        add_rewrite_rule('shop/sale/page/?([0-9]+)/?$', 'index.php?page_id=37&shop-option=sale&paged=$matches[1]', 'top');
        add_rewrite_rule('shop/our-picks/page/?([0-9]+)/?$', 'index.php?page_id=37&shop-option=picks&paged=$matches[1]', 'top');
        add_rewrite_rule('shop/([^/]+)/?$', 'index.php?page_id=37&shop-cat=$matches[1]');
        add_rewrite_rule('shop/brand/([^/]+)/?$', 'index.php?page_id=37&shop-brand=$matches[1]');
        add_rewrite_rule('shop/([^/]+)/page/?([0-9]+)/?$', 'index.php?page_id=37&shop-cat=$matches[1]&paged=$matches[2]', 'top');
        add_rewrite_rule('shop/([^/]+)/?$', 'index.php?page_id=37&shop-cat=$matches[1]', 'top');
    }

    public function term_group_redirect() {
        global $wp_query;
        if (isset($wp_query->query_vars['shop-cat'])) {
            $term = get_term_by('slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories');
            if ($term->term_group > 1) {
                $alias = get_term_by('id', $term->term_group, 'wp_aff_categories');
                wp_redirect(get_site_url() . '/shop/' . $alias->slug, 301);
                exit;
            }
        }
    }

    public function load_shop_template($template) {
        if (get_the_ID() == $this->option['shop_page'] || is_page('search')) {
            if ($overridden_template = locate_template('shop-template.php')) {
                load_template($overridden_template);
            } else {
                load_template(dirname(__FILE__) . '/templates/shop-template.php');
            }
        } else {
            return $template;
        }
    }

    public function parse_message() {
        if (!isset($_GET['msg']))
            return;

        $text = FALSE;

        switch ($_GET['msg']) {
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

        if ($this->msg_text)
            add_action('admin_notices', array($this, 'render_msg'));
    }

    public function render_msg() {
        echo '<div id="message" class="' . $this->msg_class . '"><p>'
        . $this->msg_text . '</p></div>';
    }

    public function register_post_type() {

        if (!isset($_SESSION)) {
            session_start();
        }

        $labels = array(
            'name' => _x('WP Affiliate Shop Products', 'post type general name', 'your-plugin-textdomain'),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'products'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'taxonomies' => array('wp_aff_categories'),
            'menu_position' => null,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'page-attributes'),
            'exclude_from_search' => false,
        );

        register_post_type('wp_aff_products', $args);
    }

    public function register_taxonomies() {
        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name' => _x('Aff Categories', 'taxonomy general name'),
            'singular_name' => _x('Aff Category', 'taxonomy singular name'),
            'search_items' => __('Search Categories'),
            'all_items' => __('Product Categories'),
            'parent_item' => __('Parent Category'),
            'parent_item_colon' => __('Parent Category:'),
            'edit_item' => __('Edit Category'),
            'update_item' => __('Update Category'),
            'add_new_item' => __('Add New Category'),
            'new_item_name' => __('New Category Name'),
            'menu_name' => __('Category'),
        );

        $args = array(
            'hierarchical' => true,
            'public' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'shop', 'with_front' => false),
        );

        $labels2 = array(
            'name' => _x('Colours', 'taxonomy general name'),
            'all_items' => __('Product Colours'),
        );

        $args2 = array(
            'hierarchical' => true,
            'public' => false,
            'labels' => $labels2,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'aff-colours', 'with_front' => false),
        );

        $labels3 = array(
            'name' => _x('Sizes', 'taxonomy general name'),
            'all_items' => __('Product Sizes'),
        );

        $args3 = array(
            'hierarchical' => true,
            'public' => false,
            'labels' => $labels3,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'aff-colours', 'with_front' => false),
        );

        $labels4 = array(
            'name' => _x('Brand', 'taxonomy general name'),
            'all_items' => __('Brands'),
        );

        $args4 = array(
            'hierarchical' => true,
            'public' => false,
            'labels' => $labels4,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'aff-brands', 'with_front' => false),
        );

        register_taxonomy('wp_aff_categories', array('wp_aff_products'), $args);
        register_taxonomy('wp_aff_colours', array('wp_aff_products'), $args2);
        register_taxonomy('wp_aff_sizes', array('wp_aff_products'), $args3);
        register_taxonomy('wp_aff_brands', array('wp_aff_products'), $args4);
    }

    public function shop_args($all = false) {
        global $wp_query;
        $terms = get_terms('wp_aff_categories', array('orderby' => 'term_group', 'order' => 'DESC'));
        //print_var($terms);
        //print_var($wp_query->query_vars);
        if (isset($wp_query->query_vars['shop-cat'])) {
            $term = get_term_by('slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories');
            if ($term->term_group < 2) {
                $catID = $term->term_id;
                $parent = $catID;
            } else {
                $catID = $term->term_group;
                $parent = $catID;
            }
        } elseif (isset($wp_query->query_vars['shop-brand'])) {
            $term = get_term_by('slug', $wp_query->query_vars['shop-brand'], 'wp_aff_brands');
            $catID = $term->term_id;
            $parent = $catID;
        } else {
            $catID = 0;
            $parent = '0';
        }
        if (isset($_REQUEST['per_page']) && $_REQUEST['per_page'] != 'all') {
            $per_page = $_REQUEST['per_page'];
        } elseif ((isset($_REQUEST['per_page']) && $_REQUEST['per_page'] == 'all') || $all == true) {
            $per_page = -1;
        } else {
            $per_page = 18;
        }
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array('post_type' => 'wp_aff_products',
            'posts_per_page' => $per_page,
            'paged' => $paged
        );

        if (isset($wp_query->query_vars['shop-cat'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'wp_aff_categories',
                    'field' => 'id',
                    'terms' => $catID,
                ),
            );
        } elseif (isset($wp_query->query_vars['shop-brand'])) {
            $args['tax_query'] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'wp_aff_brands',
                    'field' => 'id',
                    'terms' => $catID,
                ),
            );
        } elseif (isset($wp_query->query_vars['shop-option'])) {

            if ($wp_query->query_vars['shop-option'] == 'new') {
                $args = $this->retrieveNewInProducts($args['posts_per_page']);
                $args['meta_query']['relation'] = 'AND';
                $args['meta_query'][] = array(
                    'key' => 'wp_aff_product_sale',
                    'value' => 0,
                    'compare' => '=',
                );
            } elseif ($wp_query->query_vars['shop-option'] == 'sale') {
                $args['meta_query']['relation'] = 'AND';
                $args['meta_query'][] = array(
                    'key' => 'wp_aff_product_sale',
                    'value' => '1',
                    'compare' => '=',
                );
            } elseif ($wp_query->query_vars['shop-option'] == 'picks') {
                $args['meta_query']['relation'] = 'AND';
                $args['meta_query'][] = array(
                    'key' => 'wp_aff_product_picks',
                    'value' => 1,
                    'compare' => '=',
                );
            }
        }
        if (
                isset($_GET['colour']) ||
                isset($_GET['size']) ||
                isset($_GET['brand']) ||
                isset($_GET['category']) ||
                isset($_GET['options']) ||
                isset($_GET['price-min']) ||
                isset($_GET['price-max'])
        ) {
            foreach ($_GET as $key => $value) {
                if ($value != -1) {
                    if ($key != 'price-min' && $key != 'price-max') {
                        switch ($key) {
                            case 'colour' :
                                $cols = explode(',', $_REQUEST['colour']);
                                $args['tax_query'][] = array(
                                    'relation' => 'OR',
                                    array(
                                        'taxonomy' => 'wp_aff_colours',
                                        'field' => 'id',
                                        'terms' => $cols,
                                    )
                                );
                                break;
                            case 'size' :
                                $args['tax_query'][] = array(
                                    'taxonomy' => 'wp_aff_sizes',
                                    'field' => 'id',
                                    'terms' => $_REQUEST['size'],
                                );
                                break;
                            case 'brand' :
                                $args['tax_query'][] = array(
                                    'taxonomy' => 'wp_aff_brands',
                                    'field' => 'slug',
                                    'terms' => $_REQUEST['brand'],
                                );
                                break;
                            case 'category' :
                                $args['tax_query'][] = array(
                                    'taxonomy' => 'wp_aff_categories',
                                    'field' => 'slug',
                                    'terms' => $_REQUEST['category'],
                                );
                                break;
                            case 'shop-option':
                            case 'options' :
                                $options = explode(',', $_REQUEST['options']);
                                foreach ($options as $option) {
                                    switch ($option) {
                                        case 'new' :
                                            $args = $this->retrieveNewInProducts($args['posts_per_page']);
                                            $args['meta_query']['relation'] = 'AND';
                                            $args['meta_query'][] = array(
                                                'key' => 'wp_aff_product_sale',
                                                'value' => 0,
                                                'compare' => '==',
                                            );

                                            break;
                                        case 'our-picks' :
                                            $args['meta_query']['relation'] = 'AND';
                                            $args['meta_query'][] = array(
                                                'key' => 'wp_aff_product_picks',
                                                'value' => 1,
                                                'compare' => '=',
                                            );
                                            break;
                                        case 'sale' :
                                            $args['meta_query']['relation'] = 'AND';
                                            $args['meta_query'][] = array(
                                                'key' => 'wp_aff_product_sale',
                                                'value' => 1,
                                                'compare' => '=',
                                            );
                                            break;
                                    }
                                }
                                break;
                        }
                    } else {
                        $args['meta_query']['relation'] = 'AND';
                        $args['meta_query'][] = array(
                            'key' => 'wp_aff_product_price',
                            'value' => array($_REQUEST['price-min'], $_REQUEST['price-max']),
                            'type' => 'numeric',
                            'compare' => 'BETWEEN',
                        );
                    }
                }
            }
        } else {
            if (!isset($args)) {
                $args = array(
                    'post_type' => 'wp_aff_products',
                    'posts_per_page' => $per_page,
                    'paged' => $paged
                );
            }
        }
        if (isset($_REQUEST['sortby'])) {
            switch ($_REQUEST['sortby']) {
                case 'priceasc' :
                    $args['meta_key'] = 'wp_aff_product_price';
                    $args['meta_type'] = 'DECIMAL';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;
                case 'pricedesc' :
                    $args['meta_key'] = 'wp_aff_product_price';
                    $args['meta_type'] = 'DECIMAL';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                case 'sale' :

                    break;
                case 'toppicks' :

                    break;
                case 'new' :
                    $args['orderby'] = 'post_date';
                    $args['order'] = 'DESC';
                    break;
            }
        } elseif (!isset($args['orderby'])) {
            $args['orderby'] = 'rand';
            $args['order'] = 'DESC';
        }
        $args['meta_query'][] = array(
            'key' => 'wp_aff_product_image',
            'value' => '',
            'compare' => '!=',
        );
        $args['meta_query'][] = array(
            'key' => 'wp_aff_product_link',
            'value' => '',
            'compare' => '!=',
        );
        //print_var( $args );
        return $args;
    }

    private function retrieveNewInProducts($postsPerPage) {
        $newInProductIdsByCategory = $this->getNewInProductIdsByCategory();
        $singleNewInProductIds = $this->getSingleNewInProductIds();
        $newInProducts = array_merge($newInProductIdsByCategory, $singleNewInProductIds);
        $options = $this->get_option();
        $pastdate = strtotime('-' . ($options['new_days'] - 1) . ' days');
        $date = getdate($pastdate);
        $args['posts_per_page'] = $postsPerPage;
        $args['post_type'] = 'wp_aff_products';
        $args['post__in'] = $newInProducts;
        $args['date_query'] = array(
            array(
                'after' => array(
                    'year' => $date['year'],
                    'month' => $date['mon'],
                    'day' => $date['mday'],
                ),
                'inclusive' => true,
            ),
        );

        $args['orderby'] = 'rand';
        $args['order'] = 'DESC';

        return $args;
    }

    private function getSingleNewInProductIds() {
        $args = array(
            'post_type' => 'wp_aff_products',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'wp_aff_product_new_in',
                    'value' => 1,
                    'compare' => '='
                )
            )
        );

        $productIds = get_posts($args);

        return $productIds;
    }

    private function getNewInProductIdsByCategory() {
        $newInCategoryIds = [];
        $wpAffCategories = get_terms('wp_aff_categories');
        foreach ($wpAffCategories as $wpAffCategory) {
            $newInItem = get_post_meta($wpAffCategory->term_id, 'wp_aff_category_new_in', true);
            if ($newInItem == 1) {
                $newInCategoryIds[] = $wpAffCategory->term_id;
            }
        }

        $args = array(
            'post_type' => 'wp_aff_products',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'wp_aff_categories',
                    'terms' => $newInCategoryIds,
                )
            )
        );

        $productIds = get_posts($args);

        return $productIds;
    }

    public function get_product_terms($taxonomy) {
        $arg = $this->shop_args(true);
        /* print_var( $arg );

          global $wpdb;

          $query = "
          SELECT $wpdb->term_taxonomy.term_taxonomy_id FROM $wpdb->posts
          LEFT JOIN $wpdb->term_relationships ON
          ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
          LEFT JOIN $wpdb->term_taxonomy ON
          ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
          WHERE $wpdb->posts.post_status = 'publish'
          AND $wpdb->posts.post_type = 'wp_aff_products'
          GROUP BY $wpdb->term_taxonomy.term_taxonomy_id
          ORDER BY $wpdb->term_taxonomy.term_taxonomy_id ASC
          ";
          $results = $wpdb->get_results($query);
          print_var( $results ); */
        $query = new WP_Query($arg);
        $fn_cats = array();
        $ids = array();
        foreach ($query->posts AS $post) {
            $ids[] = $post->ID;
        }
        $cats = wp_get_object_terms($ids, $taxonomy, array('fields' => 'ids'));

        $fn_cats = array_unique($cats);
        $fn_cats = implode(',', $fn_cats);
        //print_var( $fn_cats );
        if ($fn_cats == '') {
            return 'none';
        } else {
            //print_var( $fn_cats );
            return $fn_cats;
        }
    }

    public function wp_aff_size_filter() {
        //print_var( $_POST );
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_size_filter'))
            die('Invalid nonce.' . var_export($_POST, true));
        $sizes = array();
        if (is_array($_REQUEST['wp_aff_sizes'])) {
            $sizes = $_REQUEST['wp_aff_sizes'];
        } else {
            $sizes[] = $_REQUEST['wp_aff_sizes'];
        }

        $url = preg_replace('#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer']);
        $url = str_replace('%2C', ',', $url);
        if (empty($sizes[0])) {
            $url = remove_query_arg('size', $url);
        } else {
            $sizes = array_unique($sizes);
            $sizes = implode($sizes, ',');
            $url = add_query_arg('size', $sizes, $url);
        }
        $url = str_replace('%2C', ',', $url);
        echo $url;
        wp_safe_redirect($url);
    }

    public function wp_aff_colour_filter() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_colour_filter'))
            die('Invalid nonce.' . var_export($_POST, true));

        $url = preg_replace('#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer']);

        if (is_array($_REQUEST['wp_aff_colours'])) {
            $colours = $_REQUEST['wp_aff_colours'];
        } else {
            $colours[] = $_REQUEST['wp_aff_colours'];
        }
        $colours = array_unique($colours);
        if (empty($colours[0])) {
            $url = remove_query_arg('colour', $url);
        } else {
            $colours = implode($colours, ',');
            $url = add_query_arg('colour', $colours, $url);
        }


        $url = add_query_arg('colour', $colours, $url);
        $url = str_replace('%2C', ',', $url);


        wp_safe_redirect($url);
    }

    public function wp_aff_price_filter() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_price_filter'))
            die('Invalid nonce. ' . var_export($_POST, true));
        $url = preg_replace('#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer']);
        $url = add_query_arg('price-min', $_REQUEST['price-min'], $url);
        $url = add_query_arg('price-max', $_REQUEST['price-max'], $url);
        $url = str_replace('%2C', ',', $url);
        wp_safe_redirect($url);
    }

    public function wp_aff_brand_filter() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_brand_filter'))
            die('Invalid nonce. ' . var_export($_POST, true));
        $url = preg_replace('#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer']);

        if (is_array($_REQUEST['brands'])) {
            $brands = $_REQUEST['brands'];
        } else {
            $brands[] = $_REQUEST['brands'];
        }
        $brands = array_unique($brands);
        if (empty($brands[0])) {
            $url = remove_query_arg('brand', $url);
        } else {
            $brands = implode($brands, ',');
            $url = add_query_arg('brand', $brands, $url);
        }
        $url = str_replace('%2C', ',', $url);
        echo $url;
        wp_safe_redirect($url);
    }

    public function sort_shop() {
        $url = preg_replace('#page/([0-9]+)/#', '', $_REQUEST['redirect']);
        $url = add_query_arg('sortby', $_REQUEST['sortby'], $url);
        $url = str_replace('%2C', ',', $url);
        echo $url;
        die();
    }

    public function wp_aff_sale_filter() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_sale_filter'))
            die('Invalid nonce. ' . var_export($_POST, true));

        $url = preg_replace('#page/([0-9]+)/#', '', $_REQUEST['_wp_http_referer']);
        $url = str_replace(array('our-picks/', 'sale/', 'new/'), '', $url);
        if (isset($_POST['wp_aff_new_in'])) {
            if ($_POST['wp_aff_new_in'] == 2) {
                $args[] = 'new';
            } else {
                $url = '/shop/new-in/';
            }
        }
        if (isset($_POST['wp_aff_sale'])) {
            if ($_POST['wp_aff_sale'] == 2) {
                $args[] = 'sale';
            } else {
                $url = '/shop/sale/';
            }
        }
        if (isset($_POST['wp_aff_toppicks'])) {
            if ($_POST['wp_aff_toppicks'] == 2) {
                $args[] = 'our-picks';
            } else {
                $url = '/shop/our-picks/';
            }
        }

        $url = add_query_arg('options', implode(',', $args), $url);
        $url = str_replace('%2C', ',', $url);
        wp_safe_redirect($url);
    }

    public function remove_facted_element() {
        global $wp_query;
        if ($_REQUEST['type'] == 'price') {
            $url = remove_query_arg('price-min', $_REQUEST['redirect']);
            $url = remove_query_arg('price-max', $url);
        } elseif ($_REQUEST['type'] == 'category' && !isset($_REQUEST['category'])) {
            $url = str_replace($_REQUEST['term'] . '/', '', $_REQUEST['redirect']);
        } else {
            $url = remove_query_arg($_REQUEST['type'], $_REQUEST['redirect']);
        }

        echo $url;
        die();
    }

    public function faceted_cat_ajax() {
        $walker = new Faceted_Category_Walker;
        $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'style' => 'list',
            'hide_empty' => 0,
            'use_desc_for_title' => 0,
            'child_of' => $_POST['cat'],
            'exclude' => '',
            'hierarchical' => 1,
            'title_li' => __(''),
            'show_option_none' => __(''),
            'number' => null,
            'echo' => 1,
            'depth' => 1,
            'taxonomy' => 'wp_aff_categories',
            'walker' => $walker
        );
        wp_list_categories($args);
        die();
    }

    public function wp_aff_add_products() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_add_products'))
            die('Invalid nonce.' . var_export($_POST, true));


        //var_export( $_POST, true );

        $count = count($_POST['product_image']);

        $data = array();

        if (isset($_POST['wp_aff_categories']['all'])) {
            $data['categories']['all'] = $_POST['wp_aff_categories']['all'];
        } else {
            $data['categories']['all'] = array();
        }
        if (isset($_POST['wp_aff_colours']['all'])) {
            $data['colours']['all'] = $_POST['wp_aff_colours']['all'];
        } else {
            $data['colours']['all'] = array();
        }
        if (isset($_POST['wp_aff_sizes']['all'])) {
            $data['sizes']['all'] = $_POST['wp_aff_sizes']['all'];
        } else {
            $data['sizes']['all'] = array();
        }
        //print_var($data);
        unset($_POST['wp_aff_categories']['all']);
        unset($_POST['wp_aff_colours']['all']);
        unset($_POST['wp_aff_sizes']['all']);
        //print_var($_POST);
        for ($i = 0; $i < $count; $i++) {

            if (isset($_POST['wp_aff_categories'][$i])) {
                $data['categories'][$i] = array_merge($data['categories']['all'], $_POST['wp_aff_categories'][$i]);
                $data['categories'][$i] = array_unique($data['categories'][$i]);
                $data['categories'][$i] = array_values($data['categories'][$i]);
            } else {
                $data['categories'][$i] = $data['categories']['all'];
            }
            if (isset($_POST['wp_aff_colours'][$i])) {
                $data['colours'][$i] = array_merge($data['colours']['all'], $_POST['wp_aff_colours'][$i]);
                $data['colours'][$i] = array_unique($data['colours'][$i]);
                $data['colours'][$i] = array_values($data['colours'][$i]);
            } else {
                $data['colours'][$i] = $data['colours']['all'];
            }
            if (isset($_POST['wp_aff_sizes'][$i])) {
                $data['sizes'][$i] = array_merge($data['sizes']['all'], $_POST['wp_aff_sizes'][$i]);
                $data['sizes'][$i] = array_unique($data['sizes'][$i]);
                $data['sizes'][$i] = array_values($data['sizes'][$i]);
            } else {
                $data['sizes'][$i] = $data['sizes']['all'];
            }
            /* if(($key = array_search(0, $data['categories'][$i])) !== false) {
              unset($data['categories'][$i][$key]);
              }
              if(($key = array_search(0, $data['colours'][$i])) !== false) {
              unset($data['colours'][$i][$key]);
              }
              if(($key = array_search(0, $data['sizes'][$i])) !== false) {
              unset($data['sizes'][$i][$key]);
              }
             */

            if ($term_id = term_exists($_POST['product_brand'][$i], 'wp_aff_brands')) {
                $brand = $term_id['term_id'];
            } else {
                $term = wp_insert_term($_POST['product_brand'][$i], 'wp_aff_brands');
                if (!is_wp_error($term)) {
                    $brand = $term['term_id'];
                } else {
                    $brand = '';
                }
            }
            //echo $_POST['product_brand'][$i].' :: '.$brand;
            $my_post = array(
                'post_title' => $_POST['product_name'][$i],
                'post_status' => 'publish',
                'post_type' => 'wp_aff_products',
                'tax_input' => array(
                    'wp_aff_categories' => $data['categories'][$i],
                    'wp_aff_sizes' => $data['sizes'][$i],
                    'wp_aff_colours' => $data['colours'][$i],
                    'wp_aff_brands' => $brand
                )
            );

            // Insert the post into the database

            if ($_POST['product_skip'][$i] == 0) {
                $insID = wp_insert_post($my_post);

                if (($_POST['product_price'][$i] != '' || $_POST['product_price'][$i] != null || $_POST['product_price'][$i] != '0' || $_POST['product_price'][$i] != '0.00') && $_POST['product_price'][$i] < $_POST['product_rrp'][$i]) {
                    add_post_meta($insID, 'wp_aff_product_sale', 1);
                } else {
                    add_post_meta($insID, 'wp_aff_product_sale', 0);
                }

                add_post_meta($insID, 'wp_aff_product_id', $_POST['product_id'][$i], true);
                add_post_meta($insID, 'wp_aff_product_link', $_POST['product_link'][$i], true);
                add_post_meta($insID, 'wp_aff_product_price', $_POST['product_price'][$i], true);
                add_post_meta($insID, 'wp_aff_product_rrp', $_POST['product_rrp'][$i], true);
                //add_post_meta($insID, 'wp_aff_product_brand', , true);
                add_post_meta($insID, 'wp_aff_product_desc', $_POST['product_desc'][$i], true);
                add_post_meta($insID, 'wp_aff_product_image', $_POST['product_image'][$i], true);
                add_post_meta($insID, 'wp_aff_product_aff', $_POST['product_aff'][$i], true);
            }
        }
        //print_var($data);
        unset($_SESSION['products']);
        unset($_SESSION['product_data']);
        session_regenerate_id();
        $url = add_query_arg('msg', 6, admin_url('admin.php?page=affiliate-shop/products'));

        wp_safe_redirect($url);
    }

    public function wp_aff_update_settings() {
        if (!wp_verify_nonce($_POST[$this->option_name . '_nonce'], 'wp_aff_save_api'))
            die('Invalid nonce.' . var_export($_POST, true));
        $array = $this->get_option();
        if (isset($_POST[$this->option_name])) {
            foreach ($_POST[$this->option_name] AS $key => $value) {
                $array[$key] = $value;
            }
            update_option($this->option_name, $array);
            $msg = 1;
        } else {
            delete_option($this->option_name);
            $msg = 3;
        }

        if (!isset($_POST['_wp_http_referer']))
            die('Missing target.');

        $url = add_query_arg('msg', $msg, urldecode($_POST['_wp_http_referer']));

        wp_safe_redirect($url);
        exit;
    }

    public function wp_aff_add_category() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_add_category'))
            die('Invalid nonce.' . var_export($_POST, true));

        $term = array();
        $term['name'] = sanitize_text_field($_POST['wp_term_name']);
        $term['desc'] = htmlspecialchars($_POST['wp_term_desc']);

        $term['seo_title'] = sanitize_text_field($_POST['wp_seo_title']);
        $term['seo_desc'] = sanitize_text_field($_POST['wp_seo_desc']);

        $term['parent'] = intval($_POST['wp_term_parent']);
        $term['alias'] = intval($_POST['wp_term_alias']);
        $term['slug'] = sanitize_text_field($_POST['wp_term_slug']);

        if ($term['alias'] == -1) {
            $termarray = wp_insert_term($term['name'], 'wp_aff_categories', $args = array('slug' => $term['slug'], 'description' => $term['desc'], 'parent' => $term['parent']));
            global $wpdb;
            $wpdb->query($wpdb->prepare(
                            "
                    UPDATE $wpdb->terms
                    SET term_group = %d
                    WHERE term_id = %d
                    ", 0, $termarray['term_id']
            ));
        } else {
            $alias = get_term($term['alias'], 'wp_aff_categories');
            $term['alias'] = $alias->slug;
            $term['alias_id'] = $alias->term_id;
            //print_var($term);
            $termarray = wp_insert_term($term['name'], 'wp_aff_categories', $args = array('slug' => $term['slug'], 'alias_of' => $term['alias'], 'description' => $term['desc'], 'parent' => $term['parent']));
            //print_var( $termarray );
            global $wpdb;
            $wpdb->query($wpdb->prepare(
                            "
                    UPDATE $wpdb->terms
                    SET term_group = %d
                    WHERE term_id = %d
                    ", $term['alias_id'], $termarray['term_id']
            ));
        }

        if (is_wp_error($termarray)) {
            echo $termarray->get_error_message();
            $msg = 5;
            $url = add_query_arg('msg', $msg, urldecode($_POST['_wp_http_referer']));
        } else {
            update_metadata('wp_aff_categories', $termarray['term_id'], 'aff_seo_title', $term['seo_title']);
            update_metadata('wp_aff_categories', $termarray['term_id'], 'aff_seo_desc', $term['seo_desc']);
            $msg = 4;
            $url = add_query_arg('msg', $msg, admin_url('admin.php?page=affiliate-shop'));
        }
        if (!isset($_POST['_wp_http_referer']))
            die('Missing target.');


        wp_safe_redirect($url);
        exit;
    }

    public function wp_aff_add_colours() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_add_colours'))
            die('Invalid nonce.' . var_export($_POST, true));

        $term = array();
        $term['name'] = sanitize_text_field($_POST['wp_term_name']);
        $term['slug'] = sanitize_text_field($_POST['wp_term_slug']);
        $term['colour'] = sanitize_text_field($_POST['wp_term_colour']);
        $term['css'] = sanitize_text_field($_POST['wp_term_colour_css']);

        $termarray = wp_insert_term(
                $term['name'], 'wp_aff_colours', $args = array(
            'slug' => $term['slug'],
                )
        );

        if (is_wp_error($termarray)) {
            $msg = 2;
            $url = add_query_arg('msg', $msg, urldecode($_POST['_wp_http_referer']));
        } else {
            update_metadata('wp_aff_colours', $termarray['term_id'], 'colour_code', $term['colour']);
            update_metadata('wp_aff_colours', $termarray['term_id'], 'colour_code_css', $term['css']);
            $msg = 8;
            $url = add_query_arg('msg', $msg, admin_url('admin.php?page=affiliate-shop/colours'));
        }
        if (!isset($_POST['_wp_http_referer']))
            die('Missing target.');

        //print_var($term);
        wp_safe_redirect($url);
        exit;
    }

    public function wp_aff_add_sizes() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_add_sizes'))
            die('Invalid nonce.' . var_export($_POST, true));

        $term = array();
        $term['name'] = sanitize_text_field($_POST['wp_term_name']);
        $term['slug'] = sanitize_text_field($_POST['wp_term_slug']);


        $termarray = wp_insert_term(
                $term['name'], 'wp_aff_sizes', $args = array(
            'slug' => $term['slug'],
                )
        );

        if (is_wp_error($termarray)) {
            $msg = 2;
            $url = add_query_arg('msg', $msg, urldecode($_POST['_wp_http_referer']));
        } else {
            $msg = 9;
            $url = add_query_arg('msg', $msg, admin_url('admin.php?page=affiliate-shop/sizes'));
        }
        if (!isset($_POST['_wp_http_referer']))
            die('Missing target.');

        //print_var($term);
        wp_safe_redirect($url);
        exit;
    }

    public function wp_aff_edit_colours() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_edit_colours'))
            die('Invalid nonce.' . var_export($_POST, true));

        $term = array();
        $term['id'] = sanitize_text_field($_POST['wp_term_id']);
        $term['name'] = sanitize_text_field($_POST['wp_term_name']);
        $term['slug'] = sanitize_text_field($_POST['wp_term_slug']);
        $term['colour'] = sanitize_text_field($_POST['wp_term_colour']);
        $term['css'] = sanitize_text_field($_POST['wp_term_colour_css']);

        $termarray = wp_update_term(
                $term['id'], 'wp_aff_colours', $args = array(
            'name' => $term['name'],
            'slug' => $term['slug'],
                )
        );

        if (is_wp_error($termarray)) {
            $msg = 2;
            $url = add_query_arg('msg', $msg, urldecode($_POST['_wp_http_referer']));
        } else {
            update_metadata('wp_aff_colours', $term['id'], 'colour_code', $term['colour']);
            update_metadata('wp_aff_colours', $term['id'], 'colour_code_css', $term['css']);
            $msg = 8;
            $url = add_query_arg('msg', $msg, admin_url('admin.php?page=affiliate-shop/colours'));
        }
        if (!isset($_POST['_wp_http_referer']))
            die('Missing target.');

        //print_var($term);
        wp_safe_redirect($url);
        exit;
    }

    public function wp_aff_edit_category() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_edit_category'))
            die('Invalid nonce.' . var_export($_POST, true));

        $term = array();
        $term['id'] = sanitize_text_field($_POST['wp_term_id']);
        $term['name'] = sanitize_text_field($_POST['wp_term_name']);
        $term['slug'] = sanitize_text_field($_POST['wp_term_slug']);
        $term['desc'] = htmlspecialchars($_POST['wp_term_desc']);

        $term['seo_title'] = sanitize_text_field($_POST['wp_seo_title']);
        $term['seo_desc'] = sanitize_text_field($_POST['wp_seo_desc']);
        $term['seo_canonical'] = sanitize_text_field($_POST['wp_seo_canonical']);

        $term['alias'] = intval($_POST['wp_term_alias']);
        $term['parent'] = intval($_POST['wp_term_parent']);
        if ($term['alias'] == -1) {
            $termarray = wp_update_term($term['id'], 'wp_aff_categories', $args = array('name' => $term['name'], 'slug' => $term['slug'], 'description' => $term['desc'], 'parent' => $term['parent']));
            global $wpdb;
            $wpdb->query($wpdb->prepare(
                            "
                    UPDATE $wpdb->terms
                    SET term_group = %d
                    WHERE term_id = %d
                    ", 0, $termarray['term_id']
            ));
        } else {
            $alias = get_term($term['alias'], 'wp_aff_categories');
            $term['alias'] = $alias->slug;
            $term['alias_id'] = $alias->term_id;
            $termarray = wp_update_term($term['id'], 'wp_aff_categories', $args = array('name' => $term['name'], 'slug' => $term['slug'], 'description' => $term['desc'], 'parent' => $term['parent']));
            global $wpdb;
            $wpdb->query($wpdb->prepare(
                            "
                    UPDATE $wpdb->terms
                    SET term_group = %d
                    WHERE term_id = %d
                    ", $term['alias_id'], $termarray['term_id']
            ));
        }
        if (is_wp_error($termarray)) {
            $msg = 5;
            $url = add_query_arg('msg', $msg, urldecode($_POST['_wp_http_referer']));
        } else {
            update_metadata('wp_aff_categories', $termarray['term_id'], 'aff_seo_title', $term['seo_title']);
            update_metadata('wp_aff_categories', $termarray['term_id'], 'aff_seo_desc', $term['seo_desc']);
            update_metadata('wp_aff_categories', $termarray['term_id'], 'aff_seo_canonical', $term['seo_canonical']);
            $msg = 7;
            $url = add_query_arg('msg', $msg, urldecode($_POST['_wp_http_referer']));
        }
        if (!isset($_POST['_wp_http_referer']))
            die('Missing target.');

        //print_var($term);
        wp_safe_redirect($url);
        exit;
    }

    public function wp_aff_product_search() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_product_search'))
            die('Invalid nonce.' . var_export($_POST, true));
        $url = admin_url('admin.php?page=affiliate-shop/add-products');
        $url = add_query_arg('q', $_POST['q'], $url);
        $url = add_query_arg('wp_aff_merch', $_POST['wp_aff_merch'], $url);
        $url = add_query_arg('api', $_POST['wp_aff_api'], $url);
        if (!isset($_POST['_wp_http_referer']))
            die('Missing target.');
        wp_safe_redirect($url);
        exit;
    }

    /**
     * The output for the main page of the plugin
     *
     * @return echo to screen
     */
    public function main_manager_page() {
        ?>
        <div class="wrap">
            <?php $this->man_page_title(); ?>
            <h3>Feeds</h3>

            <?php
        }

        /**
         * The output for the settings page of the plugin
         *
         * @return echo to screen
         */
        public function manager_settings_page() {
            ?>
            <div class="wrap">
                <?php $this->man_page_title(); ?>
                <h3>Update Feed</h3>

                <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                    <table class="form-table">
                        <tr>
                            <th>Update frequency</th>
                            <td>
                                <select name="<?php echo $this->man_option_name; ?>[product_update][frequency]"
                                        value="<?php echo(isset($option['product_update']['frequency']) ? $option['product_update']['frequency'] : ''); ?>"
                                        id="<?php echo $this->man_option_name; ?>[product_update][frequency]">
                                    <option <?php if (isset($option['product_update']['frequency'])) selected($option['product_update']['frequency'], 0, true); ?>
                                        value="0">Manual
                                    </option>
                                    <option <?php if (isset($option['product_update']['frequency'])) selected($option['product_update']['frequency'], 1, true); ?>
                                        value="1">Daily
                                    </option>
                                    <option <?php if (isset($option['product_update']['frequency'])) selected($option['product_update']['frequency'], 2, true); ?>
                                        value="2">Weekly
                                    </option>
                                    <option <?php if (isset($option['product_update']['frequency'])) selected($option['product_update']['frequency'], 3, true); ?>
                                        value="3">Monthly
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Manual Update</th>
                            <td><a href="<?php echo $_SERVER['REQUEST_URI']; ?>"
                                   class="button button-secondary manual_feed_update">Run Manual Update</a></td>
                        </tr>
                        <tr class="prod_update_row">
                            <th>Update Progress</th>
                            <td>
                                <span class="update_percent">0%</span>

                                <div id="update_cont">
                                    <div id="update_progress"></div>
                                </div>
                                <span class="total_update"></span>

                                <div><span class="update_success">0</span> Updated - <span class="update_fail">0</span>
                                    Failed to Update
                                </div>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Save'); ?>
                    <input type="hidden" value="wp_man_save_feed" name="action"/>
                    <?php wp_nonce_field('wp_man_save_feed', $this->man_option_name . '_nonce', TRUE); ?>
                </form>
            </div>
            <?php
        }

        public function add_products() {
            ?>
            <div class="wrap">
                <?php
                if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'manual') {
                    wp_enqueue_media();
                    ?>
                    <h2>Affiliate Shop <a href="<?php print admin_url('admin.php?page=affiliate-shop/add-products'); ?>"
                                          class="add-new-h2">Add Product(s) from API</a></h2>
                    <h3>Add Product Manually</h3>
                    <form method="POST" id="wp_add_prod_manual" action="<?php echo admin_url('admin-post.php'); ?>">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <input type="text" placeholder="Product name" name="product_name" size="30" value=""
                                       id="title" spellcheck="true" autocomplete="off">
                            </div>
                        </div>
                        <table class="form-table">
                            <tr>
                                <th>Brand</th>
                                <td>
                                    <?php
                                    $arg = array(
                                        'show_option_none' => 'Select Brand',
                                        'orderby' => 'NAME',
                                        'order' => 'ASC',
                                        'hide_empty' => 0,
                                        'name' => 'brand',
                                        'taxonomy' => 'wp_aff_brands',
                                    );
                                    wp_dropdown_categories($arg);
                                    ?> <br>
                                    <input class="regular-text" type="text" name="product_brand_new"
                                           placeholder="Or type a new one">

                                    <p class="description">If adding a new category, none should be selected from the
                                        dropdown.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Price</th>
                                <td><input class="regular-text" type="text" name="product_price" placeholder="0.00"
                                           value="">

                                    <p class="description">&pound; sign not needed.</p></td>
                            </tr>
                            <tr>
                                <th>RRP</th>
                                <td><input class="regular-text" type="text" name="product_rrp" placeholder="0.00"
                                           value="<?php echo $meta['wp_aff_product_rrp'][0]; ?>">

                                    <p class="description">&pound; sign not needed.</p></td>
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
                                    <input id="upload_image_button" type="button" class="button button-secondary"
                                           value="Upload Image"/><input type="hidden" id="product_image"
                                           name="product_image">

                                    <p class="description">The image should be at least 300px x 300px.</p>
                                </td>

                            </tr>
                        </table>
                        <table class="form-table">
                            <tr class="form-table">
                                <td width="33%" valign="top">
                                    <div style="">
            <?php $categories = new Tag_Checklist('wp_aff_categories', 'all'); ?>

                                    </div>
                                </td>

                                <td width="33%" valign="top">
                                    <div style="">
            <?php $colours = new Tag_Checklist('wp_aff_colours', 'all'); ?>

                                    </div>
                                </td>
                                <td width="33%" valign="top">
            <?php $sizes = new Tag_Checklist('wp_aff_sizes', 'all'); ?>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" value="wp_aff_add_man_product" name="action"/>
                        <?php wp_nonce_field('wp_aff_add_man_product', '_wpnonce', FALSE); ?>
                        <?php $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button(); ?>
                    </form>
                <?php } else { ?>
                    <?php
                    if (!isset($_REQUEST['step'])) {
                        ?>
                        <h2>Affiliate Shop <a
                                href="<?php print admin_url('admin.php?page=affiliate-shop/add-products&action=manual'); ?>"
                                class="add-new-h2">Add Product Manually</a></h2>
                        <h3>Add Products from API</h3>
                        <form method="POST" id="wp_aff_add_cat" class=" searchtable"
                              action="<?php echo admin_url('admin-post.php'); ?>">

                            <table class="form-table">
                                <tr>
                                    <th>Search Query</th>
                                    <td>
                                        <input class="regular-text" type="text" name="q"
                                               value="<?php echo(isset($_GET['q']) ? $_GET['q'] : ''); ?>"
                                               id="wp_aff_search">
                                        <!-- <input type="checkbox" id="adv_search_check" name="adv_search_check" value="1"> <label>Advanced Search?</label>-->
                                        <p class="description">Search for products, such as <code>Black Dress</code>.</p>
                                    </td>
                                    <?php
                                    $i = 1;
                                    $api = new wpAffAPI();
                                    if (isset($_REQUEST['wp_aff_merch'])) {
                                        $merch = $_REQUEST['wp_aff_merch'];
                                    } else {
                                        $merch = NULL;
                                    }
                                    ?>
                                    <th>Affiliate</th>
                                    <td>
                                        <select name="wp_aff_api">
                                            <option <?php echo(isset($_REQUEST['api']) && $_REQUEST['api'] == 'all' ? 'selected' : ''); ?>
                                                value="all" selected>All
                                            </option>
                                            <?php
                                            $api->get_affiliates($_REQUEST['api']);
                                            ?>
                                        </select>
                                    </td>

                                    <th>Merchant</th>
                                    <td>
                                        <select name="wp_aff_merch">
                                            <option selected value="0">All Merchants</option>
                                            <?php
                                            $api->get_merchants((isset($_REQUEST['api']) ? $_REQUEST['api'] : 'all'), $merch);
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div id="advanced_search">
                                <h3>Advanced Search </h3>
                                <table class="form-table">
                                    <tr>
                                        <th>Include these terms</th>
                                        <td>
                                            <input class="regular-text" type="text" name="include"
                                                   value="<?php echo(isset($_GET['q']) ? $_GET['q'] : ''); ?>"
                                                   id="wp_aff_search">

                                            <p class="description">These terms will be included in the search.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ignore these terms</th>
                                        <td>
                                            <input class="regular-text" type="text" name="exclude"
                                                   value="<?php echo(isset($_GET['q']) ? $_GET['q'] : ''); ?>"
                                                   id="wp_aff_search">

                                            <p class="description">These terms will be ignored in the search.</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>


                            <input type="hidden" value="wp_aff_product_search" name="action"/>
                            <?php wp_nonce_field('wp_aff_product_search', '_wpnonce', FALSE); ?>
                            <?php $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']); ?>
                            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                <?php submit_button('Search'); ?>
                        </form>
                        <hr>

                        <div id="wp_aff_prod_list">
                            <?php
                            if (isset($_GET['q']) && $_GET['q'] != '' || isset($_GET['paged'])) {

                                $curr_api = (isset($_REQUEST['api']) ? $_REQUEST['api'] : 'all');
                                $starttime = microtime(true);
                                $table_data = $api->db_search($_GET['q'], $curr_api, $merch, 20, (isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1));
                                $endtime = microtime(true);
                                $duration = $endtime - $starttime;
                                //print_var( $table_data );
                                $ListProductSearch = new ListProductSearch($table_data);
                                $ListProductSearch->prepare_items();
                                echo '<p class="description">This query took ' . number_format($duration, 4) . ' seconds.</p>';
                            }

                            //print_var($_SESSION['product_data']);
                            ?>


                            <div class="container">
                                <div class="right_box">
                                    <div class="wp-box">
                                        <h3>Selected Products <a class="button-primary next_step"
                                                                 href="<?php echo admin_url('admin.php?page=' . $_REQUEST['page'] . (isset($_REQUEST['wp_aff_categories']) ? '&wp_aff_categories=' . $_REQUEST['wp_aff_categories'] : '') . '&step=2'); ?>">Next
                                                Step</a></h3>

                                        <div class="inside">
                                            <?php
                                            //echo $ListProductSearch->current_action();
                                            // print_r($_GET['product']);

                                            if (isset($ListProductSearch) && 'add' === $ListProductSearch->current_action()) {

                                                if (is_array($_GET['product'])) {
                                                    foreach ($_GET['product'] AS $prod) {
                                                        if (@!in_array($prod, $_SESSION['products']) || !isset($_SESSION['products'])) {
                                                            $_SESSION['products'][$prod] = $prod;
                                                        }
                                                    }
                                                } else {
                                                    if (@!in_array($_GET['product'], $_SESSION['products'])) {
                                                        $_SESSION['products'][$_GET['product']] = $_GET['product'];
                                                    }
                                                }
                                            } elseif ((isset($ListProductSearch) && 'clear-products' === $ListProductSearch->current_action()) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'clear-products')) {
                                                $_SESSION['products'] = '';
                                            } elseif (isset($ListProductSearch) && 'remove-product' === $ListProductSearch->current_action()) {
                                                if (isset($_GET['product'])) {
                                                    unset($_SESSION['products'][$_GET['product']]);
                                                }
                                            }

                                            if (isset($_SESSION['products']) && is_array($_SESSION['products']) && !empty($_SESSION['products'])) {

                                                //print_r($_SESSION['products']);

                                                $products = array_unique($_SESSION['products']);
                                                $i = 0;
                                                foreach ($products AS $key => $value) {
                                                    $curr_api = (isset($_REQUEST['api']) ? $_REQUEST['api'] : 'all');
                                                    if (isset($_REQUEST['q']) && !isset($_REQUEST['wp_aff_merch'])) {
                                                        $url = 'admin.php?page=' . $_REQUEST['page'] . '&q=' . $_REQUEST['q'] . '&action=remove-product&product=' . $key . '&api=' . $curr_api;
                                                    } elseif (isset($_REQUEST['q']) && isset($_REQUEST['wp_aff_merch'])) {
                                                        $url = 'admin.php?page=' . $_REQUEST['page'] . '&q=' . $_REQUEST['q'] . '&wp_aff_merch=' . $_REQUEST['wp_aff_merch'] . '&action=remove-product&product=' . $key . '&api=' . $curr_api;
                                                    } else {
                                                        $url = 'admin.php?page=' . $_REQUEST['page'] . '&action=remove-product&product=' . $key . '&api=' . $curr_api;
                                                    }

                                                    echo '<table ' . ($i % 2 ? 'class="alt"' : '') . '>
													<tr>
														<td rowspan="2">
															<img src="' . $_SESSION['product_data']['ID-' . $value]['img'] . '" width="75" height="75">
														</td>
														<td>' . stripslashes(substr($_SESSION['product_data']['ID-' . $value]['title'], 0, 40)) . '...</td>
													</tr>
													<tr>
														<td>' . $_SESSION['product_data']['ID-' . $value]['brand'] . '</td>
													</tr>
													<tr>
														<td><a class="button-secondary" href="' . admin_url($url) . '">Remove</a></td>
														<td>&pound;' . $_SESSION['product_data']['ID-' . $value]['price'] . '</td>
													</tr>
												   </table>';
                                                    $i++;
                                                }
                                            } else {
                                                echo '<p>You have selected no products yet.</p>';
                                            }
                                            ?>
                                        </div>
                                        <div class="actions">
                                            <?php
                                            if (isset($_REQUEST['q']) && !isset($_REQUEST['wp_aff_merch'])) {
                                                $url = 'admin.php?page=' . $_REQUEST['page'] . '&q=' . $_REQUEST['q'] . '&action=clear-products&api=' . $curr_api;
                                            } elseif (isset($_REQUEST['q']) && isset($_REQUEST['wp_aff_merch'])) {
                                                $url = 'admin.php?page=' . $_REQUEST['page'] . '&q=' . $_REQUEST['q'] . '&wp_aff_merch=' . $_REQUEST['wp_aff_merch'] . '&action=clear-products&api=' . $curr_api;
                                            } else {
                                                $url = 'admin.php?page=' . $_REQUEST['page'] . '&action=clear-products&api=' . $curr_api;
                                            }
                                            ?>
                                            <a class="button-secondary" href="<?php echo admin_url($url); ?>">Clear All</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="left_box">
                <?php if (isset($_GET['q']) && $_GET['q'] != '' || isset($_GET['paged'])) { ?>
                                        <form id="product-table" method="get">
                                            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                                            <input type="hidden" name="page"
                                                   value="<?php echo urldecode($_REQUEST['page']); ?>"/>
                                            <input type="hidden" name="api"
                                                   value="<?php echo $curr_api ?>"/>
                                            <input type="hidden" name="q" value="<?php echo $_REQUEST['q'] ?>"/>
                                            <input type="hidden" name="wp_aff_merch"
                                                   value="<?php echo @$_REQUEST['wp_aff_merch'] ?>"/>
                                                   <?php
                                                   if (isset($_REQUEST['wp_aff_categories'])) {
                                                       echo '<input type="hidden" name="wp_aff_categories" value="' . $_REQUEST['wp_aff_categories'] . '" />';
                                                   }
                                                   ?>
                                            <!-- Now we can render the completed list table -->
                    <?php $ListProductSearch->display(); ?>
                                        </form>

                <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    } elseif (isset($_REQUEST['step']) && $_REQUEST['step'] == 2) {
                        if (isset($_REQUEST['action']) && 'remove-product' === $_REQUEST['action']) {
                            if (isset($_GET['product'])) {
                                unset($_SESSION['products'][$_GET['product']]);
                            }
                        }
                        ?>
                        <?php
                        $products = array_unique($_SESSION['products']);
                        ?>
                        <form method="POST" id="wp_aff_add_products" class="searchtable"
                              action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" value="wp_aff_add_products" name="action"/>
                            <?php wp_nonce_field('wp_aff_add_products', '_wpnonce', FALSE); ?>
                <?php $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']); ?>
                            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">

                            <div id="poststuff">
                                <div id="post-body" class="metabox-holder columns-1">

                                    <div id="postbox-container-2" class="postbox-container">
                                        <div class="postbox">
                                            <h3 class="hndle "><span>Apply to all products</span></h3>

                                            <div class="inside">
                                                <table class="form-table">


                                                    <tr>
                                                        <th>Product Tagging</th>
                                                        <td>
                                                            <div style="width: 33%; float: left; ">
                                                                <?php
                                                                if (isset($_REQUEST['wp_aff_categories'])) {
                                                                    $categories = new Tag_Checklist('wp_aff_categories', 'all', $_REQUEST['wp_aff_categories']);
                                                                } else {
                                                                    $categories = new Tag_Checklist('wp_aff_categories', 'all');
                                                                }
                                                                ?>
                                                            </div>
                                                            <div style="width: 33%; float: left;">
                <?php $categories = new Tag_Checklist('wp_aff_colours', 'all'); ?>
                                                            </div>
                                                            <div style="width: 33%; float: left; ">
                <?php $categories = new Tag_Checklist('wp_aff_sizes', 'all'); ?>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th>Confirm</th>
                                                        <td><input type="checkbox" class="add_product_confirm"> <i>Tick to
                                                                confirm you have updated all details for the products
                                                                below.</i></td>
                                                    </tr>
                                                    <tr>
                                                        <th></th>
                                                        <td><?php submit_button('Add All Products', 'primary', '', false, array('disabled' => 'disabled', 'id' => 'add_products_submit')); ?></td>
                                                    </tr>
                                                </table>
                                                <p></p>

                                            </div>

                                        </div>
                                        <?php
                                        if (!empty($_SESSION['products'])) {
                                            ?>
                                            <?php
                                            if (!is_array($_SESSION['products'])) {
                                                $test = $productArray->oProduct;
                                                $_SESSION['product_data'] = (array) $_SESSION['product_data'];
                                            }
                                            $i = 0;
                                            $api = new wpAffAPI();
                                            //$brands = $api->linkshare_merchants();
                                            foreach ($products AS $id) {
                                                $product = $_SESSION['product_data']['ID-' . $id];
                                                ?>
                                                <div class="postbox" id="product-<?php echo $product['ID']; ?>">
                                                    <h3 class=" ">
                                                        <span><?php echo ucwords(stripslashes(($product['title']))); ?>
                                                            (ID:<?php echo $product['ID']; ?>
                                                            )<br>Brand: <?php echo ucwords($product['brand']); ?><br><a
                                                                class="button" target="_blank"
                                                                href="<?php echo $product['link']; ?>">Visit URL</a></span>
                                                        <a href="#" class="delete button button-secondary remove-product"
                                                           rel="<?php echo $product['ID']; ?>">Remove Product</a></h3>

                                                    <div class="inside">
                                                        <input type="hidden" value="<?php echo $product['link']; ?>"
                                                               name="product_link[<?php echo $i; ?>]">
                                                        <input type="hidden" value="<?php echo $product['aff']; ?>"
                                                               name="product_aff[<?php echo $i; ?>]">
                                                        <input type="hidden" value="<?php echo $product['ID']; ?>"
                                                               name="product_id[<?php echo $i; ?>]">
                                                        <input type="hidden" value="0"
                                                               id="product-skip-<?php echo $product['ID']; ?>"
                                                               name="product_skip[<?php echo $i; ?>]">
                                                               <?php
                                                               //print_var($brands);
                                                               //$brand = $brands['ID-'.$product['brand']]['name'];
                                                               ?>
                                                        <input type="hidden"
                                                               value="<?php echo ucwords($product['brand']); ?>"
                                                               name="product_brand[<?php echo $i; ?>]">
                                                        <table class="widefat productList">
                                                            <tbody>
                                                                <tr>
                                                                    <th width="200"></th>
                                                                    <th>Product Name</th>
                                                                    <th>Price</th>
                                                                    <th>RRP</th>
                                                                </tr>
                                                                <tr>
                                                                    <td rowspan="6"><img style="width: 275px; height: auto;"
                                                                                         src="<?php echo $product['img']; ?>"><input
                                                                                         type="hidden" value="<?php echo $product['img']; ?>"
                                                                                         name="product_image[<?php echo $i; ?>]"></td>

                                                                    <td>
                                                                        <input class="large-text" type="text"
                                                                               name="product_name[<?php echo $i; ?>]"
                                                                               placeholder=""
                                                                               value="<?php echo ucwords(stripslashes(($product['title']))); ?>"
                                                                               id="">
                                                                    </td>
                                                                    <td>
                                                                        <input class="large-text" type="text"
                                                                               name="product_price[<?php echo $i; ?>]"
                                                                               placeholder=""
                                                                               value="<?php echo $product['price']; ?>" id="">
                                                                    </td>
                                                                    <td>
                                                                        <input class="large-text" type="text"
                                                                               name="product_rrp[<?php echo $i; ?>]"
                                                                               placeholder=""
                                                                               value="<?php echo $product['rrp']; ?>" id="">
                                                                    </td>

                                                                </tr>
                                                                <tr>
                                                                    <td width="33%">
                                                                        <div style="">
                        <?php $categories = new Tag_Checklist('wp_aff_categories', $i); ?>

                                                                        </div>
                                                                    </td>

                                                                    <td width="33%">
                                                                        <div style="">
                        <?php $colours = new Tag_Checklist('wp_aff_colours', $i); ?>

                                                                        </div>
                                                                    </td>
                                                                    <td width="33%">
                                                                        <div style="">
                        <?php $sizes = new Tag_Checklist('wp_aff_sizes', $i); ?>

                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th colspan="2">Description</th>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3">

                                                                        <textarea class="large-text" rows="3" type="text"
                                                                                  name="product_desc[<?php echo $i; ?>]"
                                                                                  placeholder=""><?php echo stripslashes(($product['desc'])); ?></textarea>
                                                                    </td>

                                                                </tr>


                                                            </tbody>

                                                        </table>

                                                    </div>

                                                </div>
                                                <?php
                                                $i++;
                                            }
                                        } else {
                                            ?>
                                            <div class="postbox">
                                                <p>No products, <a
                                                        href="<?php echo admin_url('admin.php?page=affiliate-shop/add-products'); ?>">please
                                                        go back and add more</a></p>
                                            </div>
                <?php } ?>
                                    </div>
                                </div>
                        </form>
                        <?php
                    }
                }
                ?>
            </div>
            <?php
        }

        public function settings_page() {
            $redirect = urlencode(remove_query_arg('msg', $_SERVER['REQUEST_URI']));
            $redirect = urlencode($_SERVER['REQUEST_URI']);
            $option = $this->get_option();
            //print_var( $option );
            ?>
            <div class="wrap">
                <h2>Settings</h2>

                <h2 class="nav-tab-wrapper">
                    <a class="nav-tab <?php echo(!isset($_REQUEST['tab']) || $_REQUEST['tab'] == 0 ? 'nav-tab-active' : ''); ?>"
                       href="<?php echo add_query_arg('tab', 0, $_SERVER['REQUEST_URI']); ?>">General Settings</a>
                    <a class="nav-tab <?php echo(isset($_REQUEST['tab']) && $_REQUEST['tab'] == 1 ? 'nav-tab-active' : ''); ?>"
                       href="<?php echo add_query_arg('tab', 1, $_SERVER['REQUEST_URI']); ?>">Titles and Desc's</a>
                    <a class="nav-tab <?php echo(isset($_REQUEST['tab']) && $_REQUEST['tab'] == 2 ? 'nav-tab-active' : ''); ?>"
                       href="<?php echo add_query_arg('tab', 2, $_SERVER['REQUEST_URI']); ?>">Update Feeds</a>
                </h2>

                <form method="POST" id="wp_aff_prod_search" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php if (!isset($_REQUEST['tab']) || $_REQUEST['tab'] == 0) { ?>
                        <table class="form-table">
                            <tr>
                                <th>Affiliate Window API Key</th>
                                <td>
                                    <input class="regular-text" type="text" name="<?php echo $this->option_name; ?>[awin]"
                                           value="<?php echo $this->option['awin']; ?>"
                                           id="<?php echo $this->option_name; ?>[awin]">

                                    <p class="description">Please enter your Affiliate Window product search API key.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Linkshare API Key</th>
                                <td>
                                    <input class="regular-text" type="text"
                                           name="<?php echo $this->option_name; ?>[linkshare]"
                                           value="<?php echo(isset($this->option['linkshare']) ? $this->option['linkshare'] : ''); ?>"
                                           id="<?php echo $this->option_name; ?>[linkshare]">

                                    <p class="description">Please enter your Affiliate Window product search API key.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Shop Page</th>
                                <td>
                                    <?php
                                    wp_dropdown_pages(
                                            array(
                                                'name' => $this->option_name . '[shop_page]',
                                                'echo' => 1,
                                                'show_option_none' => __('&mdash; Select &mdash;'),
                                                'option_none_value' => '0',
                                                'selected' => (isset($this->option['shop_page']) ? $this->option['shop_page'] : '')
                                            )
                                    );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>'NEW' Product for how many days?</th>
                                <td>
                                    <input class="text" type="text" name="<?php echo $this->option_name; ?>[new_days]"
                                           value="<?php echo $this->option['new_days']; ?>"
                                           id="<?php echo $this->option_name; ?>[new_days]">
                                </td>
                            </tr>
                        </table>

                        <?php
                        $api = new wpAffAPI();
                        $api->db_search('black dress');
                        ?>
        <?php } elseif (!isset($_REQUEST['tab']) || $_REQUEST['tab'] == 1) {
            ?>
                        <table class="form-table">
                            <tr>
                                <th>Shop Home Meta Title</th>
                                <td>
                                    <input class="regular-text" type="text"
                                           name="<?php echo $this->option_name; ?>[faceted][home][meta_title]"
                                           value="<?php echo(isset($option['faceted']['home']['meta_title']) ? $option['faceted']['home']['meta_title'] : ''); ?>"
                                           id="<?php echo $this->option_name; ?>[faceted][home][meta_title]">
                                </td>
                            </tr>
                            <tr>
                                <th>Shop Home Meta Description</th>
                                <td>
                                    <textarea class="regular-text" type="text"
                                              name="<?php echo $this->option_name; ?>[faceted][home][meta_desc]"
                                              id="<?php echo $this->option_name; ?>[faceted][home][meta_desc]"><?php echo(isset($option['faceted']['home']['meta_desc']) ? $option['faceted']['home']['meta_desc'] : ''); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th>Shop Home Title</th>
                                <td>
                                    <input class="regular-text" type="text"
                                           name="<?php echo $this->option_name; ?>[faceted][home][title]"
                                           value="<?php echo(isset($option['faceted']['home']['title']) ? $option['faceted']['home']['title'] : ''); ?>"
                                           id="<?php echo $this->option_name; ?>[faceted][home][title]">
                                </td>
                            </tr>
                            <tr>
                                <th>Shop Home Intro</th>
                                <td>
            <?php wp_editor((isset($option['faceted']['home']['intro']) ? $option['faceted']['home']['intro'] : ''), 'home_intro', array('textarea_name' => $this->option_name . '[faceted][home][intro]', 'textarea_rows' => 5)); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>New In Title</th>
                                <td>
                                    <input class="regular-text" type="text"
                                           name="<?php echo $this->option_name; ?>[faceted][newin][title]"
                                           value="<?php echo(isset($option['faceted']['newin']['title']) ? $option['faceted']['newin']['title'] : ''); ?>"
                                           id="<?php echo $this->option_name; ?>[faceted][newin][title]">
                                </td>
                            </tr>
                            <tr>
                                <th>New In Intro</th>
                                <td>
            <?php wp_editor((isset($option['faceted']['newin']['intro']) ? $option['faceted']['newin']['intro'] : ''), 'newin_intro', array('textarea_name' => $this->option_name . '[faceted][newin][intro]', 'textarea_rows' => 5)); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Sale Title</th>
                                <td>
                                    <input class="regular-text" type="text"
                                           name="<?php echo $this->option_name; ?>[faceted][sale][title]"
                                           value="<?php echo(isset($option['faceted']['sale']['title']) ? $option['faceted']['sale']['title'] : ''); ?>"
                                           id="<?php echo $this->option_name; ?>[faceted][sale][title]">
                                </td>
                            </tr>
                            <tr>
                                <th>Sale Intro</th>
                                <td>
            <?php wp_editor((isset($option['faceted']['sale']['intro']) ? $option['faceted']['sale']['intro'] : ''), 'sale_intro', array('textarea_name' => $this->option_name . '[faceted][sale][intro]', 'textarea_rows' => 5)); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Top Picks Title</th>
                                <td>
                                    <input class="regular-text" type="text"
                                           name="<?php echo $this->option_name; ?>[faceted][picks][title]"
                                           value="<?php echo(isset($option['faceted']['picks']['title']) ? $option['faceted']['picks']['title'] : ''); ?>"
                                           id="<?php echo $this->option_name; ?>[faceted][packs][title]">
                                </td>
                            </tr>
                            <tr>
                                <th>Top Picks Intro</th>
                                <td>
            <?php wp_editor((isset($option['faceted']['picks']['intro']) ? $option['faceted']['picks']['intro'] : ''), 'picks_intro', array('textarea_name' => $this->option_name . '[faceted][picks][intro]', 'textarea_rows' => 5)); ?>
                                </td>
                            </tr>
                        </table>
        <?php } elseif (!isset($_REQUEST['tab']) || $_REQUEST['tab'] == 2) { ?>
                        <h3>Update Feed</h3>
                        <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                            <table class="form-table">
                                <tr>
                                    <th>Manual Update</th>
                                    <td><a href="<?php echo $_SERVER['REQUEST_URI']; ?>"
                                           class="button button-secondary manual_feed_update">Run Manual Update</a></td>
                                </tr>
                                <tr class="prod_update_row">
                                    <th>Update Progress</th>
                                    <td>
                                        <span class="update_percent">0%</span>

                                        <div id="update_cont">
                                            <div id="update_progress"></div>
                                        </div>
                                        <span class="total_update"></span>

                                        <div><span class="update_success">0</span> Updated - <span
                                                class="update_fail">0</span> Failed to Update
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </form>


                    <?php } ?>
                    <?php submit_button('Save'); ?>
                    <input type="hidden" value="wp_aff_save_api" name="action"/>
        <?php wp_nonce_field('wp_aff_save_api', $this->option_name . '_nonce', FALSE); ?>
                    <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                </form>
            </div>
            <?php
            //$this->ajax_update_get_count();
            //$test = new WordPress_Affiliate_Shop_Webgains;
            //print_var( $test->merchants() );
            ?>
            <?php
        }

        public function list_brands() {
            $CategoryTable = new WP_Terms_List_Tables(array('taxonomy' => 'wp_aff_brands'));
            $CategoryTable->prepare_items();
            ?>
            <div class="wrap">
                <h2>Affiliate Shop</h2>

                <h3>Shop Brands <a href="<?php print admin_url('admin.php?page=affiliate-shop&action=add-category'); ?>"
                                   class="add-new-h2">Add Brand</a></h3>

                <form id="category-table" method="get">
                    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                    <!-- Now we can render the completed list table -->
            <?php $CategoryTable->display() ?>
                </form>
            </div>
            <?php
        }

        public function colours_page() {
            $CategoryTable = new WP_Terms_List_Tables(array('taxonomy' => 'wp_aff_colours'));
            $CategoryTable->prepare_items();
            ?>
            <div class="wrap">
                <h2>Affiliate Shop</h2>
        <?php if (!isset($_REQUEST['action']) || $_REQUEST['action'] == 'delete') { ?>
                    <h3>Shop Colours <a
                            href="<?php print admin_url('admin.php?page=affiliate-shop/colours&action=add-colours'); ?>"
                            class="add-new-h2">Add Colour</a></h3>
                    <form id="category-table" method="get">
                        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                        <!-- Now we can render the completed list table -->
                    <?php $CategoryTable->display() ?>
                    </form>
        <?php } elseif ($_GET['action'] == 'add-colours') { ?>
                    <h3>Add Shop Colour</h3>
                    <form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                        <table class="form-table">
                            <tr class="form-field">
                                <th>Colour Name</th>
                                <td>
                                    <input type="hidden" value="" name="wp_term_id">
                                    <input class="regular-text" type="text" name="wp_term_name" placeholder="Category Name"
                                           value="">

                                    <p class="description">The name is how it appears on your site.</p>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th>Colour Slug</th>
                                <td>
                                    <input class="regular-text" type="text" name="wp_term_slug" placeholder="Category Slug"
                                           value="">

                                    <p class="description">The "slug" is the URL-friendly version of the name. It is usually
                                        all lowercase and contains only letters, numbers, and hyphens.</p>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th>Colour</th>
                                <td>
                                    <input class="regular-text" type="color" name="wp_term_colour" value=""
                                           style="width: 50px; height: 50px;">
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th>Or Use Custom CSS?</th>
                                <td>
                                    <textarea rows="10" class="regular-text" name="wp_term_colour_css"></textarea>

                                    <p class="description">Use <a target="_blank"
                                                                  href="http://www.colorzilla.com/gradient-editor/">Gradient
                                            Generator</a> and copy and paste the code.</p>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" value="wp_aff_add_colours" name="action"/>
                        <?php $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']); ?>
                        <?php wp_nonce_field('wp_aff_add_colours', '_wpnonce', FALSE); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button('Add Colour'); ?>
                    </form>
                <?php } elseif ($_GET['action'] == 'edit' && isset($_GET['wp_aff_colours'])) { ?>
                    <?php
                    $term = get_term($_GET['wp_aff_colours'], 'wp_aff_colours', 'OBJECT');
                    $colour_code = get_metadata('wp_aff_colours', $term->term_id, 'colour_code', true);
                    $colour_code_css = get_metadata('wp_aff_colours', $term->term_id, 'colour_code_css', true);
                    ?>
                    <h3>Edit Shop Colour</h3>
                    <form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                        <table class="form-table">
                            <tr class="form-field">
                                <th>Colour Name</th>
                                <td>
                                    <input type="hidden" value="<?php echo $term->term_id; ?>" name="wp_term_id">
                                    <input class="regular-text" type="text" name="wp_term_name" placeholder="Category Name"
                                           value="<?php echo $term->name; ?>">

                                    <p class="description">The name is how it appears on your site.</p>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th>Colour Slug</th>
                                <td>
                                    <input class="regular-text" type="text" name="wp_term_slug" placeholder="Category Slug"
                                           value="<?php echo $term->slug; ?>">

                                    <p class="description">The "slug" is the URL-friendly version of the name. It is usually
                                        all lowercase and contains only letters, numbers, and hyphens.</p>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th>Colour</th>
                                <td>
                                    <input class="regular-text" type="color" name="wp_term_colour"
                                           value="<?php echo $colour_code; ?>" style="width: 50px; height: 50px;">
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th>Or Use Custom CSS?</th>
                                <td>
                                    <textarea rows="10" class="regular-text"
                                              name="wp_term_colour_css"><?php echo $colour_code_css; ?></textarea>

                                    <p class="description">Use <a target="_blank"
                                                                  href="http://www.colorzilla.com/gradient-editor/">Gradient
                                            Generator</a> and copy and paste the code.</p>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" value="wp_aff_edit_colours" name="action"/>
                        <?php $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']); ?>
                        <?php wp_nonce_field('wp_aff_edit_colours', '_wpnonce', FALSE); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button('Edit Colour'); ?>
                    </form>
            <?php } ?>
            </div>
            <?php
        }

        public function sizes_page() {
            ?>
            <div class="wrap">
                <h2>Affiliate Shop</h2>
                <?php
                if (!isset($_REQUEST['action']) || $_REQUEST['action'] == 'delete') {
                    $CategoryTable = new WP_Terms_List_Tables(array('taxonomy' => 'wp_aff_sizes'));
                    $CategoryTable->prepare_items();
                    ?>
                    <h3>Shop Sizes <a
                            href="<?php print admin_url('admin.php?page=affiliate-shop/sizes&action=add-sizes'); ?>"
                            class="add-new-h2">Add Size</a></h3>
                    <form id="category-table" method="get">
                        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                        <!-- Now we can render the completed list table -->
                    <?php $CategoryTable->display() ?>
                    </form>
        <?php } elseif ($_GET['action'] == 'add-sizes') { ?>
                    <h3>Add Shop Size</h3>
                    <form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                        <table class="form-table">
                            <tr class="form-field">
                                <th>Size Name</th>
                                <td>
                                    <input type="hidden" value="" name="wp_term_id">
                                    <input class="regular-text" type="text" name="wp_term_name" placeholder="Category Name"
                                           value="">

                                    <p class="description">The name is how it appears on your site.</p>
                                </td>
                            </tr>
                            <tr class="form-field">
                                <th>Size Slug</th>
                                <td>
                                    <input class="regular-text" type="text" name="wp_term_slug" placeholder="Category Slug"
                                           value="">

                                    <p class="description">The "slug" is the URL-friendly version of the name. It is usually
                                        all lowercase and contains only letters, numbers, and hyphens.</p>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" value="wp_aff_add_sizes" name="action"/>
                        <?php $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']); ?>
                        <?php wp_nonce_field('wp_aff_add_sizes', '_wpnonce', FALSE); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button('Add Size'); ?>
                    </form>
            <?php } ?>
            </div>
            <?php
        }

        /**
         * Place code for admin page here
         */
        public function admin_page() {
            ?>
            <div class="wrap">
                <h2>Affiliate Shop</h2>
                <?php if (!isset($_GET['action']) || $_GET['action'] == 'delete'): ?>

                    <?php
                    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['product'])) {
                        $products = array();
                        if (!is_array($_GET['product'])) {
                            $products[] = $_GET['product'];
                        } else {
                            $products = $_GET['product'];
                        }
                        foreach ($products as $product) {
                            wp_trash_post($product);
                        }
                        if (isset($_GET['referrer'])) {
                            wp_redirect($_GET['referrer']);
                        } else {
                            wp_redirect($_GET['_wp_http_referer']);
                        }
                    }
                    ?>

                    <?php
                    $CategoryTable = new WP_Terms_List_Tables(array('taxonomy' => 'wp_aff_categories'));
                    $CategoryTable->prepare_items();
                    ?>
                    <h3>Shop Categories <a
                            href="<?php print admin_url('admin.php?page=affiliate-shop&action=add-category'); ?>"
                            class="add-new-h2">Add Category</a></h3>
                    <form id="category-table" method="get">
                        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                        <!-- Now we can render the completed list table -->
                    <?php $CategoryTable->display() ?>
                    </form>
                <?php else: // Else if(!isset($_GET['action'])) ?>
                    <?php if ($_GET['action'] == 'view') : ?>
                        <?php
                        $ProductTable = new ProductTable();
                        $ProductTable->prepare_items();
                        $term = get_term($_GET['wp_aff_categories'], 'wp_aff_categories');
                        ?>
                        <h3><?php echo ucwords($term->name); ?> Products <a
                                href="<?php print admin_url('admin.php?page=affiliate-shop/add-products&wp_aff_categories=' . $_REQUEST['wp_aff_categories']); ?>"
                                class="add-new-h2">Add Product(s)</a></h3>
                        <form id="product-table" method="get">
                            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                            <!-- Now we can render the completed list table -->
                        <?php $ProductTable->display(); ?>
                        </form>
                        <?php
                    elseif ($_GET['action'] == 'add-category') :
                        $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']);
                        ?>
                        <h3>Add Shop Category</h3>
                        <form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                            <table class="form-table">
                                <tr class="form-field">
                                    <th>Category Name</th>
                                    <td>
                                        <input class="regular-text" type="text" name="wp_term_name"
                                               placeholder="Category Name">

                                        <p class="description">The name is how it appears on your site.</p>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>Category Slug</th>
                                    <td>
                                        <input class="regular-text" type="text" name="wp_term_slug"
                                               placeholder="Category Slug">

                                        <p class="description">The "slug" is the URL-friendly version of the name. It is
                                            usually all lowercase and contains only letters, numbers, and hyphens.</p>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>Parent Category</th>
                                    <td>
                                        <?php
                                        wp_dropdown_categories(array('show_count' => 0,
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
                                        wp_dropdown_categories(array('show_count' => 0,
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
                                        $settings = array(
                                            'textarea_name' => 'wp_term_desc',
                                        );
                                        wp_editor('', 'wp_term_desc', $settings);
                                        ?>

                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>SEO Title</th>
                                    <td>
                                        <input class="regular-text" type="text" name="wp_seo_title" placeholder="SEO Title"
                                               value="">

                                        <p class="description">Keep below 70 characters</p>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>SEO Description</th>
                                    <td>
                                        <textarea rows="3" name="wp_seo_desc" id="wp_seo_desc"></textarea>
                                    </td>
                                </tr>
                            </table>
                            <input type="hidden" value="wp_aff_add_category" name="action"/>
                            <?php wp_nonce_field('wp_aff_add_category', '_wpnonce', FALSE); ?>
                            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                        <?php submit_button('Add Category'); ?>
                        </form>

                    <?php elseif ($_GET['action'] == 'edit' && isset($_GET['wp_aff_categories'])) : ?>
                        <?php
                        $term = get_term($_GET['wp_aff_categories'], 'wp_aff_categories', 'OBJECT');
                        $seo_title = get_metadata('wp_aff_categories', $term->term_id, 'aff_seo_title', true);
                        $seo_desc = get_metadata('wp_aff_categories', $term->term_id, 'aff_seo_desc', true);
                        $seo_canonical = get_metadata('wp_aff_categories', $term->term_id, 'aff_seo_canonical', true);
                        ?>

                        <h3>Edit Shop Category</h3>
                        <form method="POST" id="wp_aff_add_cat" action="<?php echo admin_url('admin-post.php'); ?>">
                            <table class="form-table">
                                <tr class="form-field">
                                    <th>Category Name</th>
                                    <td>
                                        <input type="hidden" value="<?php echo $term->term_id; ?>" name="wp_term_id">
                                        <input class="regular-text" type="text" name="wp_term_name"
                                               placeholder="Category Name" value="<?php echo $term->name; ?>">

                                        <p class="description">The name is how it appears on your site.</p>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>Category Slug</th>
                                    <td>
                                        <input class="regular-text" type="text" name="wp_term_slug"
                                               placeholder="Category Slug" value="<?php echo $term->slug; ?>">

                                        <p class="description">The "slug" is the URL-friendly version of the name. It is
                                            usually all lowercase and contains only letters, numbers, and hyphens.</p>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>Parent Category</th>
                                    <td>
                                        <?php
                                        wp_dropdown_categories(array('show_count' => 0,
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
                                        if ($term->term_group > 1) {
                                            $selected_alias = $term->term_group;
                                        } else {
                                            $selected_alias = '';
                                        }
                                        wp_dropdown_categories(array('show_count' => 0,
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
                                        $settings = array(
                                            'textarea_name' => 'wp_term_desc',
                                            'textarea_rows' => 5
                                        );
                                        wp_editor(htmlspecialchars_decode($term->description), 'wp_term_desc', $settings);
                                        ?>

                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>SEO Title</th>
                                    <td>
                                        <input class="regular-text" type="text" name="wp_seo_title" placeholder="SEO Title"
                                               value="<?php echo htmlspecialchars_decode($seo_title); ?>">

                                        <p class="description">Keep below 70 characters</p>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>SEO Description</th>
                                    <td>
                                        <textarea rows="3" name="wp_seo_desc"
                                                  id="wp_seo_desc"><?php echo htmlspecialchars_decode($seo_desc); ?></textarea>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                    <th>SEO Canonical</th>
                                    <td>
                                        <input class="regular-text" type="text" name="wp_seo_canonical"
                                               placeholder="http://"
                                               value="<?php echo htmlspecialchars_decode($seo_canonical); ?>">

                                        <p class="description">Leave blank for default</p>
                                    </td>
                                </tr>
                            </table>
                            <input type="hidden" value="wp_aff_edit_category" name="action"/>
                            <?php $redirect = remove_query_arg('msg', wp_get_referer()); ?>
                            <?php wp_nonce_field('wp_aff_edit_category', '_wpnonce', FALSE); ?>
                            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                        <?php submit_button('Edit Category'); ?>
                        </form>
                    <?php endif; // End if($_GET['action'] == 'view') ?>
            <?php endif; //End if(!isset($_GET['action']))
            ?>
            </div>
            <?php
        }

        public function wp_aff_add_man_product() {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_add_man_product'))
                die('Invalid nonce.' . var_export($_POST, true));
            print_var($_POST);
            if (isset($_POST['product_brand_new']) && $_POST['product_brand_new'] != '') {
                $brand = wp_insert_term($_POST['product_brand_new'], 'wp_aff_brands');
            } else {
                $brand = $_POST['brand'];
            }

            $my_post = array(
                'post_title' => $_POST['product_name'],
                'post_status' => 'publish',
                'post_type' => 'wp_aff_products',
                'tax_input' => array(
                    'wp_aff_categories' => $_POST['wp_aff_categories']['all'],
                    'wp_aff_sizes' => $_POST['wp_aff_sizes']['all'],
                    'wp_aff_colours' => $_POST['wp_aff_colours']['all'],
                    'wp_aff_brands' => $brand
                )
            );

            // Insert the post into the database
            print_var($my_post);

            $insID = wp_insert_post($my_post);
            add_post_meta($insID, 'wp_aff_product_link', $_POST['product_url'], true);
            add_post_meta($insID, 'wp_aff_product_price', $_POST['product_price'], true);
            add_post_meta($insID, 'wp_aff_product_rrp', $_POST['product_rrp'], true);
            if ($_POST['product_price'] < $_POST['product_rrp']) {
                add_post_meta($insID, 'wp_aff_product_sale', 1, true);
            } else {
                add_post_meta($insID, 'wp_aff_product_sale', 0, true);
            }
            //add_post_meta($insID, 'wp_aff_product_brand', , true);
            add_post_meta($insID, 'wp_aff_product_desc', $_POST['product_desc'], true);
            add_post_meta($insID, 'wp_aff_product_image', $_POST['product_image'], true);
            add_post_meta($insID, 'wp_aff_product_manual', 1, true);
            $url = add_query_arg('msg', 1, $_POST['_wp_http_referer']);
            wp_safe_redirect($url);
        }

        public function wp_aff_edit_man_product() {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'wp_aff_edit_man_product'))
                die('Invalid nonce.' . var_export($_POST, true));
            print_var($_POST);
            if (isset($_POST['product_brand_new']) && $_POST['product_brand_new'] != '') {
                $brand = wp_insert_term($_POST['product_brand_new'], 'wp_aff_brands');
            } else {
                $brand = $_POST['brand'];
            }

            $insID = $_POST['post_id'];

            $my_post = array(
                'ID' => $_POST['post_id'],
                'post_title' => $_POST['product_name'],
                'post_status' => 'publish',
                'post_type' => 'wp_aff_products',
                'tax_input' => array(
                    'wp_aff_categories' => $_POST['wp_aff_categories']['all'],
                    'wp_aff_sizes' => $_POST['wp_aff_sizes']['all'],
                    'wp_aff_colours' => $_POST['wp_aff_colours']['all'],
                    'wp_aff_brands' => $brand
                )
            );

            // Insert the post into the database
            print_var($my_post);

            wp_update_post($my_post);

            update_post_meta($insID, 'wp_aff_product_link', $_POST['product_url']);
            update_post_meta($insID, 'wp_aff_product_price', $_POST['product_price']);
            update_post_meta($insID, 'wp_aff_product_rrp', $_POST['product_rrp']);
            if ($_POST['product_price'] < $_POST['product_rrp']) {
                update_post_meta($insID, 'wp_aff_product_sale', 1);
            } else {
                update_post_meta($insID, 'wp_aff_product_sale', 0);
            }
            update_post_meta($insID, 'wp_aff_product_desc', $_POST['product_desc']);
            update_post_meta($insID, 'wp_aff_product_image', $_POST['product_image']);
            $url = add_query_arg('msg', 1, $_POST['_wp_http_referer']);
            wp_safe_redirect($url);
        }

        public function products() {
            ?>
            <div class="wrap">
                <h2>Affiliate Shop</h2>
                <?php if (!isset($_REQUEST['action']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete')) { ?>
                    <?php
                    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['product'])) {
                        $products = array();
                        if (!is_array($_GET['product'])) {
                            $products[] = $_GET['product'];
                        } else {
                            $products = $_GET['product'];
                        }
                        foreach ($products as $product) {
                            wp_trash_post($product);
                        }
                        if (isset($_GET['referrer'])) {
                            wp_redirect($_GET['referrer']);
                        } else {
                            wp_redirect($_GET['_wp_http_referer']);
                        }
                    }
                    ?>
                    <h3>Products</h3>
                    <?php
                    $ProductTable = new AllProductTable();
                    $ProductTable->prepare_items();
                    ?>
                    <form id="category-table" method="get">
                        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                        <!-- Now we can render the completed list table -->
                    <?php $ProductTable->display() ?>
                    </form>
                    <?php
                } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
                    wp_enqueue_media();
                    $ID = $_REQUEST['product'];
                    $meta = get_post_meta($ID);
                    $brands = wp_get_post_terms($ID, 'wp_aff_brands');
                    //print_var($meta);
                    ?>
                    <h3>Edit Product</h3>
                    <form method="POST" id="wp_add_prod_manual" action="<?php echo admin_url('admin-post.php'); ?>">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <input type="text" placeholder="Product name" name="product_name" size="30"
                                       value="<?php echo get_the_title($ID); ?>" id="title" spellcheck="true" autocomplete="off">
                            </div>
                        </div>
                        <table class="form-table">
                            <tr>
                                <th>Brand</th>
                                <td>
                                    <?php
                                    $arg = array(
                                        'show_option_none' => 'Select Brand',
                                        'orderby' => 'NAME',
                                        'order' => 'ASC',
                                        'hide_empty' => 0,
                                        'name' => 'brand',
                                        'taxonomy' => 'wp_aff_brands',
                                        'selected' => $brands[0]->term_id
                                    );
                                    wp_dropdown_categories($arg);
                                    ?> <br>
                                    <input class="regular-text" type="text" name="product_brand_new"
                                           placeholder="Or type a new one">

                                    <p class="description">If adding a new category, none should be selected from the dropdown.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Price</th>
                                <td><input class="regular-text" type="text" name="product_price" placeholder="0.00"
                                           value="<?php echo $meta['wp_aff_product_price'][0]; ?>">

                                    <p class="description">&pound; sign not needed.</p></td>
                            </tr>
                            <tr>
                                <th>RRP</th>
                                <td><input class="regular-text" type="text" name="product_rrp" placeholder="0.00"
                                           value="<?php echo $meta['wp_aff_product_rrp'][0]; ?>">

                                    <p class="description">&pound; sign not needed.</p></td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>
                                    <textarea class="large-text" cols="46" rows="4"
                                              name="product_desc"><?php echo $meta['wp_aff_product_desc'][0]; ?></textarea>

                                    <p class="description">Not currently used on site but may be in future.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Product Link</th>
                                <td>
                                    <input class="large-text" type="url" name="product_url" placeholder="http://"
                                           value="<?php echo $meta['wp_aff_product_link'][0]; ?>">

                                    <p class="description">Affiliate link pasted here.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>Image</th>
                                <td>
                                    <div
                                        style="padding: 2px; padding-bottom: 0; border: solid 2px #ddd; background-color: #fff; display: inline-block;">
                                        <img src="<?php echo $meta['wp_aff_product_image'][0]; ?>"
                                             style="width: 150px; height: auto;">
                                    </div>
                                    <br>
                                    <input id="upload_image_button" type="button" class="button button-secondary"
                                           value="Upload Image"/>
                                    <input type="hidden" id="product_image" value="<?php echo $meta['wp_aff_product_image'][0]; ?>"
                                           name="product_image">

                                    <p class="description">The image should be at least 300px x 300px.</p>
                                </td>

                            </tr>
                        </table>
                        <table class="form-table">
                            <tr class="form-table">
                                <td width="33%" valign="top">
                                    <div style="">
            <?php $categories = new Tag_Checklist('wp_aff_categories', 'all', $ID); ?>

                                    </div>
                                </td>

                                <td width="33%" valign="top">
                                    <div style="">
            <?php $colours = new Tag_Checklist('wp_aff_colours', 'all', $ID); ?>

                                    </div>
                                </td>
                                <td width="33%" valign="top">
            <?php $sizes = new Tag_Checklist('wp_aff_sizes', 'all', $ID); ?>
                                </td>
                            </tr>
                        </table>
                        <?php
                        print_var($meta);
                        $test = new wpAffAPI();
                        print_var($test->update_product(20231, '2762_1809'));
                        ?>
                        <input type="hidden" value="wp_aff_edit_man_product" name="action"/>
                        <input type="hidden" value="<?php echo $ID; ?>" name="post_id"/>
                        <?php wp_nonce_field('wp_aff_edit_man_product', '_wpnonce', FALSE); ?>
                        <?php $redirect = remove_query_arg('msg', $_SERVER['REQUEST_URI']); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                    <?php submit_button(); ?>
                    </form>
            <?php } ?>
            </div>
            <?php
        }

        public function ajax_update_sticker() {
            $output = array();
            $output['status'] = 0;

            $meta = get_post_meta($_POST['post'], 'wp_aff_product_' . $_POST['var'], true);
            if (isset($meta) && $meta == 1) {
                if (update_post_meta($_POST['post'], 'wp_aff_product_' . $_POST['var'], 0)) {
                    $output['status'] = 1;
                    $output['previous'] = 1;
                    $output['new'] = 0;
                } else {
                    $output['status'] = 0;
                }
            } elseif (isset($meta) && $meta == 0) {
                if (update_post_meta($_POST['post'], 'wp_aff_product_' . $_POST['var'], 1)) {
                    $output['status'] = 1;
                    $output['previous'] = 0;
                    $output['new'] = 1;
                } else {
                    $output['status'] = 0;
                }
            } else {
                if (update_post_meta($_POST['post'], 'wp_aff_product_' . $_POST['var'], 1)) {
                    $output['status'] = 1;
                    $output['previous'] = 0;
                    $output['new'] = 1;
                } else {
                    $output['status'] = 0;
                }
            }
            echo json_encode((object) $output);
            die;
        }

        public function ajax_new_in() {
            $output = [];
            $output['status'] = 0;

            $meta = get_post_meta($_POST['post'], 'wp_aff_category_' . $_POST['var'], true);

            if (isset($meta) && $meta == 1) {
                if (update_post_meta($_POST['post'], 'wp_aff_category_' . $_POST['var'], 0)) {
                    $output['status'] = 'success';
                    $output['previous'] = 1;
                    $output['new'] = 0;
                } else {
                    $output['status'] = 0;
                }
            } elseif (isset($meta) && $meta == 0) {
                if (update_post_meta($_POST['post'], 'wp_aff_category_' . $_POST['var'], 1)) {
                    $output['status'] = 'success';
                    $output['previous'] = 0;
                    $output['new'] = 1;
                } else {
                    $output['status'] = 0;
                }
            } else {
                if (update_post_meta($_POST['post'], 'wp_aff_category_' . $_POST['var'], 1)) {
                    $output['status'] = 'success';
                    $output['previous'] = 0;
                    $output['new'] = 1;
                } else {
                    $output['status'] = 'failure';
                }
            }

            echo json_encode((object) $output);
            die();
        }

        public function ajax_new_in_single_product() {
            $output = [];
            $output['status'] = 0;

            $meta = get_post_meta($_POST['post'], 'wp_aff_product_new_in', true);

            if (isset($meta) && $meta == 1) {
                if (update_post_meta($_POST['post'], 'wp_aff_product_new_in', 0)) {
                    $output['status'] = 'success';
                    $output['previous'] = 1;
                    $output['new'] = 0;
                } else {
                    $output['status'] = 0;
                }
            } elseif (isset($meta) && $meta == 0) {
                if (update_post_meta($_POST['post'], 'wp_aff_product_new_in', 1)) {
                    $output['status'] = 'success';
                    $output['previous'] = 0;
                    $output['new'] = 1;
                } else {
                    $output['status'] = 0;
                }
            } else {
                if (update_post_meta($_POST['post'], 'wp_aff_product_new_in', 1)) {
                    $output['status'] = 'success';
                    $output['previous'] = 0;
                    $output['new'] = 1;
                } else {
                    $output['status'] = 'failure';
                }
            }

            echo json_encode((object) $output);
            die();
        }

        function admin_product_filter() {

            $output = array('status' => 1);

            $url = urldecode($_POST['referrer']);

            $output['url'] = add_query_arg('prod_' . $_POST['type'], $_POST['val'], $url);
            $output['url'] = $output['url'];
            echo json_encode((object) $output);
            die;
        }

        public function ajax_update_get_count($cron = false) {
            $output = array();


            #### No IDs check ####
            /* $qry_args = array(
              'post_status' => 'publish',
              'post_type' => 'wp_aff_products',
              'posts_per_page' => -1,
              'orderby' => 'post_date',
              'order' => 'DESC' ,
              'meta_query' => array(
              'relation' => 'OR',
              array(
              'key' => 'wp_aff_product_id',
              'compare' => 'NOT EXISTS' // this should work...
              ),
              array(
              'key' => 'wp_aff_product_id',
              'value' => '^([0-9]+)$',
              'compare' => 'NOT REGEXP' // this should work...
              ),
              )
              ); */

            if (function_exists('ini_set')) {
                @ini_set('memory_limit', '4096M');
            }
            $qry_args = array(
                'post_status' => 'all',
                'post_type' => 'wp_aff_products',
                'posts_per_page' => -1,
                'orderby' => 'post_date',
                'order' => 'DESC',
            );
            if ($posts = get_posts($qry_args)) {

                $output['status'] = 1;
                foreach ($posts as $post) {
                    set_time_limit(0);
                    //$prod_id = get_post_meta( $post->ID, 'wp_aff_product_id', true );
                    //$aff = get_post_meta( $post->ID, 'wp_aff_product_aff', true );

                    $meta = get_post_meta($post->ID);

                    if (isset($meta['wp_aff_product_id'])) {
                        $prod_id = $meta['wp_aff_product_id'][0];
                    } else {
                        $prod_id = '';
                    }

                    if (isset($meta['wp_aff_product_aff'])) {
                        $aff = $meta['wp_aff_product_aff'][0];
                    } else {
                        $aff = '';
                    }

                    if (isset($meta['wp_aff_product_link'])) {
                        $link = $meta['wp_aff_product_link'][0];
                    } else {
                        $link = '';
                    }

                    if (isset($meta['wp_aff_product_merch'])) {
                        $merch = $meta['wp_aff_product_merch'][0];
                    } else {
                        $merch = '';
                    }

                    $brand = wp_get_post_terms($post->ID, 'wp_aff_brands');

                    $output['ids'][] = array(
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'prod_id' => $prod_id,
                        'merch' => $merch,
                        'aff' => $aff,
                        'url' => $link,
                        'status' => $post->post_status
                    );
                }
            } else {
                $output['status'] = 0;
            }

            //$count_posts = wp_count_posts( 'wp_aff_products' );
            $output['total'] = count($posts); //

            if ($cron == true) {
                return $output;
            } else {
                $output = json_encode((object) $output);
                echo $output;
                die;
            }
        }

        function ajax_update_product() {
            $output = array();
            $api = new wpAffAPI();
            $data = $api->update_product($_POST['id'], $_POST['prod_id'], $_POST['url']);
            if ($data) {
                $output['status'] = 1;
            } else {
                $output['status'] = 0;
            }
            $output['html'] = $data;
            $output = json_encode($data);
            echo $output;
            die;
        }

        function cron_update_product($id, $prod_id, $merch = NULL) {
            $output = array();

            $api = new wpAffAPI();
            $data = $api->update_product($id, $prod_id, $merch);
            if ($data) {
                $output['status'] = 1;
            } else {
                $output['status'] = 0;
            }
            $output['html'] = $data;
            return $output;
        }

        public function product_price() {
            global $post;

            $price = get_post_meta($post->ID, 'wp_aff_product_price', true);
            $rrp = get_post_meta($post->ID, 'wp_aff_product_rrp', true);

            if (isset($rrp) && ($price < $rrp)) {

                echo '<div class="sale_price">was &pound;' . $rrp . '</div> &pound;' . $price;
            } else {
                echo '&pound;' . $price;
            }
        }

        function sitemaps() {
            global $wp_query;
            $type = get_query_var('shop-sitemap');
            if ($type != '') {
                header('Content-Type: application/xml; charset=utf-8');
                $this->sitemap_output($type);
                die();
            }
        }

        function sitemap_output($type) {
            echo '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="' . $this->plugin_url . 'sitemap.xsl"?>


<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
            switch ($type) {
                case 'categories' :
                    $tax = 'wp_aff_categories';
                    break;
                case 'brands' :
                    $tax = 'wp_aff_brands';
                    break;
            }
            $terms = get_terms($tax);
            foreach ($terms as $term) {

                echo '<url>
		<loc>' . site_url() . '/shop/' . ($type == 'brands' ? 'brand/' : '') . $term->slug . '/</loc>
		<lastmod>' . $this->get_last_post_date($term, $tax) . '</lastmod>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>

	</url>';
            }


            echo '
		</urlset>';
        }

        function get_last_post_date($term, $tax) {
            $args = array(
                'post_type' => 'wp_aff_products',
                'posts_per_page' => 1,
                'order_by' => 'post_date',
                'order' => 'DESC',
                'tax_query' => array(
                    array(
                        'taxonomy' => $tax,
                        'field' => 'ID',
                        'terms' => $term->term_id
                    )
                )
            );
            $cat_posts = get_posts($args);
            if (isset($cat_posts[0])) {
                return date('c', strtotime($cat_posts[0]->post_date));
            } else {
                return '';
            }
        }

        public function get_api_merchants() {

            if (count($this->option['apis']) > 0) {
                global $wpdb;
                $table_name = $wpdb->prefix . "feed_data";
                $wpdb->query("TRUNCATE TABLE $table_name");
                foreach ($this->option['apis'] as $affiliate) {
                    $classname = $affiliate['class'];
                    $class = new $classname();
                    $temp[] = $class->merchants();
                }

                die(var_dump($temp));

                $output = array();
                $output['items'] = array();
                foreach ($temp as $key => $input) {
                    $output['items'] = array_replace($output['items'], $input);
                }
                $output['total'] = count($output['items']);
                $output['status'] = 1;
                echo json_encode($output);
                die();
            }
        }

        public function cron_get_api_merchants() {
            if (count($this->option['apis']) > 0) {
		$temp=[];
                foreach ($this->option['apis'] as $affiliate) {
                    $classname = $affiliate['class'];
                    $class = new $classname();
                    $temp[] = $class->merchants();
                }
                $output = array();
                $output['items:'] = array();
                foreach ($temp as $input){
                   foreach ($input as $oneItem){
			$output['items'][] = $oneItem;
			}
                }
                $output['total'] = count($output['items']);
		 $output['status'] = 1;
                return $output;
            }
        }

        public function update_merchant_feed() {
            $classname = $this->option['apis'][$_POST['aff']]['class'];
            echo $classname;
            $class = new $classname();
            echo json_encode($class->update_feed($_POST['ID'], $_POST['merch']));
            die();
        }

        public function cron_update_merchant_feed($ID, $aff, $merch) {
            $classname = $this->option['apis'][$aff]['class'];
            $class = new $classname();
            return $class->update_feed($ID, $merch);
        }

        public function testMethod() {
            $webgains = WordPress_Affiliate_Shop_Webgains();
            $programs = $this->cron_get_api_merchants();
            print(var_dump($programs));
        }

        public function cron_process() {
            ini_set('memory_limit', '4096M');
            ini_set('max_execution_time', '10000');

            $mailhead = 'From: Aff Shop Cron <cron@sosensational.co.uk>' . "\r\n";

            $productlog = $this->get_plugin_path() . date('d-m-Y-H-i-s') . "_products.txt";
            $merchantlog = $this->get_plugin_path() . date('d-m-Y-H-i-s') . "_merchants.txt";


            $fp = fopen($merchantlog, 'w');
            $header = array("Number", "Merchant ID", "Merchant Name", "Affiliate", "Status", "Message");
            fputcsv($fp, $header, '|');

            wp_mail(get_option('admin_email'), 'Merchant Cron Started', "Merchant Log: $merchantlog", $mailhead);

            $i = 1;
            global $wpdb;
            $table_name = $wpdb->prefix . "feed_data";
            $wpdb->query("TRUNCATE TABLE $table_name");
            $newItemsIds = [];
            $merchants = $this->cron_get_api_merchants();
            $total = $merchants['total'];
            var_dump("total merchants: ".$total."  ");
            foreach ($merchants['items'] as $merchant) {
                echo ' '.$merchant['ID'].' ';
                $percent = number_format(($i / $total) * 100, 2);
                $data = $this->cron_update_merchant_feed($merchant['ID'], $merchant['aff'], $merchant['name']);
                if ($data['status'] == 1) {
                    $line = array($i . ' of ' . $total . ' (' . $percent . '%)', $merchant['ID'], $merchant['name'], $merchant['aff'], 'Updated - ' . $data['success'] . ' Inserted, ' . $data['error'] . ' Failed.', '');
                } else {
                    $line = array($i . ' of ' . $total . ' (' . $percent . '%)', $merchant['ID'], $merchant['name'], $merchant['aff'], 'Failed - ' . $data['success'] . ' Inserted, ' . $data['error'] . ' Failed.');
                }
                fputcsv($fp, $line, '|');
                $i++;
            }
            fclose($fp);
            wp_mail(get_option('admin_email'), 'Merchant Cron Ended', "Merchant Log: $merchantlog", $mailhead);


            $i = 1;

            $fp = fopen($productlog, 'w');
            $header = array("Number", "Post ID", "Product ID", "Affiliate", "Product Title", "Brand", "Image URL", "Price", "RRP", "Link", "Status");
            fputcsv($fp, $header, '|');
            wp_mail(get_option('admin_email'), 'Product Cron Started', "Product Log: $productlog", $mailhead);
            $products = $this->ajax_update_get_count(true);
            $total = $products['total'];
            foreach ($products['ids'] as $product) {

                $percent = number_format(($i / $total) * 100, 2);
                $data = $this->cron_update_product($product['id'], $product['prod_id'], $product['merch']);
                if ($data['html']['status'] == 1) {

                    $line = array($i . ' of ' . $total . ' (' . $percent . '%)', $product['id'], $data['html']['item']['product_id'], $data['html']['item']['product_aff'], $data['html']['item']['product_title'], $data['html']['item']['product_brand'], $data['html']['item']['product_image'], $data['html']['item']['product_price'], $data['html']['item']['product_rrp'], $data['html']['item']['product_link'], "Updated by ID");
                } elseif ($data['html']['status'] == 2) {

                    $line = array($i . ' of ' . $total . ' (' . $percent . '%)', $product['id'], $data['html']['item']['product_id'], $data['html']['item']['product_aff'], $data['html']['item']['product_title'], $data['html']['item']['product_brand'], $data['html']['item']['product_image'], $data['html']['item']['product_price'], $data['html']['item']['product_rrp'], $data['html']['item']['product_link'], "Updated by URL");
                } else {

                    $line = array($i . ' of ' . $total . ' (' . $percent . '%)', $product['id'], $product['prod_id'], $product['aff'], $product['title'], $product['merch'], "", "", "", "", "Trashed");
                }
                fputcsv($fp, $line, '|');
                $i++;
            }
            fclose($fp);
            wp_mail(get_option('admin_email'), 'Product Cron Ended', "Product Log: $productlog", $mailhead);

            die();
        }

        /**
         * Place code for your plugin's functionality here.
         */
        private function run_plugin() {

        }

    }

    $wp_aff = new WordPress_Affiliate_Shop;
