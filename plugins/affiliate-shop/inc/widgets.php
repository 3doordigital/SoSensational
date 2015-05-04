<?php
class aff_category_widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'tim_product', // Base ID
            __('Facted Nav - Categories', 'text_domain'), // Name
            array( 'description' => __( 'Shows the category list in the sidebar', 'text_domain' ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query;
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }
        if( isset( $wp_query->query_vars['shop-cat'] ) ) {
            $term = get_term_by( 'slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories' );
            $cat_id = $term->term_id;
        } elseif( isset( $_REQUEST['category'] )) {
			$term = get_term_by( 'slug', $_REQUEST['category'], 'wp_aff_categories' );
            $cat_id = $term->term_id;
		} else {
            $cat_id = 0;
        }
        echo '<div class="wp_aff_categories"><ul>';
		
		global $wp_aff;
        //$fn_include = $wp_aff->get_product_terms('wp_aff_categories');
		
		$walker = new Faceted_Category_Walker;
		$arg = array( 
			'depth' => 0, 
			'taxonomy' => 'wp_aff_categories', 
			'hide_empty' => 0, 
			'walker' => $walker, 
			'title_li' => '', 
			'orderby' => 'name', 
			'hierarchical' => 1, 
			'current_category' => $cat_id,
		);
		
        wp_list_categories( $arg );
		
        echo '</ul></div>';
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Filter by Category' ) );
            $title = $instance['title'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
}

class aff_brand_widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'tim_brand', // Base ID
            __('Facted Nav - Merchants', 'text_domain'), // Name
            array( 'description' => __( 'Shows the merchants list in the sidebar', 'text_domain' ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query;
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }
		
		global $wp_aff;
        $fn_include = $wp_aff->get_product_terms('wp_aff_brands');
		
        echo '<form action="'.admin_url('admin-post.php').'" id="wp_aff_brand_filter" method="POST">';
        echo '<div class="wp_aff_brands"><ul>';
            $walker = new Faceted_Brand_Walker;
            wp_list_categories( array( 'include' => $fn_include, 'depth' => 0, 'taxonomy' => 'wp_aff_brands', 'hide_empty' => 0, 'walker' => $walker, 'title_li' => '', 'orderby' => 'name', 'hierarchical' => 1 ) );
        echo '</ul></div>';
		echo '<input type="hidden" name="action"  value="wp_aff_brand_filter">';
	  	wp_nonce_field( 'wp_aff_brand_filter', '_wpnonce', true );
	  
		echo '</form>';
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Filter by Brand' ) );
            $title = $instance['title'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
}

class aff_price_widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'tim_price', // Base ID
            __('Facted Nav - Price', 'text_domain'), // Name
            array( 'description' => __( 'Shows the price widget in the sidebar', 'text_domain' ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query;
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        } 
		
		global $wp_aff;
		$arg = $wp_aff->shop_args( true );
		$arg['meta_query'] = null;
		$prods = new WP_Query( $arg );
		$prices = array();
		
		foreach( $prods->posts as $prod ) {
			$price = get_post_meta( $prod->ID, 'wp_aff_product_price', true );
			$price = str_replace( ',', '', $price );
			$prices[] = $price;
		}
		
		$prices = array_unique( $prices );
		$prices = array_filter( $prices );
		sort( $prices, SORT_NUMERIC );
		
		//print_var( $prices );
		if( isset( $prices[0] ) ) {
			$range = array(
					'min' 	=> number_format( $prices[0], 0, '.', '' ),
					'max'	=> number_format( end($prices), 0, '.', '' )
				);
		} else {
			$range = array(
					'min' 	=> 0,
					'max'	=> 0
				);
		}
		if( isset( $_REQUEST['price-min'] ) && isset( $_REQUEST['price-max'] ) ) {
			$range['start'] =  number_format( $_REQUEST['price-min'], 0, '.', '' );
			$range['end'] 	=  number_format( $_REQUEST['price-max'], 0, '.', '' );
		} else {
			$range['start'] = $range['min'];
			$range['end'] 	= $range['max'];
		}
		//print_var( $range );
		?>
    
    
	
    <div id="slider-range"></div>
    <div class="price_text">
    <form action="<?php echo admin_url('admin-post.php'); ?>" id="wp_aff_price_filter" method="POST">
      <input type="text" id="amount" readonly style="border:0;">
      <input type="hidden" id="price-min" name="price-min" value="75">
      <input type="hidden" id="price-max" name="price-max" value="300">
      <input type="hidden" name="action" value="wp_aff_price_filter">
	  <?php wp_nonce_field( 'wp_aff_price_filter', '_wpnonce', true ); ?>
	  <button type="submit" class="btn btn-default pull-right">Update</button>
	  </form>
    </div>
    <script type="text/javascript">
		var minPrice = <?php echo $range['min']; ?>;
		var maxPrice = <?php echo $range['max']; ?>;
		var valuesPrice = [ <?php echo $range['start']; ?>, <?php echo $range['end']; ?> ];
	</script>
    <?php
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Filter by Price' ) );
            $title = $instance['title'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
}

class aff_colour_widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'tim_colour', // Base ID
            __('Facted Nav - Colour', 'text_domain'), // Name
            array( 'description' => __( 'Shows the colour widget in the sidebar', 'text_domain' ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query;
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        } 
		echo '<form action="'.admin_url('admin-post.php').'" id="wp_aff_colour_filter" method="POST">';
		
		global $wp_aff;
        //$fn_include = $wp_aff->get_product_terms('wp_aff_colours');
		
		$arg =  array(
			//'include' => $fn_include,
            'show_count' => 0,
            'hierarchical' => 0,
            'taxonomy' => 'wp_aff_colours',
			'name' => 'wp_aff_colours',
			'hide_empty' => 1,
        );
		
		if( isset( $_REQUEST['colour'] ) ) 
			$arg['selected'] = $_REQUEST['colour'];
		
		$colours = get_categories( $arg );
		foreach( $colours as $colour ) {
			$colour_code = get_metadata('wp_aff_colours', $colour->term_id, 'colour_code', true);
			$colour_code_css = get_metadata('wp_aff_colours', $colour->term_id, 'colour_code_css', true);
			
			$checked = 0;
			if( isset( $_REQUEST['colour'] ) ) {
				$colours2 = explode( ',', $_REQUEST['colour'] );
				
				foreach( $colours2 as $colour2 ) {
					if( $colour2 == $colour->term_id ) {
						$checked ++;
					} 
				}
			}
			echo '<div class="'. ( $checked > 0 ? 'col-sel ' : '' ).'colour_filter">';
			if( $colour_code_css == '' ) {
				echo '<a title="'.$colour->name.'" href="'.$_SERVER['REQUEST_URI'].'" data-id="'.$colour->term_id.'" style="background-color: '.$colour_code.';"></a>';
			} else {
				echo '<a title="'.$colour->name.'" href="'.$_SERVER['REQUEST_URI'].'" data-id="'.$colour->term_id.'" style="'.$colour_code_css.'"></a>';
			}
			
			echo '<input type="checkbox" '.( $checked > 0 ? 'checked' : '' ).' data-id="'.$colour->term_id.'" name="wp_aff_colours[]" class="hide_check" value="'.$colour->term_id.'">';
			echo '</div>';
		}
		
		echo '<input type="hidden" name="action" value="wp_aff_colour_filter">';
		wp_nonce_field( 'wp_aff_colour_filter', '_wpnonce', true );
		echo '</form>';
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Filter by Colour' ) );
            $title = $instance['title'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
}

class aff_size_widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'tim_size', // Base ID
            __('Facted Nav - Size', 'text_domain'), // Name
            array( 'description' => __( 'Shows the size widget in the sidebar', 'text_domain' ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query;
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        } 
		echo '<form action="'.admin_url('admin-post.php').'" id="wp_aff_size_filter" method="POST">';
		
		global $wp_aff;
        //$fn_include = $wp_aff->get_product_terms('wp_aff_sizes');
		$arg = array(
			//'include' => $fn_include,
            'show_count' => 0,
            'hierarchical' => 0,
            'taxonomy' => 'wp_aff_sizes',
			'name' => 'wp_aff_sizes',
            'class' => 'form-control wp_aff_sizes_select',
            'show_option_none' => 'All Sizes'
        );
		
		$sizes = get_categories( $arg );
		if( $fn_include != 'none' ) {
			foreach( $sizes as $size ) {
				$checked = 0;
				if( isset( $_REQUEST['size'] ) ) {
					$sizes2 = explode( ',', $_REQUEST['size'] );
					foreach( $sizes2 as $size2 ) {
						if( $size2 == $size->term_id ) {
							$checked ++;
						} 
					}
				}
				echo '<div class="'. ( $checked > 0 ? 'col-sel ' : '' ).'size_filter">';
				echo '<a href="'.$_SERVER['REQUEST_URI'].'" data-id="'.$size->term_id.'">'.ltrim( $size->name, '0' ).'</a>';
				echo '<input type="checkbox" '.( $checked > 0 ? 'checked' : '' ).' data-id="'.$size->term_id.'" name="wp_aff_sizes[]" class="hide_check" value="'.$size->term_id.'">';
				echo '</div>';
			}
		} else {
			echo '<p>No sizes available with the current filters</p>';
		}
		echo '<input type="hidden" name="action" value="wp_aff_size_filter">';
		wp_nonce_field( 'wp_aff_size_filter', '_wpnonce', true );
		echo '</form>';
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Filter by Size' ) );
            $title = $instance['title'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
}

class aff_sale_widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'tim_sale', // Base ID
            __('Facted Nav - Sale', 'text_domain'), // Name
            array( 'description' => __( 'Shows the sale/new/top picks checkboxes', 'text_domain' ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query;
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        } 
		echo '<form action="'.admin_url('admin-post.php').'" id="wp_aff_sale_filter" method="POST">';
		
		if( isset( $_REQUEST['options'] ) ) {
			$options = explode( ',', $_REQUEST['options'] );
			$opt = array();
			foreach( $options as $option ) {
				$opt[$option] = '1';	
			}
		}
		
		echo '<p class="checkbox"><label><input type="checkbox" ';
		if( ( isset( $wp_query->query_vars['shop-option'] ) && $wp_query->query_vars['shop-option'] == 'new' ) || isset( $opt['new'] ) ) {
			echo ' checked ';
		} else {
			echo ''; 
		}
		echo ' value="';
		if( isset( $wp_query->query_vars['shop-option'] ) || isset( $wp_query->query_vars['shop-cat'] ) || isset( $wp_query->query_vars['shop-brand'] ) || ( count( $_GET ) > 0 ) ) {
			echo '2';
		} else {
			echo '1';
		}
		echo '" ';
		echo 'name="wp_aff_new_in"> <i class="fa fa-calendar fa-fw"></i> New In</label></p>';
		
		echo '<p class="checkbox"><label><input type="checkbox" ';
		if( ( isset( $wp_query->query_vars['shop-option'] ) && $wp_query->query_vars['shop-option'] == 'sale' ) || isset( $opt['sale'] ) ) {
			echo ' checked ';
		} else {
			echo ''; 
		}
		echo ' value="';
		if( isset( $wp_query->query_vars['shop-option'] ) || isset( $wp_query->query_vars['shop-cat'] ) || isset( $wp_query->query_vars['shop-brand'] ) || ( count( $_GET ) > 0 ) ) {
			echo '2';
		} else {
			echo '1';
		}
		echo '" ';
		echo 'name="wp_aff_sale"> <i class="fa fa-shopping-cart fa-fw"></i> Sale</label></p>';
		
		echo '<p class="checkbox"><label><input type="checkbox" ';
		if( ( isset( $wp_query->query_vars['shop-option'] ) && $wp_query->query_vars['shop-option'] == 'picks' ) || isset( $opt['picks'] ) ){
			echo ' checked ';
		} else {
			echo ''; 
		}
		echo ' value="';
		if( isset( $wp_query->query_vars['shop-option'] ) || isset( $wp_query->query_vars['shop-cat'] ) || isset( $wp_query->query_vars['shop-brand'] ) || ( count( $_GET ) > 0 ) ) {
			echo '2';
		} else {
			echo '1';
		}
		echo '" ';
		echo 'name="wp_aff_toppicks"> <i class="fa fa-heart fa-fw"></i> Our Picks</label></p>';
		
		echo '<input type="hidden" name="action" value="wp_aff_sale_filter">';
		wp_nonce_field( 'wp_aff_sale_filter', '_wpnonce', true );
		echo '</form>';
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Options' ) );
            $title = $instance['title'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
}

class aff_active_widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'tim_active', // Base ID
            __('Facted Nav - Active Filters', 'text_domain'), // Name
            array( 'description' => __( 'Shows the active filters in the sidebar', 'text_domain' ), ) // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $wp_query;
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        } 
        $output = '';
        if( isset( $wp_query->query_vars['shop-cat'] ) ) {
            $term = get_term_by( 'slug', $wp_query->query_vars['shop-cat'], 'wp_aff_categories' );
			//print_var($term);
            $cat_id = $term->term_id;
            $term_title = 'Category';
			$output .= '<a href="'.$_SERVER['REQUEST_URI'].'" class="btn btn-default remove filter" data-term="'.$term->slug.'" data-type="category">'.$term->name.' <i class="fa fa-times-circle pull-right"></i> </a>';
        } elseif( isset( $wp_query->query_vars['shop-brand'] ) ) {
            $term = get_term_by( 'slug', $wp_query->query_vars['shop-brand'], 'wp_aff_brands' );
            $cat_id = $term->term_id;
            $term_title = 'Brand';
			$output .= '<a href="'.$_SERVER['REQUEST_URI'].'" class="btn btn-default remove filter" data-term="'.$term->term_id.'" data-type="brand">'.$term->name.' <i class="fa fa-times-circle pull-right"></i> </a>';
        } 
		if ( isset ( $_REQUEST['brand'] ) ) {
			$brands = explode( ',' , $_REQUEST['brand'] );
			foreach( $brands as $brand ) {
				$term = get_term_by( 'slug', $brand, 'wp_aff_brands' );
				$cat_id = $term->term_id;
				$term_title = 'Brand';
				$output .= '<a href="'.$_SERVER['REQUEST_URI'].'" class="btn btn-default remove filter" data-term="'.$term->term_id.'" data-type="brand">'.$term->name.' <i class="fa fa-times-circle pull-right"></i> </a>';  
			} 
		} 
		if ( isset( $_REQUEST['category'] ) ) {
			$category = get_term_by( 'slug', $_REQUEST['category'], 'wp_aff_categories' );
			$output .= '<a href="'.$_SERVER['REQUEST_URI'].'" class="btn btn-default remove filter" data-term="'.$category->term_id.'" data-type="category">'.$category->name.' <i class="fa fa-times-circle pull-right"></i> </a>'; 
		}
		
		if( isset( $_REQUEST['size'] ) && $_REQUEST['size'] != -1 ) {
			$sizes = explode(',', $_REQUEST['size'] );
			foreach( $sizes as $size ) {
				$size = get_term_by( 'id', $size, 'wp_aff_sizes' );
				$output .= '<a href="'.$_SERVER['REQUEST_URI'].'" class="btn btn-default remove filter" data-term="'.$size->term_id.'" data-type="size">'.$size->name.' <i class="fa fa-times-circle pull-right"></i> </a>';  
			}
		}
		
		if( isset( $_REQUEST['colour'] ) && $_REQUEST['colour'] != -1 ) {
			$sizes = explode(',', $_REQUEST['colour'] );
			foreach( $sizes as $size ) {
				$size = get_term_by( 'id', $size, 'wp_aff_colours' );
				$output .= '<a href="'.$_SERVER['REQUEST_URI'].'" class="btn btn-default remove filter" data-term="'.$size->term_id.'" data-type="colour">'.$size->name.' <i class="fa fa-times-circle pull-right"></i> </a>';  
			}
		}
		
		if( isset( $_REQUEST['price-min'] ) && isset( $_REQUEST['price-max'] ) ) {
			$output .= '<a href="'.$_SERVER['REQUEST_URI'].'" class="btn btn-default remove filter" data-type="price">£'.$_REQUEST['price-min'].' - £'.$_REQUEST['price-max'].' <i class="fa fa-times-circle pull-right"></i></a>';   
		}
			if( $output != '' ) {
				echo $output;
				echo '<a href="/shop/" class="btn btn-default">Clear All Filters</a>';
			} else {
				echo '<p>No filters selected.</p>';	
			}
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Active Filters' ) );
            $title = $instance['title'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
}