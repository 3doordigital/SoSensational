<?php
    
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
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
		
		if( $item['new'] == 1 ) {
			$output .= '<a href="#" class="active" data-item="'.$item['ID'].'" data-action="new"><i class="fa fa-calendar fa-fw fa-lg"></i></a>';
		} else {
			$output .= '<a href="#" class="" data-item="'.$item['ID'].'" data-action="new"><i class="fa fa-calendar fa-fw fa-lg"></i></a>';
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

        $per_page = 10;
        
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
			( isset( $post_meta['wp_aff_product_picks'] ) && $post_meta['wp_aff_product_picks'] == 1 ? $data[$i]['picks'] = 1 : $data[$i]['picks'] = 0 ); 
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