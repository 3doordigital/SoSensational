<?php
	require_once('admin/admin-init.php');
	require_once('inc/wp_bootstrap_navwalker.php');
	require_once('inc/widgets.php');
	require_once('inc/template_functions.php');
	require_once('inc/woocommerce_functions.php');
	
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'sosen' ),
		'footer'  => __( 'Footer Menu', 'sosen' ),
	) );
	

	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'post-thumbnails' );
	
	add_image_size( 'home_top_small', 451, 347, true ); //(cropped)
    add_image_size( 'home_slider', 700, 653, true ); //(not cropped)
	add_image_size( 'home_cat', 367, 234, true ); //(not cropped)
	add_image_size( 'home_brand', 367, 319, true ); //(not cropped)
	add_image_size( 'home-thumb', 240, 157, true ); //(not cropped)
	
	function sosen_widgets_init() {
		register_sidebar( array(
			'name' => __( 'Page Sidebar', 'seowned' ),
			'id' => 'page_sidebar',
			'before_widget' => '<div class="sidebox">',
			'after_widget' => "</div>",
			'before_title' => '<h2>',
			'after_title' => '</h2>',
		) );
		
		register_sidebar( array(
			'name' => __( 'Blog Sidebar', 'seowned' ),
			'id' => 'blog_sidebar',
			'before_widget' => '<div class="sidebox">',
			'after_widget' => "</div>",
			'before_title' => '<h2>',
			'after_title' => '</h2>',
		) ); 
		
		register_sidebar( array(
			'name' => __( 'Contact Sidebar', 'seowned' ),
			'id' => 'contact_sidebar',
			'before_widget' => '<div class="sidebox">',
			'after_widget' => "</div>",
			'before_title' => '<h2>',
			'after_title' => '</h2>',
		) );
		
		register_sidebar( array(
			'name' => __( 'Shop Sidebar', 'seowned' ),
			'id' => 'shop_sidebar',
			'before_widget' => '<div class="sidebox">',
			'after_widget' => "</div>",
			'before_title' => '<h2>',
			'after_title' => '</h2>',
		) );
	}

	add_action( 'widgets_init', 'sosen_widgets_init' );
	
	add_action( 'wp_enqueue_scripts', 'enqueue_and_register_my_scripts' );
	function enqueue_and_register_my_scripts(){
		
		wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri() . '/css/bootstrap.min.css', '1.0');
		wp_enqueue_style( 'bootstrap_theme', get_stylesheet_directory_uri() . '/css/bootstrap-theme.min.css', '1.0');
		wp_enqueue_style( 'fontawesome', get_stylesheet_directory_uri() . '/css/font-awesome.min.css', '1.0');
		wp_enqueue_style( 'animate', get_stylesheet_directory_uri() . '/css/animate.css', '1.0');
		
		wp_enqueue_style( 'sosen-style', get_stylesheet_uri(), array( 'bootstrap', 'bootstrap_theme', 'fontawesome', 'animate' ) );
		
		wp_enqueue_script('jquery');

		wp_enqueue_script( 'bootstrap-js', get_stylesheet_directory_uri() . '/js/bootstrap.min.js', array( 'jquery'), '1.0', true );
		wp_enqueue_script( 'images-loaded', get_stylesheet_directory_uri() . '/js/imagesloaded.pkgd.min.js', array( 'jquery' ), '1.0', true);
		wp_enqueue_script( 'viewportchecker', get_stylesheet_directory_uri() . '/js/jquery.viewportchecker.js', array( 'jquery' ) , '1.0', true);
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