<?php
    
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WP_Terms_List_Tables extends WP_List_Table {

    private $level;

	public $callback_args;

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		global $post_type, $taxonomy, $action, $tax;

		parent::__construct( array(
			'plural' => 'tags',
			'singular' => 'tag',
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
        $this->screen->taxonomy = $args['taxonomy'];
        $this->screen->post_type = 'wp_aff_products';
		
		$this->out = array();
		$action    = $this->screen->action;
		$post_type = $this->screen->post_type;
		$taxonomy  = $this->screen->taxonomy;

		

		$tax = get_taxonomy( $taxonomy );

		// @todo Still needed? Maybe just the show_ui part.
		

	}

	public function ajax_user_can() {
		return current_user_can( get_taxonomy( $this->screen->taxonomy )->cap->manage_terms );
	}

	public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
		$tags_per_page = 99999999999;

		if ( 'post_tag' == $this->screen->taxonomy ) {
			/**
			 * Filter the number of terms displayed per page for the Tags list table.
			 *
			 * @since 2.8.0
			 *
			 * @param int $tags_per_page Number of tags to be displayed. Default 20.
			 */
			$tags_per_page = apply_filters( 'edit_tags_per_page', $tags_per_page );

			/**
			 * Filter the number of terms displayed per page for the Tags list table.
			 *
			 * @since 2.7.0
			 * @deprecated 2.8.0 Use edit_tags_per_page instead.
			 *
			 * @param int $tags_per_page Number of tags to be displayed. Default 20.
			 */
			$tags_per_page = apply_filters( 'tagsperpage', $tags_per_page );
		} elseif ( 'category' == $this->screen->taxonomy ) {
			/**
			 * Filter the number of terms displayed per page for the Categories list table.
			 *
			 * @since 2.8.0
			 *
			 * @param int $tags_per_page Number of categories to be displayed. Default 20.
			 */
			$tags_per_page = apply_filters( 'edit_categories_per_page', $tags_per_page );
		}

		$search = !empty( $_REQUEST['s'] ) ? trim( wp_unslash( $_REQUEST['s'] ) ) : '';

		$args = array(
			'search' => $search,
			'page' => $this->get_pagenum(),
			'number' => $tags_per_page,
		);

		if ( !empty( $_REQUEST['orderby'] ) )
			$args['orderby'] = trim( wp_unslash( $_REQUEST['orderby'] ) );

		if ( !empty( $_REQUEST['order'] ) )
			$args['order'] = trim( wp_unslash( $_REQUEST['order'] ) );

		$this->callback_args = $args;

		$this->set_pagination_args( array(
			'total_items' => wp_count_terms( $this->screen->taxonomy, compact( 'search' ) ),
			'per_page' => $tags_per_page,
		) );
	}

	public function has_items() {
		// todo: populate $this->items in prepare_items()
		return true;
	}

	protected function get_bulk_actions() {
		$actions = array();
		$actions['delete'] = __( 'Delete' );

		return $actions;
	}

	public function current_action() {
		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['delete_tags'] ) && ( 'delete' == $_REQUEST['action'] || 'delete' == $_REQUEST['action2'] ) )
			return 'bulk-delete';

		return parent::current_action();
	}

	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => _x( 'Name', 'term name' ),
			'slug'        => __( 'Slug' ),
			'alias'		  => __( 'Alias of' ),
            'new_in'      => 'New In',
            'posts'       => _x( 'Products', 'Number/count of items' ),
			'view'       => __( '', '' )
		);

		return $columns;
	}

	protected function get_sortable_columns() {
		return array(
			'name'    	=> array('name', false),
			'slug'    	=> array('slug', false),
			'alias'		=> array('alias', false),
            'new_in'      => 'New In',
			'posts'     => array('count', false)
        );
	}

	public function display_rows_or_placeholder() {
		$taxonomy = $this->screen->taxonomy;

		$args = wp_parse_args( $this->callback_args, array(
			'page' => 1,
			'number' => 20,
			'search' => '',
			'hide_empty' => 0
		) );

		$page = $args['page'];

		// Set variable because $args['number'] can be subsequently overridden.
		$number = $args['number'];

		$args['offset'] = $offset = ( $page - 1 ) * $number;

		// Convert it to table rows.
		$count = 0;

		if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $args['orderby'] ) ) {
			// We'll need the full set of terms then.
			$args['number'] = $args['offset'] = 0;
		}
		$terms = get_terms( $taxonomy, $args );
		if ( empty( $terms ) ) {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
			return;
		}

		if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $args['orderby'] ) ) {
			if ( ! empty( $args['search'] ) ) {// Ignore children on searches.
				$children = array();
			} else {
				$children = _get_term_hierarchy( $taxonomy );
			}
			// Some funky recursion to get the job done( Paging & parents mainly ) is contained within, Skip it for non-hierarchical taxonomies for performance sake
			$this->_rows( $taxonomy, $terms, $children, $offset, $number, $count );
		} else {
			$terms = get_terms( $taxonomy, $args );
			foreach ( $terms as $term ) {
				$this->single_row( $term );
			}
		}
	}

	/**
	 * @param string $taxonomy
	 * @param array $terms
	 * @param array $children
	 * @param int $start
	 * @param int $per_page
	 * @param int $count
	 * @param int $parent
	 * @param int $level
	 */
	private function _rows( $taxonomy, $terms, &$children, $start, $per_page, &$count, $parent = 0, $level = 0 ) {

		$end = $start + $per_page;

		foreach ( $terms as $key => $term ) {

			if ( $count >= $end )
				break;

			if ( $term->parent != $parent && empty( $_REQUEST['s'] ) )
				continue;

			// If the page starts in a subtree, print the parents.
			if ( $count == $start && $term->parent > 0 && empty( $_REQUEST['s'] ) ) {
				$my_parents = $parent_ids = array();
				$p = $term->parent;
				while ( $p ) {
					$my_parent = get_term( $p, $taxonomy );
					$my_parents[] = $my_parent;
					$p = $my_parent->parent;
					if ( in_array( $p, $parent_ids ) ) // Prevent parent loops.
						break;
					$parent_ids[] = $p;
				}
				unset( $parent_ids );

				$num_parents = count( $my_parents );
				while ( $my_parent = array_pop( $my_parents ) ) {
					echo "\t";
					$this->single_row( $my_parent, $level - $num_parents );
					$num_parents--;
				}
			}

			if ( $count >= $start ) {
				echo "\t";
                //echo print_r($term, true).' '.$level.'<br>';
				$this->single_row( $term, $level );
			}

			++$count;

			unset( $terms[$key] );
            
			if ( isset( $children[$term->term_id] ) && empty( $_REQUEST['s'] ) )
				$this->_rows( $taxonomy, $terms, $children, $start, $per_page, $count, $term->term_id, $level + 1 );
		}
        
	}

	/**
	 * @global string $taxonomy
	 * @staticvar string $row_class
	 * @param object $tag
	 * @param int $level
	 */
	public function single_row( $tag, $level = 0 ) {
		global $taxonomy;
 		$tag = sanitize_term( $tag, $taxonomy );

		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );
        
        
		$this->level = $level;
		echo '<tr id="tag-' . $tag->term_id . '"' . $row_class . '>';
		$this->single_row_columns( $tag );
		echo '</tr>';
	}
    protected function single_row_columns( $item ) {
        
		list( $columns, $hidden ) = $this->get_column_info();
		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			}
			elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo "</td>";
			}
			else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo "</td>";
			}
		}
	}
    protected function get_column_info() {
		if ( isset( $this->_column_headers ) )
			return $this->_column_headers;
		$columns = get_column_headers( $this->screen );
		$hidden = get_hidden_columns( $this->screen );

		$sortable_columns = $this->get_sortable_columns();
		/**
		 * Filter the list table sortable columns for a specific screen.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @since 3.5.0
		 *
		 * @param array $sortable_columns An array of sortable columns.
		 */
		$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $sortable_columns );
		$sortable = array();
		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}
		$this->_column_headers = array( $columns, $hidden, $sortable );

		return $this->_column_headers;
	}

    private function column_new_in($term)
    {
        $output = '';
        $meta = get_post_meta($term->term_id, 'wp_aff_category_new_in', true);

        if (!$meta || $meta === 0) {
            $output .= '<a href="#" class="ajax_new_in" data-item="'.$term->term_id.'" data-action="new_in"><i class="fa fa-calendar fa-fw fa-lg"></i></i></a> ';
        } else {
            $output .= '<a href="#" class="ajax_new_in active" data-item="'.$term->term_id.'" data-action="new_in"><i class="fa fa-calendar fa-fw fa-lg"></i></i></a> ';
        }


        return $output;

    }


	/**
	 * @param object $tag
	 * @return string
	 */
	public function column_cb( $tag ) {
		$default_term = get_option( 'default_' . $this->screen->taxonomy );

		if ( current_user_can( get_taxonomy( $this->screen->taxonomy )->cap->delete_terms ) && $tag->term_id != $default_term )
			return '<label class="screen-reader-text" for="cb-select-' . $tag->term_id . '">' . sprintf( __( 'Select %s' ), $tag->name ) . '</label>'
				. '<input type="checkbox" name="delete_tags[]" value="' . $tag->term_id . '" id="cb-select-' . $tag->term_id . '" />';

		return '&nbsp;';
	}
	public function rec_parent( $tag ) {
		//$this->out[] = $tag->name;
		//print_var($tag);
		if( $tag->parent != 0 ) {
			$parent = get_term_by( 'id', $tag->parent, $this->screen->taxonomy );
			//print_var($parent);
			$this->out[] = '<a href="'.$_SERVER['REQUEST_URI'].'#tag-'.$parent->term_id.'">'.$parent->name.'</a> &raquo; ';
			if( $parent->parent != 0 ) {
				$this->rec_parent( $parent );	
			}
		}
		return $this->out;
	}
	public function column_alias( $tag ) {
		$this->out = array();
		if( $alias = get_term_by( 'id', $tag->term_group, $this->screen->taxonomy ) ) {
			
			$out = $this->rec_parent( $alias );
			
			if (is_array($out)) {
                $out = array_reverse( $out );
            }
			$out[] = '<a href="'.$_SERVER['REQUEST_URI'].'#tag-'.$alias->term_id.'">'.$alias->name.'</a>';
			return implode( '', $out );
		} else {
			return '';	
		}
		
	}
	
	public function column_view( $tag ) {
		//print_var( $tag );	
		
		return '<a href="/shop/'.$tag->slug.'/" target="_blank" class="button button-secondary">View Category</a>';
	}
	
	/**
	 * @param object $tag
	 * @return string
	 */
	public function column_name( $tag ) {
		$taxonomy = $this->screen->taxonomy;
		$tax = get_taxonomy( $taxonomy );

		$default_term = get_option( 'default_' . $taxonomy );

		$pad = str_repeat( '&#8212; ', max( 0, $this->level ) );

		/**
		 * Filter display of the term name in the terms list table.
		 *
		 * The default output may include padding due to the term's
		 * current level in the term hierarchy.
		 *
		 * @since 2.5.0
		 *
		 * @see WP_Terms_List_Table::column_name()
		 *
		 * @param string $pad_tag_name The term name, padded if not top-level.
		 * @param object $tag          Term object.
		 */
		$name = apply_filters( 'term_name', $pad . ' ' . $tag->name, $tag );

		$qe_data = get_term( $tag->term_id, $taxonomy, OBJECT, 'edit' );
		
		if( $taxonomy == 'wp_aff_colours' ) {
			$urlend = '/colours';
		} elseif( $taxonomy == 'wp_aff_sizes' ) {
			$urlend = '/sizes';
		} else {
			$urlend = '';
		}
		
        $editurl = sprintf('admin.php?page=%1$s&action=%2$s&%3$s=%4$s', 'affiliate-shop'.$urlend, 'edit', $taxonomy, $tag->term_id);
        $edit_link = esc_url( admin_url( $editurl ) );
        
        $viewurl = sprintf('admin.php?page=%1$s&action=%2$s&%3$s=%4$s', 'affiliate-shop', 'view', $taxonomy, $tag->term_id);
        $view_link = esc_url( admin_url( $viewurl ) );

		$out = '<strong><a class="row-title" href="' . $view_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $name ) ) . '">' . $name . '</a></strong><br />';

		$actions = array();
		if ( current_user_can( $tax->cap->edit_terms ) ) {
			$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
		}
		if ( current_user_can( $tax->cap->delete_terms ) && $tag->term_id != $default_term )
			$actions['delete'] = "<a class='delete-tag' href='" . wp_nonce_url( "edit-tags.php?action=delete&amp;taxonomy=$taxonomy&amp;tag_ID=$tag->term_id", 'delete-tag_' . $tag->term_id ) . "'>" . __( 'Delete' ) . "</a>";
		if ( $tax->public )
			$actions['view'] = '<a href="' . get_term_link( $tag ) . '">' . __( 'View' ) . '</a>';

		/**
		 * Filter the action links displayed for each term in the Tags list table.
		 *
		 * @since 2.8.0
		 * @deprecated 3.0.0 Use {$taxonomy}_row_actions instead.
		 *
		 * @param array  $actions An array of action links to be displayed. Default
		 *                        'Edit', 'Quick Edit', 'Delete', and 'View'.
		 * @param object $tag     Term object.
		 */
		$actions = apply_filters( 'tag_row_actions', $actions, $tag );

		/**
		 * Filter the action links displayed for each term in the terms list table.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $actions An array of action links to be displayed. Default
		 *                        'Edit', 'Quick Edit', 'Delete', and 'View'.
		 * @param object $tag     Term object.
		 */
		$actions = apply_filters( "{$taxonomy}_row_actions", $actions, $tag );

		$out .= $this->row_actions( $actions );
		$out .= '<div class="hidden" id="inline_' . $qe_data->term_id . '">';
		$out .= '<div class="name">' . $qe_data->name . '</div>';

		/** This filter is documented in wp-admin/edit-tag-form.php */
		$out .= '<div class="slug">' . apply_filters( 'editable_slug', $qe_data->slug ) . '</div>';
		$out .= '<div class="parent">' . $qe_data->parent . '</div></div>';

		return $out;
	}

	/**
	 * @param object $tag
	 * @return string
	 */
	public function column_description( $tag ) {
		return $tag->description;
	}

	/**
	 * @param object $tag
	 * @return string
	 */
	public function column_slug( $tag ) {
		/** This filter is documented in wp-admin/edit-tag-form.php */
		return $tag->slug ;
	}

	/**
	 * @param object $tag
	 * @return string
	 */
	public function column_posts( $tag ) {
		$count = number_format_i18n( $tag->count );

		$tax = get_taxonomy( $this->screen->taxonomy );

		$ptype_object = get_post_type_object( $this->screen->post_type );
		if ( ! $ptype_object->show_ui )
			return $count;

		if ( $tax->query_var ) {
			$args = array( $tax->query_var => $tag->slug );
		} else {
			$args = array( 'taxonomy' => $tax->name, 'term' => $tag->slug );
		}

        $viewurl = sprintf('admin.php?page=%1$s&action=%2$s&%3$s=%4$s', 'affiliate-shop', 'view', $this->screen->taxonomy, $tag->term_id);
        $view_link = esc_url( admin_url( $viewurl ) );
		
        return "<a href='" . $viewurl . "'>$count</a>";
        
	}

	/**
	 * @param object $tag
	 * @return string
	 */
	public function column_links( $tag ) {
		$count = number_format_i18n( $tag->count );
		if ( $count )
			$count = "<a href='link-manager.php?cat_id=$tag->term_id'>$count</a>";
		return $count;
	}

	/**
	 * @param object $tag
	 * @param string $column_name
	 * @return string
	 */
	public function column_default( $tag, $column_name ) {
		/**
		 * Filter the displayed columns in the terms list table.
		 *
		 * The dynamic portion of the hook name, `$this->screen->taxonomy`,
		 * refers to the slug of the current taxonomy.
		 *
		 * @since 2.8.0
		 *
		 * @param string $string      Blank string.
		 * @param string $column_name Name of the column.
		 * @param int    $term_id     Term ID.
		 */
		return apply_filters( "manage_{$this->screen->taxonomy}_custom_column", '', $column_name, $tag->term_id );
	}

	public function inline_edit() {
		$tax = get_taxonomy( $this->screen->taxonomy );

		print_var($tax);
?>

	<form method="get" action=""><table style="display: none"><tbody id="inlineedit">
		<tr id="inline-edit" class="inline-edit-row" style="display: none"><td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">

			<fieldset><div class="inline-edit-col">
				<h4><?php _e( 'Quick Edit' ); ?></h4>

				<label>
					<span class="title"><?php _ex( 'Name', 'term name' ); ?></span>
					<span class="input-text-wrap"><input type="text" name="name" class="ptitle" value="" /></span>
				</label>
	<?php if ( !global_terms_enabled() ) { ?>
				<label>
					<span class="title"><?php _e( 'Slug' ); ?></span>
					<span class="input-text-wrap"><input type="text" name="slug" class="ptitle" value="" /></span>
				</label>
	<?php } ?>
			</div></fieldset>
	<?php

		$core_columns = array( 'cb' => true, 'description' => true, 'name' => true, 'slug' => true, 'posts' => true );

		list( $columns ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			if ( isset( $core_columns[$column_name] ) )
				continue;

			/** This action is documented in wp-admin/includes/class-wp-posts-list-table.php */
			do_action( 'quick_edit_custom_box', $column_name, 'edit-tags', $this->screen->taxonomy );
		}

	?>

		<p class="inline-edit-save submit">
			<a accesskey="c" href="#inline-edit" class="cancel button-secondary alignleft"><?php _e( 'Cancel' ); ?></a>
			<a accesskey="s" href="#inline-edit" class="save button-primary alignright"><?php echo $tax->labels->update_item; ?></a>
			<span class="spinner"></span>
			<span class="error" style="display:none;"></span>
			<?php wp_nonce_field( 'taxinlineeditnonce', '_inline_edit', false ); ?>
			<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $this->screen->taxonomy ); ?>" />
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $this->screen->post_type ); ?>" />
			<br class="clear" />
		</p>
		</td></tr>
		</tbody></table></form>
	<?php
	}
	
}

