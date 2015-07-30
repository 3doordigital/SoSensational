<?php
/*
	Plugin Name: Affiliate Shop &raquo; F&F API
	Plugin URI: 
	Description: F&F API for WP Affiliate Shop
	Version: 1.0.0
	Author: 3 Door Digital
	Author URI: http://www.3doordigital.com
	License: GPL V3
*/
/**
* Adds F&F API to Affiliate Shop
*
*
* @copyright  2015 3 Door Digital
* @license    GPL v3
* @version    Release: 1.0.0
* @since      Class available since Release 1.0.0
*/ 
class WordPress_Affiliate_Shop_FF {
	static $options	;
	public function __construct() {
		global $wp_aff;
		$this->option = $wp_aff->get_option();
		self::$options = $wp_aff->get_option();
		
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		
	}
	
	/**
	* Fires on plugin activation. Sets inital options
	*
	* @return nothing
	*/ 
	static function activation() {
		global $wp_aff;
		if( !isset( self::$options['apis'] ) ) {
			$array = $wp_aff->get_option();
			$array['apis']['ff'] = array(
				'name' 		=> 'ff',
				'nicename'	=> 'F&F',
				'class' 	=> 'WordPress_Affiliate_Shop_FF'
			);
		} else {
			$array = $wp_aff->get_option();
			$array['apis']['ff'] = array(
				'name' 		=> 'ff',
				'nicename'	=> 'F&F',
				'class' 	=> 'WordPress_Affiliate_Shop_FF'
			);
		}
		update_option( $wp_aff->option_name, $array );
	}
	
	/**
	* Fires on plugin deactivation. 
	*
	* @return nothing
	*/
	static function deactivation() {
		global $wp_aff;
		$array = $wp_aff->get_option();
		unset( $array['apis']['ff'] );
		update_option( $wp_aff->option_name, $array );
	}
	
		public function merchants() {
			
			$array = array();
			
			
				$array['ID-'.$item->iId] = array (
					'ID'	=> 'fanf',
					'name'	=> 'F&F (Tesco)',
					'aff'	=> 'ff'
				);	
			
			
			return $array;
			
		}
		
		private function get_file( ) {
			$source = 'http://www.fusepumpaffiliates.co.uk/feed-distribution/tesco/clothing/grab.php?fpid=1fa3d1429ed9&pid=1755009';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $source);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec ($ch);
			$error = curl_error($ch); 
			curl_close ($ch);
			
			$upload_dir = wp_upload_dir(); 
			$user_dirname = $upload_dir['basedir'].'/feed-data';
			if( ! file_exists( $user_dirname ) )
				wp_mkdir_p( $user_dirname );
	
			$destination = $user_dirname.'/fandf.csv';
			$file = fopen($destination, "w+");
			fputs($file, $data);
			fclose($file);	
			
			return $destination;
		}
		
		public function update_feed( $ID, $merch = NULL ) {
			$out['success'] = 0;
			$out['error'] = 0;
			$out = array();
			$local_file = $this->get_file( );
			
			$upload_dir = wp_upload_dir(); 
			$user_dirname = $upload_dir['basedir'].'/feed-data';
			$filename = $user_dirname.'/fandf.csv';
			if(($handle = fopen( $filename, 'r')) !== false)
			{
				global $wpdb;
				// get the first row, which contains the column-titles (if necessary)
				$header = fgetcsv($handle);
				$out['status'] = 1;	
				// loop through the file line-by-line
				while(($data = fgetcsv($handle)) !== false)
				{
					set_time_limit(0);
					$table_name = $wpdb->prefix . "feed_data";
					//print_var( $data );
					if( $data[8] != '' ) {
						$price = $data[7];
						$rrp = $data[8];	
					} else {
						$price = $data[7];
						$rrp = $data[7];	
					}
					$replace = $wpdb->replace( $table_name, array( 
							'product_id' => 'fandf_'.$data[2], 
							'product_aff' => 'ff',
							'product_merch' => sanitize_text_field( 'fandf' ),
							'product_title' => sanitize_text_field( $data[0] ),
							'product_brand' => sanitize_text_field( $data[1] ),
							'product_image' => esc_url( $data[6] ),
							'product_desc' => sanitize_text_field( $data[9] ),
							'product_price' => $price,
							'product_rrp' => $rrp,
							'product_link' => esc_url( $data[3] ), 
						)
					);
					
					switch ($replace) {
						case false :
							//die( $wpdb->last_query );
							$out['message'][] = $wpdb->last_query;
							$out['error'] ++;
							break;
						case 1 :
							$out['message'][] = 'Inserted fandf_'.$data['2'];
							$out['success'] ++;
							break;
						default :
							$out['message'][] = 'Replaced fandf_'.$data['2'];
							break;	
					}
					
					unset($data);
				}
				fclose($handle);
			}
			return $out;
		}
	}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_FF', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_FF', 'deactivation' ) );