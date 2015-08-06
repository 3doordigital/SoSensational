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
		
		public function db_search( $term = '', $api = 'all', $merchant=0,  $depth = 25, $page = 1, $sortby = 'title', $sort = 'asc') {
			ini_set('memory_limit', '2048M');
			ini_set('max_execution_time', '5000');
			$products = array();
			$products['items'] = array();
			$search = '';
			$offset = $depth * ( $page - 1);
			$products['total']['depth'] = $depth;
			$terms = explode( ' ', $term );
			foreach( $terms as $term ) {
				$search .= '+'.$term.' ';	
			}
			//$search = $term;
			global $wpdb;
			$table_name = $wpdb->prefix . "feed_data";
			$query ="
				SELECT SQL_CALC_FOUND_ROWS *
				FROM 
				$table_name 
				WHERE MATCH(product_title) AGAINST('$search' IN BOOLEAN MODE)	";
			//$query .= "LIKE '%$term%' ";
			if( $api != 'all' ) { $query .= " AND product_aff='$api' "; }
			echo $merchant;
			if( $merchant == 0 ) { } else { $query .= " AND product_merch='$merchant' "; }	
			$query .= "ORDER BY MATCH(product_title) AGAINST('$search' IN BOOLEAN MODE) DESC";
			$query2 = $query." LIMIT $offset, $depth";
			
			echo $query2;
			//$out = $query;
			//$totalres = $wpdb->get_results( $query );
			
			if ( $result = $wpdb->get_results( $query2, ARRAY_A	) ) {
				$totalres = $wpdb->get_var( "SELECT FOUND_ROWS();" );
				foreach( $result as $product ) {

					$pid = explode( '_', $product['product_id'] );
					$pid = $pid[1];

					$qry_args = array(
						'post_status' => 'publish',
						'post_type' => 'wp_aff_products',
						'posts_per_page' => 1,
						'orderby' => 'post_date',
						'order' => 'DESC' ,
                        'fields' => 'ids',
						'meta_query' => array(
                            'relation' => 'AND',
							array(
							 'key' => 'wp_aff_product_id',
							 'value' => $product['product_id'],
							 'compare' => '='
							),
						)
					);
					$posts = get_posts( $qry_args );

					if( count( $posts ) > 0 ) {
						$exists = 1;
					} else {
						$exists = 0;
					}



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
						'exists'	=> $exists
                    );
				}

			}
			
			//print_var( $result );
			$products['total']['total'] = $totalres;
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
		
		public function update_product( $id = null, $prod_id = null, $merch = null ) {
			
			$data = array();
			$out = '';
			if( !strstr( $prod_id, '_' ) && $merch != '' ) {
				$prod_id = $merch.'_'.$prod_id;	
			}
			global $wpdb;
			$table_name = $wpdb->prefix . "feed_data";
			$query ="
				SELECT * 
				FROM 
					$table_name 
				WHERE product_id
					= '$prod_id' 
				LIMIT 1
				";
			//$out = $query;
			//echo $query;
			if ($products = $wpdb->get_results( $query, ARRAY_A	) ) {
				foreach ( $products as $product ) 
				{
					$data['item'] = $product;
				}	
			} else {
				$data['status'] = 0;
			}
			
			
			if( !empty( $data['item'] ) ) {
				$item = $data['item'];

				$dbtitle = substr( htmlspecialchars( $item['product_title'] ), 0, 5 );
				$wptitle = substr( get_the_title( $id ), 0, 5 );
				if( get_post_meta( $id, 'wp_aff_product_manual', true ) == 1 ) {
					wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
				} else {
					if( stristr( $dbtitle, $wptitle ) == FALSE ) {
						update_post_meta( $id, 'wp_aff_product_notfound', 1 );
						$data['out'] = 'Not Found '.$prod_id;
						wp_trash_post( $id  );
						$data['status'] = 0;
					} else {
	
						$data['status'] = 1;
	
						wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
						update_post_meta( $id, 'wp_aff_product_rrp', $item['product_rrp'] );
						update_post_meta( $id, 'wp_aff_product_price', $item['product_price'] );
						update_post_meta( $id, 'wp_aff_product_id', $prod_id );
						update_post_meta( $id, 'wp_aff_product_notfound', 0 );
						if( $item['product_price'] < $item['product_rrp'] ) {
							update_post_meta( $id, 'wp_aff_product_sale', 1 );
						} else {
							update_post_meta( $id, 'wp_aff_product_sale', 0 );
						}
						update_post_meta( $id, 'wp_aff_product_link', $item['product_link'] );
						update_post_meta( $id, 'wp_aff_product_image', $item['product_image'] );
						update_post_meta( $id, 'wp_aff_product_merch', $item['product_merch'] );
						update_post_meta( $id, 'wp_aff_product_notfound', 0 );
						$data['out'] = 'Updated by ID '.$id;
					}
				}
			} else {
				update_post_meta( $id, 'wp_aff_product_notfound', 1 );
				$data['out'] = 'Not Found '.$prod_id;
				wp_trash_post( $id  );
				$data['status'] = 0;

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