class ProductTable extends WP_List_Table {
    
    
    
    function __construct( ){
        global $status, $page;
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'product',     //singular name of the listed records
            'plural'    => 'products',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default($item, $column_name){
        return $item[$column_name];
    }

    function column_title($item){
        
        //Build row actions
		
        $actions = array(
            'edit'      => sprintf('<a href="?page=affiliate-shop/products&action=%s&product=%s">Edit</a>','edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&product=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        //Return the title contents
        return sprintf('<strong><a href="?page=affiliate-shop/products&action=edit&product=%1$s">%2$s</a></strong>%4$s',
            /*$1%s*/ $item['ID'],
            /*$2%s*/ $item['title'],
                     $_REQUEST['page'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    function column_img($item) {
       return sprintf(
            '<img src="%1$s" style="max-height: 90px; width: auto;" />',
            /*$1%s*/ $item['img']  //Let's simply repurpose the table's singular label ("movie")
        ); 
    }

    function column_desc($item) {
        if(strlen($item['desc']) < 100) {
            return $item['desc'];
        } else {
            return substr($item['desc'], 0, 100).'...';   
        }
    }
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'img'     => 'Image',
            'title'     => 'Title',
            'brands'     => 'Brand',
            'colours'     => 'Colours',
            'sizes'     => 'Sizes',
            'price'    => 'Price'
            
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
            'price'    => array('price',false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        $per_page = 20;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $this->process_bulk_action();
        
        $args = array(
            'post_type' => 'wp_aff_products',
            'tax_query' => array(
                array(
                    'taxonomy' => 'wp_aff_categories',
                    'field'    => 'id',
                    'terms'    => $_REQUEST['wp_aff_categories'],
                ),
            ),
        );
        $query = new WP_Query( $args );
        
        $data = array();
        $i = 0;
        foreach($query->posts AS $post) {
            
            $post_meta = get_post_meta($post->ID);
            $colours = wp_get_post_terms( $post->ID, 'wp_aff_colours' );
            $sizes = wp_get_post_terms( $post->ID, 'wp_aff_sizes' );
            $brands = wp_get_post_terms( $post->ID, 'wp_aff_brands' );
            
			$sizes = (array) $sizes;
			$colours = (array) $colours;
			$brands = (array) $brands;
			
            $prod_data = array();
            $prod_data['colours'] = array();
			$prod_data['sizes'] = array();
			$prod_data['brands'] = array();
			
            foreach( $colours AS $colour ) {
                $prod_data['colours'][] = $colour->name;
            }
            foreach( $sizes AS $size ) {
                $prod_data['sizes'][] = $size->name;
            }
            foreach( $brands AS $brand ) {
                $prod_data['brands'][] = $brand->name;
            }
            //print_var($prod_data);
            $colours = implode( ', ', $prod_data['colours']);
            $sizes = implode( ', ', $prod_data['sizes']);
            $brands = implode( ', ', $prod_data['brands']);
            
            //print_var($terms);
            
            $data[$i] = array(
                'ID'    => $post->ID,
                'title' => $post->post_title,
                'img' => $post_meta['wp_aff_product_image'][0],
                'colours' => $colours,
                'sizes' => $sizes,
                'brands' => $brands,
                'price' => $post_meta['wp_aff_product_price'][0],
                'link' => $post_meta['wp_aff_product_link'][0],
            );
            
            $i++;
        }
        //print_var($data);
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'DESC'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}

class AllProductTable extends WP_List_Table {
    
    
    
    function __construct( ){
        global $status, $page;
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'product',     //singular name of the listed records
            'plural'    => 'products',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default($item, $column_name){
        return $item[$column_name];
    }
	
	function column_price( $item ) {
		if( $item['rrp'] <= $item['price'] ) {
			$output = $item['price'];
		} else {
			$output = '(<strike>'.$item['rrp'].'</strike>) '.$item['price'];
		}
		return $output;
	}
	
    function column_title($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&product=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&product=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        //Return the title contents
        return sprintf('<strong><a href="?page=%3$s&action=edit&product=%1$s">%2$s</a></strong>%4$s',
            /*$1%s*/ $item['ID'],
            /*$2%s*/ $item['title'],
                     $_REQUEST['page'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    function column_img($item) {
       return sprintf(
            '<img src="%1$s" style="max-height: 90px; width: auto;" />',
            /*$1%s*/ $item['img']  //Let's simply repurpose the table's singular label ("movie")
        ); 
    }
	function column_stickers( $item ) {
		if( $item['sale'] == 1 ) {
			$output = '<a href="#" class="active" data-item="'.$item['ID'].'" data-action="sale"><i class="fa fa-shopping-cart fa-fw fa-lg"></i></a> ';
		} else {
			$output = '<a href="#" class="" data-item="'.$item['ID'].'" data-action="sale"><i class="fa fa-shopping-cart fa-fw fa-lg"></i></a> ';	
		}
		
		if( $item['picks'] == 1 ) {
			$output .= '<a href="#" class="active ajax_sticker" data-item="'.$item['ID'].'" data-action="picks"><i class="fa fa-heart fa-fw fa-lg"></i></i></a> ';
		} else {
			$output .= '<a href="#" class="ajax_sticker" data-item="'.$item['ID'].'" data-action="picks"><i class="fa fa-heart fa-fw fa-lg"></i></i></a> ';
		}

        $isProductNewIn = get_post_meta($item['ID'], 'wp_aff_product_new_in', true);

		if($isProductNewIn == 1) {
			$output .= '<a href="#" class="ajax_new_in_single_product active" data-item="'.$item['ID'].'" data-action="new"><i class="fa fa-calendar fa-fw fa-lg"></i></a>';
		} else {
			$output .= '<a href="#" class="ajax_new_in_single_product" data-item="'.$item['ID'].'" data-action="new_in_single_product"><i class="fa fa-calendar fa-fw fa-lg"></i></a>';
		}

		return $output;
	}
    function column_desc($item) {
        if(strlen($item['desc']) < 100) {
            return $item['desc'];
        } else {
            return substr($item['desc'], 0, 100).'...';   
        }
    }
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ 'product',  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'img'     => 'Image',
            'title'     => 'Title',
			'stickers'	=> '',
			'cats'		=> 'Categories',
            'brands'     => 'Brand',
            'colours'     => 'Colours',
            'sizes'     => 'Sizes',
            'price'    => 'Price'
            
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
            'price'    => array('price',false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            //wp_die('Items deleted (or they would be if we had items to delete)!');
			$products = array();
			if( isset( $_REQUEST['product'] ) ) {
				if( !is_array( $_REQUEST['product'] ) ) {
					$products[] = $_REQUEST['product'];
				} else {
					$products = $_REQUEST['product'];
				}
				foreach( $products as $product ) { 
					wp_trash_post( $product );
				}
			}
        }
        
    }
	
	function extra_tablenav( $which ) {
    if ( $which == "top" ){
        ?>
        <div class="alignleft actions bulkactions">
        
            <select name="prod_type_filter" class="prod_filter" id="type">
                <option <?php echo ( !isset( $_GET['prod_type'] ) || $_GET['prod_type'] == 0 ? ' selected ' : '' ); ?> value="0">All Products</option>
                <option <?php echo ( isset( $_GET['prod_type'] ) && $_GET['prod_type'] == 1 ? ' selected ' : '' ); ?> value="1">Affiliate Feed Products</option>
                <option <?php echo ( isset( $_GET['prod_type'] ) && $_GET['prod_type'] == 2 ? ' selected ' : '' ); ?> value="2">Manual Products</option>
            </select>
          
        </div>
        <div class="alignleft actions bulkactions">
        
            <?php 
				if( isset( $_REQUEST['prod_category'] ) ) {
					$selected = $_REQUEST['prod_category'];
				} else {
					$selected = 0;	
				}
				wp_dropdown_categories(
					array(
						'taxonomy' => 'wp_aff_categories', 
						'hide_empty' => 1,
						'selected' => $selected, 
						'name' => 'product_cat_filter',
						'class' => 'prod_filter',
						'id' => 'category',
						'orderby' => 'name', 
						'hierarchical' => true, 
						'show_option_none' => __('All Categories')
					)
				); 
			?>
          
        </div>
        <div class="alignleft actions bulkactions">
        
            <?php 
				if( isset( $_REQUEST['prod_brand'] ) ) {
					$selected = $_REQUEST['prod_brand'];
				} else {
					$selected = 0;	
				}
        		
				wp_dropdown_categories(
					array(
						'taxonomy' => 'wp_aff_brands', 
						'hide_empty' => 1, 
						'selected' => $selected, 
						'name' => 'product_brand_filter',
						'class' => 'prod_filter',
						'id' => 'brand',
						'orderby' => 'name', 
						'hierarchical' => true, 
						'show_option_none' => __('All Brands')
					)
				); 
			?>
          
        </div>
        <!--
        <div class="alignleft actions bulkactions">
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="get">
            <input type="text" placeholder="Search" /> <button type="submit" class="prod_search_filter_sub button button-secondary">Search</button>
            <input type="hidden" value="wp_aff_add_category" name="action" />
            <?php wp_nonce_field( 'wp_aff_add_category', '_wpnonce', TRUE ); ?>
        </form>  
        </div>
        -->
        <?php
    }
    if ( $which == "bottom" ){
        //The code that goes after the table is there

    }
}

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        $per_page = 20;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
		$current_page = $this->get_pagenum();
        
		$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to title
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'DESC'; //If no order, default to asc
        
		$this->process_bulk_action();
        $paged = ( $current_page ) ? $current_page : 1;
		$offset = ( $paged - 1 ) * $per_page;
        $args = array(
            'post_type' => 'wp_aff_products',
			//'post_status' => 'all',
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'orderby' => $orderby,
			'order' => $order,
			'tax_query' => array( 'relation' => 'AND' ),
			'meta_query' => array( 'relation' => 'AND' ),
        );
		if( isset( $_REQUEST['prod_type'] ) ) {
			switch( $_REQUEST['prod_type'] ) {
				case 1 :
					$args['meta_query'][] = array( 
						'key' => 'wp_aff_product_manual',
						'value' => '',
						'compare' => 'NOT EXISTS'
					);
					break;
				case 2: 
					$args['meta_query'][] = array(
						'key' => 'wp_aff_product_manual',
						'value' => 1,
						'compare' => '='
					);
					break;	
			}
		}
		
		if( isset( $_REQUEST['prod_category'] ) ) {
			$args['tax_query'][] = 
				array(
					'taxonomy' => 'wp_aff_categories',
					'field'    => 'term_id',
					'terms'    => $_REQUEST['prod_category'],
			);
		}
		
		if( isset( $_REQUEST['prod_brand'] ) ) {
			$args['tax_query'][] = array(
					'taxonomy' => 'wp_aff_brands',
					'field'    => 'term_id',
					'terms'    => $_REQUEST['prod_brand'],
			);
		}
		
        $query = new WP_Query( $args );
        //print_var($query);
		
		$total_items = $query->found_posts;
		
        $data = array();
        $i = 0;
        foreach($query->posts AS $post) {
            
            $post_meta = get_post_meta($post->ID);
            $colours = wp_get_post_terms( $post->ID, 'wp_aff_colours' );
            $sizes = wp_get_post_terms( $post->ID, 'wp_aff_sizes' );
            $brands = wp_get_post_terms( $post->ID, 'wp_aff_brands' );
			$cats = wp_get_post_terms( $post->ID, 'wp_aff_categories' );
            
            $prod_data = array();
            
            foreach( $colours AS $colour ) {
                $prod_data['colours'][] = $colour->name;
            }
            foreach( $sizes AS $size ) {
                $prod_data['sizes'][] = $size->name;
            }
            foreach( $brands AS $brand ) {
                $prod_data['brands'][] = $brand->name;
            }
			foreach( $cats AS $cat ) {
                $prod_data['cats'][] = $cat->name;
            }
            //print_var($prod_data);
            $colours = @implode( ', ', $prod_data['colours']);
            $sizes = @implode( ', ', $prod_data['sizes']);
            $brands = @implode( ', ', $prod_data['brands']);
			$cats = @implode( ', ', $prod_data['cats']);
            
						
            //print_var($post_meta);
            
            $data[$i] = array(
                'ID'    => $post->ID,
                'title' => $post->post_title,
                'img' => $post_meta['wp_aff_product_image'][0],
                'colours' => $colours,
                'sizes' => $sizes,
                'brands' => $brands,
				'cats' => $cats,
                'price' => $post_meta['wp_aff_product_price'][0],
                'link' => $post_meta['wp_aff_product_link'][0],
            );
			
			$data[$i]['rrp'] = ( isset( $post_meta['wp_aff_product_rrp'] ) ? $post_meta['wp_aff_product_rrp'][0] : $post_meta['wp_aff_product_price'][0] ); 
			
			( isset( $data[$i]['rrp'] ) && ( $data[$i]['price'] < $data[$i]['rrp'] )  ? $data[$i]['sale'] = 1 : $data[$i]['sale'] = 0 ); 
			( isset( $post_meta['wp_aff_product_sale'] ) && $post_meta['wp_aff_product_sale'][0] == 1 ? $data[$i]['sale'] = 1 : NULL ); 
			( isset( $post_meta['wp_aff_product_picks'] ) && $post_meta['wp_aff_product_picks'][0] == 1 ? $data[$i]['picks'] = 1 : $data[$i]['picks'] = 0 ); 
			global $wp_aff;
			$options = $wp_aff->get_option();
			
			$pastdate = strtotime('-'.$options['new_days'].' days');
			( $pastdate <= strtotime( $post->post_date ) ? $data[$i]['new'] = 1 : $data[$i]['new'] = 0 ); 
			
            $i++;
        }
        //print_var($data);
        /*function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'DESC'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        //usort($data, 'usort_reorder');
        */
        
        
        //$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}

class ListProductSearch extends WP_List_Table {
    var $total;
	var $data;
function __construct( $data = array() ){

    $this->data = $data;
    global $status, $page;
    //Set parent defaults
    parent::__construct( array(
        'singular'  => 'product',     //singular name of the listed records
        'plural'    => 'products',    //plural name of the listed records
        'ajax'      => true        //does this table support ajax?
    ) );

}

function column_default($item, $column_name){
    switch($column_name){
        case 'img':
        case 'desc':
        case 'price':
        case 'link':
        case 'brand' :
            return $item[$column_name];
        default:
            return print_r($item,true); //Show the whole array for troubleshooting purposes
    }
}


    function column_price( $item ) {
		if( $item['rrp'] <= $item['price'] ) {
			$output = $item['price'];
		} else {
			$output = '(<strike>'.$item['rrp'].'</strike>) '.$item['price'];
		}
		return $output;
	}
	
    function column_title($item){
        
        //Build row actions
        
        if( isset( $_REQUEST['category'] ) ) {
            $actions = array(
                'add'      => sprintf(
					'<a href="?page=%s&action=%s&product=%s&q=%s&category=%s&wp_aff_merch=%s&paged=%d">Add Product</a>',
					$_REQUEST['page'],
					'add',$item['ID'], 
					$_REQUEST['q'], 
					$_REQUEST['category'], 
					$_REQUEST['wp_aff_merch'],
					( isset( $_REQUEST['paged'] ) ? (($_REQUEST['paged']+1) > $this->_pagination_args['total_pages'])? 1: $_REQUEST['paged']+1 : 1 )
				
				),
            );
        } else {
            $actions = array(
                'add'      => sprintf(
					'<a href="?page=%s&action=%s&product=%s&q=%s&wp_aff_merch=%s&paged=%d">Add Product</a>',
					$_REQUEST['page'],
					'add',
					$item['ID'], 
					$_REQUEST['q'], 
					$_REQUEST['wp_aff_merch'],
					( isset( $_REQUEST['paged'] ) ? (($_REQUEST['paged']+1) > $this->_pagination_args['total_pages'])? 1: $_REQUEST['paged']+1 : 1 )
				),
            );
        }
                                
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s %4$s',
            /*$1%s*/ stripslashes( $item['title'] ),
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions),
			/*$3$s*/ ( $item['exists'] == 1 ? '<div class="prod_exist">Already Added</div>' : '' )
        );
    }


function column_img($item) {
   return sprintf(
        '<img src="%1$s" style="max-height: 90px; width: auto;" />',
        /*$1%s*/ $item['img']  //Let's simply repurpose the table's singular label ("movie")
    ); 
}

function column_link($item) {
   return sprintf(
        '<a href="%1$s" target="_blank" class="button button-secondary">View Product</a>',
        /*$1%s*/ $item['link']  //Let's simply repurpose the table's singular label ("movie")
    ); 
}
    
function column_desc($item) {
    if(strlen($item['desc']) < 100) {
        return $item['desc'];
    } else {
        return substr($item['desc'], 0, 100).'...';   
    }
}
    
function column_cb($item){
    if( @in_array( $item['ID'], $_SESSION['products'] ) ) {
        $checked = ' checked="checked"';
    } else {
        $checked = '';
    }
    return sprintf(
        '<input type="checkbox" name="%1$s[]" value="%2$s" %3$s />',
        /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
        /*$2%s*/ $item['ID'],                //The value of the checkbox should be the record's id
        /*$3$s*/ $checked
    );
}
function column_aff($item) {
	
    if($item['aff'] == 'awin') {
        return '<img src="'.str_replace( 'inc/', '', plugin_dir_url( __FILE__ ) ).'img/awin.png" style="max-width: 50%; height: auto;" />';
    }
	if($item['aff'] == 'linkshare') {
        return '<img src="'.str_replace( 'inc/', '', plugin_dir_url( __FILE__ ) ).'img/linkshare.jpg" style="max-width: 50%; height: auto;" />';
    }
	if($item['aff'] == 'ff') {
        return '<img src="'.str_replace( 'inc/', '', plugin_dir_url( __FILE__ ) ).'img/ff.jpg" style="max-width: 50%; height: auto;" />';
    }
	if($item['aff'] == 'tradedoubler' || $item['aff'] == 'tradedoubler-hb' ) {
        return '<img src="'.str_replace( 'inc/', '', plugin_dir_url( __FILE__ ) ).'img/tradedoubler.jpg" style="max-width: 50%; height: auto;" />';
    }
}
function get_columns(){
    $columns = array(
        'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
        'aff'       => 'Affiliate',
        'img'       => 'Image',
        'title'     => 'Title',
        'brand'     => 'Brand',
        'price'     => 'Price',
        'desc'      => 'Description',
        'link'      => 'Link'
    );
    return $columns;
}

function get_sortable_columns() {
    $sortable_columns = array(
        'title'    => array('title',false),
        'price'  => array('price',false),
    );
    return $sortable_columns;
}

function get_bulk_actions() {
    $actions = array(
        'add'    => 'Add'
    );
    return $actions;
}

function process_bulk_action() {   
}

    
function prepare_items() {
	//print_var( $this->data );
	$data = $this->data['items'];
    $per_page = $this->data['total']['depth'];

    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = array($columns, $hidden, $sortable);
    if(!get_query_var('redirected')){
        $this->process_bulk_action();   
    }
	  
	if( !isset( $_SESSION['product_data'] ) ) {
		$_SESSION['product_data'] = $data;
	} else {
		$_SESSION['product_data'] = array_merge( $_SESSION['product_data'], $data );
	}
    
    $current_page = $this->get_pagenum();

    $total_items = $this->data['total']['total'];

    $this->items = $data;

    $this->set_pagination_args( array(
        'total_items' => $total_items,                  //WE have to calculate the total number of items
        'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
        'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
    ) );
}

}