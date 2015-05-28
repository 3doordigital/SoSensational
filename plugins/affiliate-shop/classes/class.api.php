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
		
		public function update_product( $id = null, $prod_id = null, $aff = null, $title = null, $merch = null ) {
			
			$data = array();
			
			$classname = $this->option['apis'][$api]['class'];
			$class = new $classname();
			$data = $class->update_product( $prod_id, $merch );
			
			$out = '';
			if( !empty( $data['item'] ) ) {
				foreach( $data['item'] as $item ) {
				if( ( $item['price'] == '' || $item['price'] == 0 || $item['price'] == '0.00' ) && $item['rrp'] != '' ) {
					update_post_meta($id, 'wp_aff_product_price', $item['rrp']);
				} else {
					update_post_meta($id, 'wp_aff_product_price', $item['price']);
				}
				//update_post_meta($id, 'wp_aff_product_id', $item['ID']);
				//update_post_meta($id, 'wp_aff_product_aff', $item['aff']);
				update_post_meta($id, 'wp_aff_product_rrp', $item['rrp']);
				update_post_meta($id, 'wp_aff_product_merch', $item['merch'] );
				}
			} else {
				//wp_trash_post( $id  );
			}
			return $out;
		}
				
		private function usort_reorder( $a, $b ){
			$orderby = ( !empty( $this->sortby ) ) ? $this->sortby : 'title'; //If no sort, default to title
			$order = ( !empty( $this->sort ) ) ? $this->sort : 'asc'; //If no order, default to asc
			$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
			return ( $order==='asc' ) ? $result : -$result; //Send final sort direction to usort
		}
		
	}