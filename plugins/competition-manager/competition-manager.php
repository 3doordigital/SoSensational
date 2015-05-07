<?php
/*
  Plugin Name: WordPress Competition Manager
  Plugin URI: 
  Description: Create a shop on your WordPress site using the most popular Affiliate networks.
  Version: 0.1b
  Author: Dan Taylor
  Author URI: http://www.tailoredmarketing.co.uk
  License: GPL V3
 */
include( 'inc/base-functions.php' );
class WordPress_Competition_Manager {
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
    private $text_domain    = 'wpcompman';
    private $admin_icon     = 'dashicons-tickets';
    private $option_name    = 'wp_comp_man_settings';
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
        
		add_action('edit_form_after_title', array( $this, 'move_meta_boxes' ) );
		
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
                
        add_action( 'add_meta_boxes_wp_comp_man', array( $this, 'comp_meta_boxes') ); 
		add_action( 'save_post_wp_comp_man', array( $this, 'save_comp_meta_data' ) );
		
        add_action( 'add_meta_boxes_wp_comp_entries', array( $this, 'entry_meta_boxes') ); 
        
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        
        add_action( 'admin_menu', array( $this, 'create_menu' ) );
        
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
        
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
        
        add_action( 'wp_ajax_wp_comp_man_pick_winner', array( $this, 'pick_winner' ) );
        add_action( 'wp_ajax_wp_comp_man_export', array( $this, 'export_entries' ) );
        
        add_action( 'wp_ajax_wp_comp_man_add_entry', array( $this, 'add_comp_entry' ) );
        add_action( 'wp_ajax_nopriv_wp_comp_man_add_entry', array( $this, 'add_comp_entry' ) );
        
		add_action( 'admin_post_wp_comp_man_settings_save', array( $this, 'save_settings' ) ) ;
		add_action( 'admin_post_wp_comp_man_gen_settings_save', array( $this, 'gen_save_settings' ) ) ;
		
        add_filter( 'manage_wp_comp_entries_posts_columns', array( $this, 'entries_columns' ), 10 );
        add_action( 'manage_wp_comp_entries_posts_custom_column', array( $this,'entries_columns_content'), 10, 2 );
        
        add_filter( 'manage_wp_comp_man_posts_columns', array( $this, 'comp_columns' ), 10 );
        add_action( 'manage_wp_comp_man_posts_custom_column', array( $this,'comp_columns_content'), 10, 2 );
        
        add_action( 'restrict_manage_posts', array( $this, 'comp_filter' ) );
        add_filter( 'parse_query', array( $this, 'comp_filter_list' ) );
		
		add_filter( 'template_include', array( $this, 'load_shop_template' ) );
        
		add_filter( 'the_content', array( $this, 'display_single_comp'), 10 );
		
		add_action( 'template_redirect', array( $this, 'check_comp_date' ) );
		add_action( 'pre_get_posts', array( $this, 'remove_expired_comps' ) );

