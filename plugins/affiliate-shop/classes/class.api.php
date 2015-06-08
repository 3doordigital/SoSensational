<?php

	class wpAffAPI {
		
		public function __construct() {
			global $wp_aff;
			$this->option = $wp_aff->get_option();
		}
		
		private function get_all_products() {
			
			$args = array(
				'post_type' => 'wp_aff_products',
				'posts_per_page' => -1, 
				'post_status' => 'publish'
			 );
			$postslist = get_posts( $args );
			
			$posts = array();
			
			foreach( $postslist as $post ) {
				$meta = get_post_meta( $post->ID, 'wp_aff_product_id', true );
				$posts[] = $meta;
			}
			return $posts;
		}
		
		public function db_search( $term = '', $api = 'all', $merchant = NULL,  $depth = 25, $page = 1, $sortby = 'title', $sort = 'asc') {
			ini_set('memory_limit', '2048M');
			ini_set('max_execution_time', '5000');
			$products = array();
			$products['items'] = array();
			$search = '';
			$offset = $depth * ( $page - 1);
			
			$terms = explode( ' ', $term );
			foreach( $terms as $term ) {
				$search .= '+'.$term.' ';	
			}
			//$search = $term;
			global $wpdb;
			$table_name = $wpdb->prefix . "feed_data";
			$query ="
				SELECT * 
				FROM 
				$table_name 
				WHERE MATCH(product_title) AGAINST('$search' IN BOOLEAN MODE)	";
			//$query .= "LIKE '%$term%' ";
			if( $api != 'all' ) { $query .= " AND product_aff='$api' "; }
			if( $merchant != NULL && $merchant != 0 ) { $query .= " AND product_merch='$merchant' "; }	
			$query .= "ORDER BY MATCH(product_title) AGAINST('$search' IN BOOLEAN MODE) DESC";
			$query2 = $query." LIMIT $offset, $depth";
			echo $query2;
			//$out = $query;
			$totalres = $wpdb->get_results( $query );
			$products['total']['total'] = $wpdb->num_rows;
			if ( $result = $wpdb->get_results( $query2, ARRAY_A	) ) {
				foreach( $result as $product ) {
					$products['items']['ID-'.$product['product_id']] = array (
						'ID'        => $product['product_id'],
						'aff'     	=> $product['product_aff'],   
						'title'     => addslashes( trim( ucwords( strtolower( $product['product_title'] ) ) ) ),
						'brand'     => addslashes( $product['product_brand'] ),
						'img'       => addslashes( $product['product_image'] ),
						'desc'      => addslashes( $product['product_desc'] ),
						'price'     => number_format( $product['product_price'], 2, '.', '' ),
						'rrp'       => number_format( $product['product_rrp'], 2, '.', ''  ),
						'link'      => addslashes( $product['product_link'] )	,
						'exists'	=> 0
					);	
				}
				
			}
			return $products;
		}
		
		public function search( $term = '', $api, $merchant = NULL,  $depth = 25, $page = 1, $sortby = 'title', $sort = 'asc') {
			
			$temp = array();
			
			$this->sortby = $sortby;
			$this->sort = $sort;
			
			if( count( $this->option['apis'] ) > 0 ) {
				foreach( $this->option['apis'] as $affiliate ) {
					$classname = $affiliate['class'];
					$class = new $classname();
					if( $api == $affiliate['name'] || $api == 'all' ) {
						$temp[] = $class->search( $term, $merchant, $depth, $page, $sortby, $sort );
					}
				}
			}
			
			$output = array();	
			$output['items'] = array();
			$output['total'] = array();	
			$output['total']['total'] = '';
			
			foreach( $temp as $key=>$input ) {
				if( isset( $input['items'] ) ) {
					$output['items'] = array_replace( $output['items'], $input['items'] );
				}
				if( isset( $input['total'] ) ) {
					$output['total'] =  array_replace( $output['total'], $input['total'] );;
				}
			}
			
			
			
			uasort( $output['items'], array( $this, 'usort_reorder' ) );
			
			foreach( $output['total'] as $value ) {
				$output['total']['total'] = $output['total']['total'] + $value;
			}
			
			return $output;
			
		}
		
		public function get_affiliates( $api ) {
			if( count( $this->option['apis'] ) > 0 ) {
				$output = '';
				foreach( $this->option['apis'] as $affiliate ) {
					$output .= '<option '. selected( $api, $affiliate['name'] ). ' value='.$affiliate['name'].'">'.$affiliate['nicename'].'</option>';
				}
				echo $output;
			}
		}
		
		public function get_merchants( $api, $merchant, $array = false ) {
			
			$temp = array();
			
			$this->sortby = 'name';
			$this->sort = 'asc';
			
			if( count( $this->option['apis'] ) > 0 ) {
				foreach( $this->option['apis'] as $affiliate ) {
					$classname = $affiliate['class'];
					$class = new $classname();
					if( $api == $affiliate['name'] || $api == 'all' ) {
						$temp[] = $class->merchants( $term, $merchant, $depth, $page, $sortby, $sort );
					}
				}
			}
			
			$output = array();	
				
			foreach( $temp as $input ) {
				$output = array_replace( $output, $input );
			}
			
			
			uasort( $output, array( $this, 'usort_reorder' ) );
			if( $array == false ) {
				$echo =  '';
				foreach( $output as $item ) {
					$echo .= '<option '.( $merchant == $item['ID'] ? ' selected ' : '' ).'value="'.$item['ID'].'">'.$item['name'].' ('.$item['aff'].')</option>';
				}
				
				echo $echo;
			} else {
				return $output;	
			}
						
		}
		
		public function update_product( $id = null, $prod_id = null, $api = null, $title = null, $merch = null ) {
			
			$data = array();
			$out = '';
			
			global $wpdb;
			$table_name = $wpdb->prefix . "feed_data";
			$query ="
				SELECT * 
				FROM 
				$table_name 
				WHERE product_id 
				REGEXP '^([0-9]+)_{$prod_id}$' 
				LIMIT 1
				";
			//$out = $query;
			if ($products = $wpdb->get_results( $query, ARRAY_A	) ) {
				$data['status'] = 1;
				foreach ( $products as $product ) 
				{
					$data['item'] = $product;
				}	
			} else {
				$data['status'] = 0;
			}
			
			
			
			if( !empty( $data['item'] ) ) {
				$item = $data['item'];
				/*if( ( $item['price'] == '' || $item['price'] == 0 || $item['price'] == '0.00' ) && $item['rrp'] != '' ) {
					update_post_meta( $id, 'wp_aff_product_price', $item['rrp'] );
				} else {
					update_post_meta( $id, 'wp_aff_product_price', $item['price'] );
				}
				update_post_meta( $id, 'wp_aff_product_rrp', $item['rrp'] );
				update_post_meta( $id, 'wp_aff_product_merch', $item['merch'] );*/
				$data['out'] = ' updated '.$id;
			} else {
				update_post_meta( $id, 'wp_aff_product_notfound', 1 );
				$data['out'] = 'not found '.$id;
				//wp_trash_post( $id  );
			}
			return $data;
		}
				
		private function usort_reorder( $a, $b ){
			$orderby = ( !empty( $this->sortby ) ) ? $this->sortby : 'title'; //If no sort, default to title
			$order = ( !empty( $this->sort ) ) ? $this->sort : 'asc'; //If no order, default to asc
			$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
			return ( $order==='asc' ) ? $result : -$result; //Send final sort direction to usort
		}
		
	}