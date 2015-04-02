<?php
	require_once('admin/admin-init.php');
    require_once('inc/custom_functions.php');
	require_once('inc/wp_bootstrap_navwalker.php');
	require_once('inc/widgets.php');
	require_once('inc/template_functions.php');
	
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'sosen' ),
		'blog' => __( 'Blog Top Menu', 'sosen' ),
		'footer'  => __( 'Footer Menu', 'sosen' ),
	) );
	

	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'post-thumbnails' );
	
	add_image_size( 'home_top_small', 451, 347, true ); 
    add_image_size( 'home_slider', 700, 653, true ); 
	add_image_size( 'home_cat', 367, 234, true ); 
	add_image_size( 'home_brand', 367, 319, true ); 
	add_image_size( 'home-thumb', 240, 157, true ); 
	
    add_image_size( 'blog-small', 366, 150, true ); 
    add_image_size( 'blog-related', 366, 200, true ); 
    add_image_size( 'blog-large', 748, 300, true ); 
    
    add_image_size( 'hot-deal', 175, 175, false ); 
	
	function sosen_widgets_init() {
		register_sidebar( array(
			'name' => __( 'Page Sidebar', 'seowned' ),
			'id' => 'page_sidebar',
			'before_widget' => '<div class="sidebox">',
			'after_widget' => "</div>",
			'before_title' => '<h3><span>',
			'after_title' => '</span></h3>',
		) );
		
		register_sidebar( array(
			'name' => __( 'Blog Sidebar', 'seowned' ),
			'id' => 'blog_sidebar',
			'before_widget' => '<div class="sidebox">',
			'after_widget' => "</div>",
			'before_title' => '<h3><span>',
			'after_title' => '</span></h3>',
		) ); 
		
		register_sidebar( array(
			'name' => __( 'Contact Sidebar', 'seowned' ),
			'id' => 'contact_sidebar',
			'before_widget' => '<div class="sidebox">',
			'after_widget' => "</div>",
			'before_title' => '<h3><span>',
			'after_title' => '</span></h3>',
		) );
		
		register_sidebar( array(
			'name' => __( 'Shop Sidebar', 'seowned' ),
			'id' => 'shop_sidebar',
			'before_widget' => '<div class="shop_side">',
			'after_widget' => "</div>",
			'before_title' => '<h3><span>',
			'after_title' => '</span></h3>',
		) );
        
        register_sidebar( array(
			'name' => __( 'Hot Deals Sidebar', 'seowned' ),
			'id' => 'deals_sidebar',
			'before_widget' => '<div class="deal_ad">',
			'after_widget' => "</div>",
			'before_title' => '',
			'after_title' => '',
		) );
	}

	add_action( 'widgets_init', 'sosen_widgets_init' );
	
	add_action( 'wp_enqueue_scripts', 'enqueue_and_register_my_scripts' );
	function enqueue_and_register_my_scripts(){
		
		wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri() . '/css/bootstrap.min.css', '1.0');
		wp_enqueue_style( 'bootstrap_theme', get_stylesheet_directory_uri() . '/css/bootstrap-theme.min.css', '1.0');
		wp_enqueue_style( 'fontawesome', get_stylesheet_directory_uri() . '/css/font-awesome.min.css', '1.0');
		wp_enqueue_style( 'animate', get_stylesheet_directory_uri() . '/css/animate.css', '1.0');
		wp_enqueue_style( 'webfont', get_stylesheet_directory_uri() . '/MyFontsWebfontsKit.css', '1.0');		
		wp_enqueue_style( 'sosen-style', get_stylesheet_uri(), array( 'bootstrap', 'bootstrap_theme', 'fontawesome', 'animate' ), '1.1' );
		wp_enqueue_style( 'general-sass-compiled', plugins_url( 'SoSensational/styles/dest/general.css' ), array('megamenu') ); 		
                
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'bootstrap-js', get_stylesheet_directory_uri() . '/js/bootstrap.min.js', array( 'jquery'), '1.0', true );
		wp_enqueue_script( 'images-loaded', get_stylesheet_directory_uri() . '/js/imagesloaded.pkgd.min.js', array( 'jquery' ), '1.0', true);
		wp_enqueue_script( 'viewportchecker', get_stylesheet_directory_uri() . '/js/jquery.viewportchecker.js', array( 'jquery' ) , '1.0', true);
		wp_enqueue_script( 'masonry', get_stylesheet_directory_uri() . '/js/masonry.pkgd.min.js', array( 'jquery' ) , '1.0', true);
		wp_enqueue_script( 'sharre', get_stylesheet_directory_uri() . '/js/jquery.sharrre.min.js', array( 'jquery' ) , '1.0', true);
		wp_enqueue_script( 'nicescroll', get_stylesheet_directory_uri() . '/js/jquery.nicescroll.min.js', array( 'jquery' ) , '1.0', true);
		wp_enqueue_script( 'sosen-custom', get_stylesheet_directory_uri() . '/js/functions.js', array( 'jquery', 'bootstrap-js', 'images-loaded'), '1.0', true );
		
		wp_localize_script( 'sosen-custom', 'ajax_login_object', array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'redirecturl' => $_SERVER['REQUEST_URI'],
			'loadingmessage' => __('Signing in, please wait...')
		));
		
        
	}
	
	add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
	function ajax_login(){
	
		// First check the nonce, if it fails the function will break
		check_ajax_referer( 'ajax-login-nonce', 'security' );
	
		// Nonce is checked, get the POST data and sign user on
		$info = array();
		$info['user_login'] = $_POST['username'];
		$info['user_password'] = $_POST['password'];
		$info['remember'] = true;
	
		$user_signon = wp_signon( $info, false );
		if ( is_wp_error($user_signon) ){
			echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
		} else {
			echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
		}
	
		die();
	}
	
	add_action('wp_print_styles','lm_dequeue_header_styles');
	function lm_dequeue_header_styles() {
		wp_dequeue_style('yarppWidgetCss');
	}
	
	add_action('get_footer','lm_dequeue_footer_styles');
	function lm_dequeue_footer_styles()	{
		wp_dequeue_style('yarppRelatedCss');
	}	

    function the_excerpt_max_charlength($charlength) {
        $excerpt = strip_tags(get_the_content());
        $charlength++;
        echo '<p>';
        if ( mb_strlen( $excerpt ) > $charlength ) {
            $subex = mb_substr( $excerpt, 0, $charlength - 5 );
            $exwords = explode( ' ', $subex );
            $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
            if ( $excut < 0 ) {
                echo mb_substr( $subex, 0, $excut );
            } else {
                echo $subex;
            }
            echo '...';
        } else {
            echo $excerpt;
        }
        echo '</p>';
    }

    function sosen_post_meta() {
        if( $category = get_the_category() ) {
            echo '<div class="post_meta">';
            if($category[0]){
                echo '<a href="'.get_category_link($category[0]->term_id ).'">'.$category[0]->cat_name.'</a> | ';
            }
            the_time( get_option( 'date_format' ) );
            echo '</div>';
        }
    }

    function sosen_related_posts($cat, $post_id) {
        $output = '<div class="related_posts">
                    <h3><span>You May Also Like:</span></h3>
                        <div class="row">';
        $args = array(
            'posts_per_page'   => 4,
            'offset'           => 0,
            'category'         => $cat,
            'orderby'          => 'rand',
            'exclude'          => $post_id,
        );
        $posts_array = get_posts( $args ); 
        foreach( $posts_array AS $post ) {
            setup_postdata( $post );
            $output .= '<div class="col-md-6">';
            $output .= '<div class="related-image">';
            $output .= get_the_post_thumbnail( $post->ID, 'blog-related', array( 'class' => 'img-responsive' ) );
            $output .= '</div>';
            $output .= '<h4><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></h4>';
            $output .= '</div>';
        }
        $output .= '</div>
                </div>';
        echo $output;
    }
    add_filter('widget_categories_args','show_empty_categories_links');
    function show_empty_categories_links($args) {
        $args['hide_empty'] = 0;
        
        return $args;
    }