		$this->run_plugin();
	}
	
	function remove_expired_comps( $query ) {
		
		if( is_post_type_archive( 'wp_comp_man' ) && !is_admin() ) {
			$meta_query = array( 'relation' => 'AND' );
			$meta_query[] = array(
						'key'=>'wp_comp_sdate',
						'value'=> date("Y-m-d"),
						'compare'=>'<=',
						'type' => 'date'
					);
			$meta_query[] = array(
						'key'=>'wp_comp_edate',
						'value'=> date("Y-m-d"),
						'compare'=>'>=',
						'type' => 'date'
					);
			$query->set('meta_query',$meta_query);
			//print_var( $query );
			//print_var( $query->get('meta_query') );
			//die();
		}
	}
	
    function check_comp_date(  ) {
		if( is_singular( 'wp_comp_man' ) && !is_admin() ) {
			$sdate = strtotime( get_post_meta( get_the_ID(), 'wp_comp_sdate', true ) );
			$edate = strtotime( get_post_meta( get_the_ID(), 'wp_comp_edate', true ) );
			$cdate = strtotime( date("Y-m-d H:i:s") );
			
			if( $sdate > $cdate || $edate < $cdate) {
				wp_redirect( home_url( '/competitions/' ) ); 
				//echo $sdate.' : '.$edate.' : '.$cdate;
				exit;
			} 
		}
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
         wp_enqueue_script( 'fancybox', $this->plugin_url . 'js/jquery.fancybox.js', array( 'jquery' ) );
		 wp_enqueue_script( 'wp_comp_man_functions', $this->plugin_url . 'js/front-end.js' );
         wp_localize_script( 'wp_comp_man_functions', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
    
    public function register_styles() {
		wp_enqueue_style( 'fancybox_css', $this->plugin_url . 'css/jquery.fancybox.css' );
		wp_enqueue_style( 'all_css', $this->plugin_url . 'css/all.css' );
        $settings = $this->get_option();
		if( $settings['general_settings']['frontend_css'] == 1 ) {
        	wp_enqueue_style( 'wp_news-man-style', $this->plugin_url . 'css/front-end.css' );
		}
	}
     
    public function admin_register_scripts() {
        
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'wp_comp_man_admin_functions', $this->plugin_url . 'js/functions.js' );
        wp_localize_script( 'wp_comp_man_admin_functions', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
    
    public function admin_register_styles() {
        wp_enqueue_style( 'wp_news-man-admin-style', $this->plugin_url . 'css/admin.css' );
        wp_enqueue_style( 'fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
	}
     
    public function create_menu() {
        
        $this->main_page = add_submenu_page('edit.php?post_type=wp_comp_man', 'Competition Manager', 'View All', 'manage_options', 'competition-manager', array( $this, 'main_page' ));
        $this->sub_page = add_submenu_page('edit.php?post_type=wp_comp_man', 'Settings', 'Settings', 'manage_options', 'competition-manager-settings', array( $this, 'settings_page' ));
                
        add_action( "load-$this->sub_page", array ( $this, 'parse_message' ) );
	}
    
    public function comp_meta_boxes() {
        add_meta_box( 'wp_comp_entry', 'Competition Details', array( $this, 'entry_meta_box' ), 'wp_comp_man', 'side' , 'default' ); 
		add_meta_box( 'wp_comp_question', 'Competition Question', array( $this, 'comp_question_meta' ), 'wp_comp_man', 'advanced' , 'high' ); 
    }
	public function entry_meta_box( $post ) { 
		$meta = get_post_meta( $post->ID );
	?>
		<table class="form-table">
        	<tr>
            	<th>Start Date</th>
                <td><input name="wp_comp_sdate" type="date" value="<?php echo ( isset( $meta['wp_comp_sdate'][0] ) ? $meta['wp_comp_sdate'][0] : '' ); ?>"></td>
            </tr>
            <tr>
            	<th>End Date</th>
                <td><input name="wp_comp_edate" type="date" value="<?php echo ( isset( $meta['wp_comp_edate'][0] )? $meta['wp_comp_edate'][0] : '' ); ?>"></td>
            </tr>
            <tr>
            	<th>Brand</th>
                <td><input name="wp_comp_brand" type="text" value="<?php echo ( isset( $meta['wp_comp_brand'][0] ) ? $meta['wp_comp_brand'][0] : '' ); ?>"></td>
            </tr>
            <tr>
            	<th>Winners Required</th>
                <td><input name="wp_comp_winners" type="number" min="1" step="1" value="<?php echo ( isset( $meta['wp_comp_winners'][0] ) ? $meta['wp_comp_winners'][0] : '' ); ?>"></td>
            </tr>
            <tr>
            	<th>Facebook Only?</th>
                <td><input name="wp_comp_facebook" type="checkbox" value="1" value="1" <?php if( isset( $meta['wp_comp_facebook'][0] ) ) checked( $meta['wp_comp_facebook'][0], 1 ) ; ?> ></td>
            </tr>
        </table>
	<?php }
	
	public function comp_question_meta( $post ) { 
		$meta = get_post_meta( $post->ID );
	?>
		<table class="form-table">
        	<tr>
            	<th>Enter competition text</th>
                <td>
                	<p><label><input type="radio" <?php if( isset( $meta['wp_comp_type'][0] ) ) { checked($meta['wp_comp_type'][0], 1); } else { echo 'checked'; } ?> name="wp_comp_type" value="1"> Use: To be in with a chance to win this amazing prize, simply tell us:</label></p>
                    <p><input type="radio" <?php if( isset( $meta['wp_comp_type'][0] ) ) { checked($meta['wp_comp_type'][0], 2); }  ?> name="wp_comp_type" value="2"> <input type="text" name="wp_comp_type_text" class="comp-type-text" placeholder="Or type your own" value="<?php if( isset( $meta['wp_comp_type'][0] ) && $meta['wp_comp_type'][0] ==2 ) { echo $meta['wp_comp_type_text'][0]; } ?>">
                </td>
            </tr>
        	<tr>
            	<th>Question</th>
                <td><input type="text" name="wp_comp_question" class="large-text" value="<?php echo ( isset( $meta['wp_comp_question'][0] ) ? $meta['wp_comp_question'][0] : '' ); ?>"></td>
            </tr>
            <tr>
            	<th>Answer</th>
                <td><input type="text" name="wp_comp_answer" class="large-text" value="<?php echo ( isset( $meta['wp_comp_answer'][0] ) ? $meta['wp_comp_answer'][0] : '' ); ?>"></td>
            </tr>
            <tr>
            	<th>Special Instructions</th>
                <td><input type="text" name="wp_comp_rules" class="large-text" value="<?php echo ( isset( $meta['wp_comp_rules'][0] ) ? $meta['wp_comp_rules'][0] : '' ); ?>"><p class="description">e.g. Open to entrants in the UK and Europe only.</p></td>
            </tr>
            
        </table>
	<?php }
	
	public function get_comp_date( $comp_id ) {
		return $comp = get_post_meta( $comp_id, 'wp_comp_edate', true);  	
	}
	
	public function wp_footer() {
		global $post;
		if( get_post_type() == 'wp_comp_man' && isset( $_REQUEST['msg'] ) ) { ?>
            	<a class="popup fancybox.ajax">Popup</a>
				<?php if( $_REQUEST['msg'] == 1 ) { ?>
					<script type="text/javascript">
                    jQuery(document).ready( function($) {
                        $('.popup').fancybox({
                            wrapCSS: 'pop-up-thanks',
                            width: 600,
                            height: 372,
							maxWidth: 600,
							maxHeight: 372,
							padding: 0,
							autoSize: false,
                            dataType : 'html',
                            href: '<?php echo plugin_dir_url( __FILE__ ); ?>templates/thank-you.php?date=<?php echo $this->get_comp_date( $_REQUEST['comp'] ); ?>',
                            fitToView: false,
                        }).trigger('click');
                    });
                    </script>
                 <?php } elseif( $_REQUEST['msg'] == 0) { ?>
                 	<script type="text/javascript">
                    jQuery(document).ready( function($) {
                        $('.popup').fancybox({
                            wrapCSS: 'pop-up-fail',
                            width: 630,
                            height: 372,
							maxWidth: 570,
							maxHeight: 337,
                            dataType : 'html',
                            href: '<?php echo plugin_dir_url( __FILE__ ); ?>templates/duplicate.php?date=<?php echo $this->get_comp_date( $_REQUEST['comp'] ); ?>&comp=<?php echo $_REQUEST['comp']; ?>',
                            fitToView: false
                        }).trigger('click');
                    });
                    </script>
                 <?php } ?>
			<?php 
		}
	}
	
	public function move_meta_boxes() {
		global $post, $wp_meta_boxes;

        # Output the "advanced" meta boxes:
        #do_meta_boxes( get_current_screen(), 'advanced', $post );

        # Remove the initial "advanced" meta boxes:
        #unset($wp_meta_boxes['post']['test']);	
	}
	
	public function save_comp_meta_data( $post_id ) {
		if ( isset( $_REQUEST['wp_comp_question'] ) ) {
			update_post_meta( $post_id, 'wp_comp_question', sanitize_text_field( $_REQUEST['wp_comp_question'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_answer'] ) ) {
			update_post_meta( $post_id, 'wp_comp_answer', sanitize_text_field( $_REQUEST['wp_comp_answer'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_rules'] ) ) {
			update_post_meta( $post_id, 'wp_comp_rules', sanitize_text_field( $_REQUEST['wp_comp_rules'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_sdate'] ) ) {
			update_post_meta( $post_id, 'wp_comp_sdate', sanitize_text_field( $_REQUEST['wp_comp_sdate'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_edate'] ) ) {
			update_post_meta( $post_id, 'wp_comp_edate', sanitize_text_field( $_REQUEST['wp_comp_edate'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_winners'] ) ) {
			update_post_meta( $post_id, 'wp_comp_winners', sanitize_text_field( $_REQUEST['wp_comp_winners'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_facebook'] ) ) {
			update_post_meta( $post_id, 'wp_comp_facebook', sanitize_text_field( $_REQUEST['wp_comp_facebook'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_brand'] ) ) {
			update_post_meta( $post_id, 'wp_comp_brand', sanitize_text_field( $_REQUEST['wp_comp_brand'] ) );
		}
		if ( isset( $_REQUEST['wp_comp_type'] ) ) {
			update_post_meta( $post_id, 'wp_comp_type', sanitize_text_field( $_REQUEST['wp_comp_type'] ) );
		}
		if( isset( $_REQUEST['wp_comp_type'] ) && $_REQUEST['wp_comp_type'] == 2 && $_REQUEST['wp_comp_type_text'] != '' ) {
			update_post_meta( $post_id, 'wp_comp_type_text', sanitize_text_field( $_REQUEST['wp_comp_type_text'] ) );
		} elseif( isset( $_REQUEST['wp_comp_type'] ) && $_REQUEST['wp_comp_type'] == 1 ) {
			update_post_meta( $post_id, 'wp_comp_type_text', 'To be in with a chance to win this amazing prize, simply tell us:' );
		}
	}
	
    public function entry_meta_boxes() {
        add_meta_box( 'wp_comp_entry_raw', 'Entry Details', array( $this, 'entry_meta_box_raw' ), 'wp_comp_entries', 'advanced' , 'core' ); 
    }
    public function entries_meta_box( $post ) {
        $post_meta = get_post_meta($post->ID);
        echo '<div class="comp_entries">';
        echo '<p><strong>'.$post_meta['wp_comp_entries'][0].'</strong></p>
                        <p>People Have Entered</p>';  
        echo '</div>';
        echo '<div class="actions">
                        <a href="'.admin_url('admin.php?page=competition-manager&action=entries&wp_comp_man='.$post->ID).'" class="button-secondary">Email List</a> <a href="'. admin_url('admin.php?page=&competition-manager&action=entries&wp_comp_man='.$post->ID).'" class="button-secondary">View Entries</a>
                    </div>';
    }
    
    public function entry_meta_box_raw( $post ) {
        $meta = get_post_meta($post->ID);
        //print_var($meta); ?>
        <table class="form-table">
            <?php
                foreach( $meta as $key=>$value ) {
                    if( $key != '_edit_lock' ) {
                        $key = ucwords( str_replace( array('wp_comp_entry_', 'wp_comp_', '-', '_'), array( '', '', ' ', ' '), $key ) );
                        echo '<tr><th>'.$key.'</th><td>'.$value[0].'</td></tr>';
                    }
                }
            ?>
        </table>
    <?php
    }
    public function register_widgets() {
        
    }
    
    public function register_post_types() {
        
        $labels = array(
            'name'               => _x( 'Competitions', 'post type general name', 'your-plugin-textdomain' ),
            'singular_name'      => _x( 'Competition', 'post type singular name', 'your-plugin-textdomain' ),
            'menu_name'          => _x( 'Competition Manager', 'admin menu', 'your-plugin-textdomain' ),
            'name_admin_bar'     => _x( 'Competition', 'add new on admin bar', 'your-plugin-textdomain' ),
            'add_new'            => _x( 'Add New', 'book', 'your-plugin-textdomain' ),
            'add_new_item'       => __( 'Add New Competition', 'your-plugin-textdomain' ),
            'new_item'           => __( 'New Competition', 'your-plugin-textdomain' ),
            'edit_item'          => __( 'Edit Competition', 'your-plugin-textdomain' ),
            'view_item'          => __( 'View Competition', 'your-plugin-textdomain' ),
            'all_items'          => __( 'View All', 'your-plugin-textdomain' ),
            'search_items'       => __( 'Search Competitions', 'your-plugin-textdomain' ),
            'parent_item_colon'  => __( 'Parent Competitions:', 'your-plugin-textdomain' ),
            'not_found'          => __( 'No competitions found.', 'your-plugin-textdomain' ),
            'not_found_in_trash' => __( 'No competitions found in Trash.', 'your-plugin-textdomain' )
        );
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'competitions', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon' => $this->admin_icon,
            'supports'           => array( 'title', 'editor', 'thumbnail' )
        );
		
		$args2 = array( 
				'labels' => array( 'name' => 'Competition Entries' ), 
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => false,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_icon' => $this->admin_icon, 
				'supports' => array( 'title', 'custom-fields' ) 
			);
		
        register_post_type( 'wp_comp_man', $args );
        register_post_type( 'wp_comp_entries', $args2 );
    }
    
    public function register_taxonomies() {
        
    }
    
	public function get_include_contents($filename) {
		if (is_file($filename)) {
			ob_start();
			include $filename;
			return ob_get_clean();
		}
		return false;
	}
	
	public function display_single_comp( $content = '' ) {
		global $post;
		
		if ($post->post_type == "wp_comp_man" && is_single() ) {
			$contents = $content;
			if(file_exists(get_stylesheet_directory(). '/comp_templates/single.php')) {
				$contents .= $this->get_include_contents(get_stylesheet_directory(). '/comp_templates/single.php');
			} elseif(file_exists($this->plugin_path. '/templates/single.php')) {
                $contents .= $this->get_include_contents( $this->plugin_path . '/templates/single.php' );
			} 
			return $contents;
		} else {
			return $content;	
		}	
	}
    
    
    public function parse_message() {
        if ( ! isset ( $_GET['msg'] ) )
            return;

        $text = FALSE;
        
        switch($_GET[ 'msg' ]) {
            case 1 :
                $this->msg_text = 'Competition Added';
                $this->msg_class = 'updated';
                break;
            case 2 :
                $this->msg_text = 'Competition Updated';
                $this->msg_class = 'updated';
                break;
            case 3 :
                $this->msg_text = 'Settings Updated';
                $this->msg_class = 'updated';
                break;
            case 4 :
                $this->msg_text = 'Competition Trashed';
                $this->msg_class = 'updated';
                break;
            case 5 :
                $this->msg_text = 'Error! Competition Could Not Be Trashed';
                $this->msg_class = 'error';
                break;
             case 6 :
                $this->msg_text = 'Winners Chosen!';
                $this->msg_class = 'updated';
                break;
            case 7 :
                $this->msg_text = 'Category Updated!';
                $this->msg_class = 'updated';
                break;
        }
        
        if ( $this->msg_text )
            add_action( 'admin_notices', array ( $this, 'render_msg' ) );
    }

    public function render_msg() {
        echo '<div id="message" class="' . $this->msg_class . '"><p>'
            . $this->msg_text . '</p></div>';
    }
    private function is_even($number) {
        $isEven = false;
        if (is_numeric ($number)) {
            if ( $number % 2 == 0) $isEven = true;
        }
        return $isEven;
    }
    
	public function gen_save_settings() {
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_comp_man_gen_settings_save' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) ); 
		$data = $this->get_option();
		print_var($data);
		$data['general_settings'] = array(
			'frontend_css'	=> $_POST['wp_comp_css']
		);
		
		update_option( $this->option_name, $data );
		$url = add_query_arg( 'msg', 3, $_REQUEST['_wp_http_referer'] );
        wp_safe_redirect( $url );
	}
	
    public function save_settings() {
        if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_comp_man_settings_save' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) ); 
        
        if( isset( $_POST['comp_man_field_name'] )) {
			$data = $this->get_option();
			$data['form_fields'] = array();
			$count = count( $_POST['comp_man_field_name'] );
			for( $n = 0; $n<$count; $n++ ) {
				$name = sanitize_text_field( $_POST['comp_man_field_name'][$n] );
				$type = sanitize_text_field( $_POST['comp_man_field_type'][$n] );
				$order = sanitize_text_field( $_POST['comp_man_field_order'][$n] );
				if( isset( $_POST['comp_man_field_req'][$n] ) ) {
					$req  = $_POST['comp_man_field_req'][$n];
				} else {
					$req = 0;   
				}
				if( $_POST['comp_man_field_del'][$n] != '1' ) {
					$data['form_fields'][] = array(
						'field_name'    => $name,
						'field_type'    => $type,
						'field_req'     => $req,
						'field_order'   => $order,
						'del' => $_POST['comp_man_field_del'][$n]
					);
				}
			}
			update_option( $this->option_name, $data );
		}
        $url = add_query_arg( 'msg', 3, $_REQUEST['_wp_http_referer'] );
        wp_safe_redirect( $url );
        print_var($_POST);
        print_var($data);
        
    }
    
    
    
    public function edit_comp_process() {
        if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'wp_comp_man_edit' ) )
            die( 'Invalid nonce.' . var_export( $_POST, true ) );
        
        $my_post = array(
            'ID'            => $_POST['wp_comp_id'],
            'post_title'    => wp_strip_all_tags( $_POST['wp_comp_name'] ),
            'post_status'   => $_POST['post_status'],
            'post_type'     => 'wp_comp_man',
            'post_content'  => $_POST['post_content']
        );

        $insID = wp_update_post( $my_post );
        update_post_meta($insID, 'wp_comp_answer', $_POST['wp_comp_answer']);
        update_post_meta($insID, 'wp_comp_brand', $_POST['wp_comp_brand']);
        update_post_meta($insID, 'wp_comp_edate', $_POST['wp_comp_edate']);
        update_post_meta($insID, 'wp_comp_question', $_POST['wp_comp_question']);
        update_post_meta($insID, 'wp_comp_sdate', $_POST['wp_comp_sdate']);
        update_post_meta($insID, 'wp_comp_winners', $_POST['wp_comp_winners']);
        if( isset( $_POST['wp_comp_facebook'] ) ) update_post_meta($insID, 'wp_comp_facebook', $_POST['wp_comp_facebook']);
        
        $url = add_query_arg( 'msg', 2, admin_url('admin.php?page=competition-manager&action=edit&wp_comp_man='.$insID ) );
        wp_safe_redirect( $url );
    }
    
        
    public function main_page() {
    ?>
        <div class="wrap">
            <h2>Competition Manager</h2>
            
            <?php
               
            ?>
        </div>
    <?php
    }
    
    public function form_builder() { ?>
        <div id="comp_man_form_builder">
            <table id="comp_man_form_inner">
                <thead><th></th><th>Field Name</th><th>Field Type</th><th style="text-align: center;">Required?</th><th>Order</th><th></th></thead>
                <tbody>
                    <?php
					$i = 4;
                    $count = 0;
					echo '<tr class="row">
                                        <th>1</th>
                                        <td>First Name</td>
                                        <td>Single line text</td>
                                        <th><input type="checkbox" readonly value="1" checked disabled></th>
                                        <td>0</td>
                                        <th>Required</th>
                                    </tr>'; 
							echo '<tr class="row">
                                        <th>2</th>
                                        <td>Last Name</td>
                                        <td>Single line text</td>
                                        <th><input type="checkbox" readonly value="1" checked disabled ></th>
                                        <td>1</td>
                                        <th>Required</th>
                                    </tr>'; 
							
							echo '<tr class="row">
                                        <th>3</th>
                                        <td>Email</td>
                                        <td>Single line text</td>
                                        <th><input type="checkbox" readonly value="1" checked disabled ></th>
                                        <td>2</td>
                                        <th>Required</th>
                                    </tr>'; 
                        if( isset( $this->option['form_fields'] ) ) {
                            $fields = $this->option['form_fields'];
                            
                            foreach( $fields as $key=>$row ) {
                                $sort[$key] = $row['field_order'];
                            }
                            array_multisort($sort, SORT_ASC, $fields);
							
							
							
                            foreach( $fields as $field ) {
                                echo '<tr class="row">
                                        <th>'.$i.'</th>
                                        <td><input type="text" placeholder="Field Name" name="comp_man_field_name['.$count.']" value="'.$field['field_name'].'"></td>
                                        <td>
                                            <select name="comp_man_field_type['.$count.']">
                                                <option value="0" '.selected( $field['field_type'], 0, false ).'>Single line text</option>
                                                <option value="1" '.selected( $field['field_type'], 1, false ).'>Multi line text</option>
                                                <option value="2" '.selected( $field['field_type'], 2, false ).'>Checkbox</option>
                                            </select>
                                        </td>
                                        <th><input type="checkbox" value="1" '.checked( $field['field_req'], 1, false ).' name="comp_man_field_req['.$count.']"></th>
                                        <td><input type="number" min="3" step="1" placeholder="Order" name="comp_man_field_order['.$count.']" value="'.$field['field_order'].'"></td>
                                        <th><input type="hidden" value="0" class="del_field'.$count.'" name="comp_man_field_del['.$count.']"><a rel="'.$count.'" href="#" class="comp_man_field_remove"><i class="fa fa-trash"></i></a></th>
                                    </tr>'; 
                                $i++;
                                $count++;
                            }
                        } ?>
                </tbody>
                <tfoot><th colspan="6"><a href="#" class="comp_man_add_row button-secondary"><i class="fa fa-plus-circle"></i> Add Row</a></th></tfoot>
            </table>
            <input type="hidden" class="comp_man_field_count" value="<?php echo $count; ?>">
        </div>   
    <?php 
    }
    
    public function settings_page() { ?>
        <div class="wrap">
            <h2>Competition Manager Settings</h2>
            <h2 class="nav-tab-wrapper">
            	<a class="nav-tab <?php echo ( !isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] == 0 ? 'nav-tab-active' : '' ); ?>" href="<?php echo admin_url('edit.php?post_type=wp_comp_man&page=competition-manager-settings&tab=0'); ?>">General Settings</a>
                <a class="nav-tab <?php echo ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 1 ? 'nav-tab-active' : '' ); ?>" href="<?php echo admin_url('edit.php?post_type=wp_comp_man&page=competition-manager-settings&tab=1'); ?>">Form Builder</a>
            </h2>
            <?php if( !isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] == 0 ) { ?>
            	<h3>General Settings</h3>
                <form method="POST" id="wp_comp_man" action="<?php echo admin_url('admin-post.php'); ?>">
                    <div class="comp_man_right">
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
                    <div class="comp_man_left">
                    <p>The following settings are available.</p>
                    <?php 
						$settings = $this->get_option(); 
						$settings = $settings['general_settings'];
					?>
                        <table class="form-table">
                    		<tr>
                            	<th>Use our front-end CSS file?</th>
                                <td><input <?php checked( 1, $settings['frontend_css'] ); ?>type="checkbox" value="1" name="wp_comp_css"></td>
                            </tr>
                        </table>
                    </div>
                </form>
            <?php } elseif( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 1 ) { ?>
                <h3>Form Builder</h3>
                <form method="POST" id="wp_comp_man" action="<?php echo admin_url('admin-post.php'); ?>">
                    <div class="comp_man_right">
                    <div class="wp-box">
                        <h3>Details</h3>
                        <div class="inside">
                            <p>On this page you can create the form that visitors use to enter the competitions.</p>
                        </div>
                        <div class="actions">
                            <input type="hidden" value="wp_comp_man_settings_save" name="action" />
                            <?php $redirect =  remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ); ?>
                            <?php wp_nonce_field( 'wp_comp_man_settings_save', '_wpnonce', FALSE ); ?>
                            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                            <?php submit_button( 'Save Settings', 'primary', 'save_settings', false ); ?>
                        </div>
                    </div>
                </div>
                <div class="comp_man_left">
                    <?php $this->form_builder(); ?>
                </div>
                </form>
            <?php } ?>
        </div>
    <?php
      //print_var($this->option['form_fields']);          
    }
    
    public function frontend_form( $bootstrap = false, $cols = false ) {
		$sdate = strtotime( get_post_meta( get_the_ID(), 'wp_comp_sdate', true ) );
		$edate = strtotime( get_post_meta( get_the_ID(), 'wp_comp_edate', true ) );
		$cdate = strtotime( date("Y-m-d H:i:s") );
		
		if( $sdate > $cdate ) {
			echo '<p>This competition has not yet started.</p>';
		} elseif( $cdate > $edate ) {
			echo '<p>This competition has ended.</p>';
		} else {
		
			if( $cols ) $halfcol = $cols/2;
			
			if( isset( $this->option['form_fields'] ) ) {
				$fields = $this->option['form_fields'];
				$fields[] = array(
					'field_name' => 'First Name',
					'field_type' => 'text',
					'field_req'  => 1,
					'field_order'=> 0
				);
				$fields[] = array(
					'field_name' => 'Last Name',
					'field_type' => 'text',
					'field_req'  => 1,
					'field_order'=> 1
				);
				$fields[] = array(
					'field_name' => 'Email',
					'field_type' => 'text',
					'field_tooltip' => 'By entering your email address, you consent to receiving newsletters from SoSensational and the prize giving brand. You may unsubscribe at any time.',
					'field_req'  => 1,
					'field_order'=> 2
				);
				$i = 1;
				$count = count( $fields );
				echo '<form id="comp_form" method="post">';
				echo '<h4>Your Answer *</h4>
				<p><textarea class="form-control" required name="wp_comp_answer" class="wp_comp_answer"></textarea></p>';
				foreach( $fields as $key=>$row ) {
					$sort[$key] = $row['field_order'];
				}
				array_multisort($sort, SORT_ASC, $fields);
				foreach( $fields as $field ) {  
					$field_name = sanitize_title( $field['field_name'] );
					if( $bootstrap ) {
						if( !$this->is_even( $i ) ) {
							echo '<div class="row">';
						} 
						echo '<div class="col-md-'.$halfcol.'">';	
					} else {
						echo '<p>';
					}
					switch( $field['field_type'] ) {
						case 0 :
							echo '<label for="'.$field_name.'">'.$field['field_name'].' '. ( isset( $field['field_req'] ) && $field['field_req'] == 1 ? '*' : '' ).'</label> <input class="form-control" '.( isset( $field['field_tooltip'] ) ? 'data-toggle="tooltip" data-placement="top" title="'.$field['field_tooltip'].'"' : '' ).' '.( isset( $field['field_req'] ) && $field['field_req'] == 1 ? 'required' : '' ) .' type="text" placeholder="'.$field['field_name'].'" name="'.$field_name.'">';
							break;
						case 1 :
							echo '<label for="'.$field_name.'">'.$field['field_name'].'</label> <textarea '.( isset( $field['field_req'] ) && $field['field_req'] == 1 ? 'required' : '' ) .' class="form-control" placeholder="'.$field['field_name'].'" name="'.$field_name.'"></textarea>';
							break;
						case 2 :
							echo '<div class="checkbox"><label for="'.$field_name.'"><input '.( isset( $field['field_req'] ) && $field['field_req'] == 1 ? 'required' : '' ) .' type="checkbox" value="1" name="'.$field_name.'"> '.$field['field_name'].'</label></div>';
							break;
					}
					
					if( $bootstrap ) {
						echo '</div>';
						if( $this->is_even( $i ) ) {
							echo '</div>';   
						} elseif( !$this->is_even( $i ) && $i == $count ) {
							echo '</div>';   
						}	
					} else {
						echo '</p>';
					}
					$i++;
				}
				echo '<p>* Required field</p>';
				echo '<input type="hidden" name="competition" value="'.get_the_title().'">';
				echo '<input type="hidden" name="competition-id" value="'.get_the_ID().'">';
				echo '<input type="hidden" name="action" value="wp_comp_man_add_entry">';
				
				echo '<br><div class="g-recaptcha" data-sitekey="6LdFgwYTAAAAANpxHyMNhLCjqNII56duND8kOPiE"></div><br>';
				echo '<a target="_blank" href="/competition-terms-conditions/">Terms &amp; Conditions</a>';
				
				echo'<p><button type="submit" class="btn btn-primary" id="submit_answer">Submit Answer</button></p>';
				echo '</form>';
				return ob_get_contents();
				ob_end_clean();
			} else {
				echo 'help';
			}
		}
    }
    
    public function add_comp_entry() {
        //print_var($_POST);
		$return = array();
		
		$params = array();
		$params['secret'] = '6LdFgwYTAAAAAAnuF0OV3TBHNIdhWQVHRfjj80Wf'; // Secret key
		if (!empty($_POST) && isset($_POST['g-recaptcha-response'])) {
			$params['response'] = urlencode($_POST['g-recaptcha-response']);
		}
		$params['remoteip'] = $_SERVER['REMOTE_ADDR'];
	
		$params_string = http_build_query($params);
		$requestURL = 'https://www.google.com/recaptcha/api/siteverify?' . $params_string;
	
		// Get cURL resource
		$curl = curl_init();
	
		// Set some options
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $requestURL,
		));
	
		// Send the request
		$response = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
	
		$response = @json_decode($response, true);
	
		if ($response["success"] == true) {
		
		$args = array(
			'post_type'		=> 'wp_comp_entries',
			'posts_per_page' => -1,
		);
		$args['meta_query'] = array( 'relation' => 'AND' );
		$args['meta_query'][] = array(
					'key'       => 'wp_comp_entry_competition-id',
					'value'     => $_POST['competition-id'],
					'compare'   => '=',
					'type'      => 'CHAR',
				);
		$args['meta_query'][] = array(
					'key'       => 'wp_comp_entry_email',
					'value'     => $_POST['email'],
					'compare'   => '=',
					'type'      => 'CHAR',
				);
		//print_var($args);
		$lookup = new WP_Query( $args ) ;
		//print_var($lookup);
		if( $lookup->have_posts() ) {
			$return = array(
				'status' 	=> 0,
				'message'	=> 'The email address has already entered the competition.',
				'redirect'	=> site_url('competitions/?msg=0&comp='.$_POST['competition-id'])
			);
		} else {
		
			if( isset( $_POST['first-name'] ) && isset( $_POST['last-name'] ) ) {
				$name = $_POST['first-name'].' '.$_POST['last-name'];
			} elseif( isset( $_POST['name'] ) ) {
				$name = $_POST['name'];
			} else { $name = ''; };
			$my_post = array(
				'post_title'    => $name.' '.date('d-m-Y H:i:s'),
				'post_status'   => 'publish',
				'post_type' => 'wp_comp_entries'
			);
	
			// Insert the post into the database
			$insID = wp_insert_post( $my_post );
			foreach( $_POST as $key=>$value ) {
				if( $key != 'action' ) {
					update_post_meta($insID, 'wp_comp_entry_'.$key, $value);  
				}
			}
			
			require_once ( $this->get_plugin_path().'/campaign-monitor/csrest_subscribers.php' );

			/*$auth = array(
				'access_token' => 'your access token',
				'refresh_token' => 'your refresh token');*/
			
			$auth = array('api_key' => 'db073d15d60ca2279ed792532264c19e');
			$wrap = new CS_REST_Subscribers('f59ef39c0b2b7b92bd444170cf5b260a', $auth);

			$result = $wrap->add(array(
				'EmailAddress' => $_POST['email'],
				'Name' => $name,
				'CustomFields' => array(
					array(
						'Key' => 'Competition ID',
						'Value' => $_POST['competition-id']
					),
					array(
						'Key' => 'Entry ID',
						'Value' => $insID
					),
				),
				'Resubscribe' => true
			));
			if($result->was_successful()) {
				$return = array(
					'status' 	=> 1,
					'message'	=> 'Entry successfully added.',
					'redirect'	=> site_url('competitions/?msg=1&comp='.$_POST['competition-id']),
					'comp'		=> $_POST['competition-id']
				);
			} else {
				$return = array(
					'status' 	=> 1,
					'message'	=> 'Entry successfully added, but an error occured at Campaign Monitor',
					'redirect'	=> site_url('competitions/?msg=1&comp='.$_POST['competition-id']),
					'comp'		=> $_POST['competition-id']
				);
				 echo 'Failed with code '.$result->http_status_code."\n<br /><pre>";
					var_dump($result->response);
					echo '</pre>';
			}
		}
		} else {
			$return = array(
					'status' 	=> 3,
					'message'	=> 'Captcha failed.',
					'redirect'	=> site_url('competitions/?msg=0&comp='.$_POST['competition-id']),
					'comp'		=> $_POST['competition-id']
				);
		}
		echo json_encode( $return );
		die();
    }
    public function entries_columns ($defaults) {
        $defaults['competition'] = 'Competition';
        $defaults['name'] = 'Name';
        return $defaults;
    }
    public function entries_columns_content ($column_name, $post_ID) {
        $meta = get_post_meta($post_ID);
		//print_var($meta);
		if( isset( $meta['wp_comp_entry_first-name'][0] ) && isset( $meta['wp_comp_entry_last-name'][0] ) ) {
			$name = $meta['wp_comp_entry_first-name'][0].' '.$meta['wp_comp_entry_last-name'][0];
		} elseif( isset( $meta['wp_comp_entry_name'][0] ) ) {
			$name = $meta['wp_comp_entry_name'][0];
		}
        if ($column_name == 'competition') {
            echo '<a href="'.get_permalink( $meta['wp_comp_entry_competition-id'][0] ).'">'.$meta['wp_comp_entry_competition'][0].' ('.$meta['wp_comp_entry_competition-id'][0].')</a>';
        }
        if ($column_name == 'name') {
            echo $name;
        }
    }
    
    public function comp_columns ($defaults) {
        $defaults['dates'] = 'Date';
        //$defaults['brand'] = 'Brand';
        $defaults['facebook'] = 'Facebook Only?';
        $defaults['status'] = 'Status';
        $defaults['pick_winner'] = 'Winner(s)';
        $defaults['actions'] = '';
        unset( $defaults['date'] );
        return $defaults;
    }
    public function comp_columns_content ($column_name, $post_ID) {
        $meta = get_post_meta($post_ID);
        $status = get_post_status( $post_ID );
        $entry_args = array(
            'meta_key'   => 'wp_comp_entry_competition-id',
            'meta_value' => $post_ID,
            'post_type'  => 'wp_comp_entries'
        );
        $entry_query = new WP_Query( $entry_args );
        wp_reset_postdata();
        if( isset ( $meta['wp_comp_winner'][0] ) && $meta['wp_comp_winner'][0] != '' ) {
            $winners = json_decode( $meta['wp_comp_winner'][0] );
        }
        if ($column_name == 'brand') {
            if( $meta['wp_comp_brand'][0] != '' ) echo $meta['wp_comp_brand'][0] ;
        }
        if ($column_name == 'facebook') {
            if( isset( $meta['wp_comp_facebook'] ) && $meta['wp_comp_facebook'][0] == 1 ) echo '<i class="fa fa-facebook-square fa-lg"></i>';
        }
        if ($column_name == 'dates') {
            if( $meta['wp_comp_sdate'][0] != '' ) echo date('d-m-Y', strtotime( $meta['wp_comp_sdate'][0] ) );
            if( $meta['wp_comp_edate'][0] != '' ) echo ' - '.date('d-m-Y', strtotime( $meta['wp_comp_edate'][0] ) );
        }
        if ($column_name == 'status') {
            echo '<table>';
            echo '<tr><th>Status</th><td>';
            switch ($status) {
                case 'publish' :
                    echo 'Running';
                    break;
                case 'draft' :
                    echo 'Not Running';
                    break;
            }
            echo '</td></tr>';
            echo '<tr><th>Entries</th><td>'. $entry_query->post_count.'</td></tr>';
            echo '<tr><th>Answer</th><td>'. $meta['wp_comp_answer'][0].'</td></tr>';
            echo '</table>';
        }
        
        if ($column_name == 'pick_winner') {
             if( isset( $winners ) ) {
                 echo '<table>';
                    foreach( $winners as $winner ) {
                        echo '<tr><th><a href="'.admin_url('post.php?post='.$winner->id.'&action=edit').'">'.$winner->name.'</a></th><td><strong>A: </strong>'.$winner->answer.'</td></tr>';   
                    }
                 echo '</table>';
             }   
        }
        if ($column_name == 'actions') {
            echo '<a href="#" '.( $entry_query->post_count == 0 ? 'disabled' : '' ).' class="button button-primary admin_pick_winner" data-comp="'.$post_ID.'" data-winners="'.$meta['wp_comp_winners'][0].'" title="Pick Winner"><i class="fa fa-ticket"></i> Pick Winner</a> ';
            echo '<a href="'.admin_url('edit.php?s&post_status=all&post_type=wp_comp_entries&action=-1&m=0&wp_comp_man_id='.$post_ID.'&filter_action=Filter&paged=1&mode=list&action2=-1').'" '.( $entry_query->post_count == 0 ? 'disabled' : '' ).' class="button button-secondary" title="View Entries"><i class="fa fa-users"></i></a> ';
            echo '<a href="#" '.( $entry_query->post_count == 0 ? 'disabled' : '' ).' data-comp="'.$post_ID.'" class="button button-secondary admin_export_entries" title="Export Entries"><i class="fa fa-cloud-download"></i></a> ';
        }
    }
    
    public function comp_filter() {
        global $typenow;
        global $wp_query;
        
        if ($typenow == 'wp_comp_entries') {
            echo '<select name="wp_comp_man_id" class="postform">';
            $comps = get_posts( array ( 'post_type' => 'wp_comp_man' ) );
            echo '<option value="-1">All competitions</option>';
            foreach( $comps as $comp ) {
                echo '<option '. ( isset( $_GET['wp_comp_man_id'] ) && $_GET['wp_comp_man_id'] == $comp->ID ? 'selected' : '' ) .' value="'.$comp->ID.'">'.$comp->post_title.'</option>';
            }
            echo '<select>';
        }
    }
    
    public function comp_filter_list( $query ) {
        if( is_admin() && $query->query['post_type'] == 'wp_comp_entries' && !isset( $_POST ) ) {
            $qv = &$query->query_vars;
            $qv['meta_query'] = array();
        }
        if( !empty( $_GET['wp_comp_man_id'] ) && $_GET['wp_comp_man_id'] != -1 ) {
          $qv['meta_query'][] = array(
            'field' => 'wp_comp_entry_competition-id',
            'value' => $_GET['wp_comp_man_id'],
            'compare' => '=',
            'type' => 'CHAR'
          );
        }
    }
    public function pick_winner() {
        $winners = get_post_meta($_REQUEST['comp'], 'wp_comp_winners', true);
        $args = array(
            'meta_key'   => 'wp_comp_entry_competition-id',
            'meta_value' => $_REQUEST['comp'],
            'post_type'  => 'wp_comp_entries',
            'posts_per_page' => $winners, 
            'orderby' => 'rand'
        );
        $the_query = new WP_Query( $args );
        $winner = array();
        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $meta = get_post_meta( get_the_id() );
				if( isset( $meta['wp_comp_entry_first-name'][0] ) && isset( $meta['wp_comp_entry_last-name'][0] ) ) {
					$name = $meta['wp_comp_entry_first-name'][0].' '.$meta['wp_comp_entry_last-name'][0];
				} elseif( isset( $meta['wp_comp_entry_name'][0] ) ) {
					$name = $meta['wp_comp_entry_name'][0];
				}
                print_var($meta);
                $winner[] = array( 
                    'id'    => get_the_id(),
                    'name'  => $name,
                    'answer' => $meta['wp_comp_entry_wp_comp_answer'][0]
                );
            }
        }
        $winner = json_encode($winner);
        update_post_meta( $_REQUEST['comp'], 'wp_comp_winner', $winner );
        print_var($winner);
        wp_reset_postdata();
    }
    
    public function export_entries() {
        $fields = $this->option['form_fields'];
        foreach( $fields as $key=>$row ) {
            $sort[$key] = $row['field_order'];
        }
        array_multisort($sort, SORT_ASC, $fields);
        $csv = array();
        foreach( $fields as $field ) {
            $csv[0][] = $field['field_name'];             
        }
        $csv[0][] = 'Answer';
        $args = array(
            'meta_key'   => 'wp_comp_entry_competition-id',
            'meta_value' => $_REQUEST['comp'],
            'post_type'  => 'wp_comp_entries',
            'orderby' => 'rand'
        );
        $the_query = new WP_Query( $args );
        $i = 1; 
        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $meta = get_post_meta( get_the_id() );
                foreach( $fields as $field ) {
                    $field_name = sanitize_title( $field['field_name'] );
                    if( isset( $meta['wp_comp_entry_'.$field_name][0] ) ) {
                        $csv[$i][] = $meta['wp_comp_entry_'.$field_name][0];
                    } else {
                        $csv[$i][] = '';
                    }
                }
                $csv[$i][] = $meta['wp_comp_entry_wp_comp_answer'][0];
                $i++;
            }
        }
        wp_reset_postdata();
        $upload_dir = wp_upload_dir();
        $filename = 'comp_entries_'.$_REQUEST['comp'].'_'.date('Y-m-d');
        $fileloc = $upload_dir['baseurl'].'/entry_csvs/'.$filename.'.csv';
        $fp = fopen($upload_dir['basedir'].'/entry_csvs/'.$filename.'.csv', 'w');
        foreach ($csv as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        echo $fileloc;
        die();
    }
    
	public function load_shop_template($template) {
         if( is_post_type_archive( 'wp_comp_man' ) ) {
             if ( $overridden_template = locate_template( 'comp-archive.php' ) ) {
               load_template( $overridden_template );
             } else {
               load_template( dirname( __FILE__ ) . '/templates/archive.php' );
             }
         } else {
             return $template;
         }
    }
	
    private function run_plugin() {
	
    }
}
$comp_manager = WordPress_Competition_Manager::get_instance();