add_action( 'init', 'hot_deals_init' );
/**
 * Register a book post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function hot_deals_init() {
	$labels = array(
		'name'               => 'Hot Deals',
		'singular_name'      => 'Hot Deal',
		'menu_name'          => 'Hot Deals',
		'name_admin_bar'     => 'Hot Deals',
		'add_new'            => 'Add New Hot Deal',
		'add_new_item'       => 'Add New Hot Deal',
		'new_item'           => 'New Hot Deal',
		'edit_item'          => 'Edit Hot Deal',
		'view_item'          => 'View Hot Deal',
		'all_items'          => 'All Hot Deals',
		'search_items'       => 'Search Hot Deals',
		'parent_item_colon'  => 'Hot Deals Parent: ',
		'not_found'          => 'Hot Deal Not Found',
		'not_found_in_trash' => 'No Hot Deals in Trash',
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'hot-deals', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => true,
		'menu_position'      => null,
		'supports'           => array( 'title', 'thumbnail', 'excerpt' )
	);

	register_post_type( 'hot-deals', $args );
}
add_action( 'admin_init',  'change_excerpt_box_title' );
function change_excerpt_box_title() {
	remove_meta_box( 'postexcerpt', 'hot-deals', 'side' );
	add_meta_box('postexcerpt', __('Hot Deal Summary'), 'post_excerpt_meta_box', 'hot-deals', 'normal', 'high');
}
add_action('admin_head-nav-menus.php', 'wpclean_add_metabox_menu_posttype_archive');

function wpclean_add_metabox_menu_posttype_archive() {
add_meta_box('wpclean-metabox-nav-menu-posttype', 'Custom Post Type Archives', 'wpclean_metabox_menu_posttype_archive', 'nav-menus', 'side', 'default');
}

function wpclean_metabox_menu_posttype_archive() {
$post_types = get_post_types(array('show_in_nav_menus' => true, 'has_archive' => true), 'object');

if ($post_types) :
    $items = array();
    $loop_index = 999999;

    foreach ($post_types as $post_type) {
        $item = new stdClass();
        $loop_index++;

        $item->object_id = $loop_index;
        $item->db_id = 0;
        $item->object = 'post_type_' . $post_type->query_var;
        $item->menu_item_parent = 0;
        $item->type = 'custom';
        $item->title = $post_type->labels->name;
        $item->url = get_post_type_archive_link($post_type->query_var);
        $item->target = '';
        $item->attr_title = '';
        $item->classes = array();
        $item->xfn = '';

        $items[] = $item;
    }

    $walker = new Walker_Nav_Menu_Checklist(array());

    echo '<div id="posttype-archive" class="posttypediv">';
    echo '<div id="tabs-panel-posttype-archive" class="tabs-panel tabs-panel-active">';
    echo '<ul id="posttype-archive-checklist" class="categorychecklist form-no-clear">';
    echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $items), 0, (object) array('walker' => $walker));
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    echo '<p class="button-controls">';
    echo '<span class="add-to-menu">';
    echo '<input type="submit"' . disabled(1, 0) . ' class="button-secondary submit-add-to-menu right" value="' . __('Add to Menu', 'andromedamedia') . '" name="add-posttype-archive-menu-item" id="submit-posttype-archive" />';
    echo '<span class="spinner"></span>';
    echo '</span>';
    echo '</p>';

endif;
}

add_filter( 'views_edit-hot-deals', 'so_13813805_add_button_to_views' );
function so_13813805_add_button_to_views( $views )
{
    $views['my-button'] = '<a target="_blank" href="/hot-deals/" class="button">View Hot Deals</a>';
    return $views;
}
add_filter('manage_hot-deals_posts_columns', 'ST4_columns_head_only_movies', 10);
add_action('manage_hot-deals_posts_custom_column', 'ST4_columns_content_only_movies', 10, 2);

// CREATE TWO FUNCTIONS TO HANDLE THE COLUMN
function ST4_columns_head_only_movies($defaults) {
    
    $new = array();
    
    foreach($defaults as $key=>$value) {
        if($key == 'date') {
            $new['provider'] = 'Provider';   
        }
        $new[$key] = $value;   
    }
    
    return $new;
}
function ST4_columns_content_only_movies($column_name, $post_ID) {
    if ($column_name == 'provider') {
        $meta_values = get_post_meta( $post_ID, 'deal_provider', true );
        echo $meta_values;
    }
}

// add_filter('wpseo_breadcrumb_single_link', 'breadcrumbLinksOutput');

// function breadcrumbLinksOutput($links) {

// 	var_dump($links);

// }

// add_filter('wpseo_breadcrumb_links', 'editBreadcrumbLinks');

// function editBreadcrumbLinks($breadcrumbs) {
// 	var_dump($breadcrumbs);
// }