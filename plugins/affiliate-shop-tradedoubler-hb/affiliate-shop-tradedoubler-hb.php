<?php
/*
	Plugin Name: Affiliate Shop &raquo; TradeDoubler (Hugo Boss) API
	Plugin URI: 
	Description: TradeDoubler API for WP Affiliate Shop
	Version: 1.0.0
	Author: 3 Door Digital
	Author URI: http://www.3doordigital.com
	License: GPL V3
*/
/**
* Adds TradeDoubler API to Affiliate Shop
*
*
* @copyright  2015 3 Door Digital
* @license    GPL v3
* @version    Release: 1.0.0
* @since      Class available since Release 1.0.0
*/

class WordPress_Affiliate_Shop_TradeDoubler_HB {
	static $options;	
	public function __construct() {
		global $wp_aff;
		$this->option = $wp_aff->get_option();
		self::$options = $wp_aff->get_option();
		$this->token = '8EFD196167B1A341C02BC3052CE293876673C7C6';
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
			$array['apis']['tradedoubler-hb'] = array(
				'name' 		=> 'tradedoubler-hb',
				'nicename'	=> 'TradeDoubler',
				'class' 	=> 'WordPress_Affiliate_Shop_TradeDoubler_HB'
			);
		} else {
			$array = $wp_aff->get_option();
			$array['apis']['tradedoubler-hb'] = array(
				'name' 		=> 'tradedoubler-hb',
				'nicename'	=> 'TradeDoubler',
				'class' 	=> 'WordPress_Affiliate_Shop_TradeDoubler_HB'
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
		unset( $array['apis']['tradedoubler-hb'] );
		update_option( $wp_aff->option_name, $array );
	}
	
	
		
	/**
	* Returns array of merchants
	*
	* @return array	$array
	*/ 
	
	public function merchants() {
		
		
			$array['td-hb'] = array(
				'ID'        => 'td-hb',
				'name'     	=> 'Hugo Boss',
				'aff'     	=> 'tradedoubler-hb',
			);
		return $array;
		
	}
	
	public function update_feed( $merchant, $merch ) {
		
		$data = array();
		$out = array();
		$out['success'] = 0;
		$out['error'] = 0;
		$out['status'] = 0;
		
		$url = 'http://pf.tradedoubler.com/export/export?myFeed=14387854752501130&myFormat=14376626532501130';
		if ( function_exists( 'ini_set' ) ) {
			@ini_set('memory_limit', '2048M');
		}
		set_time_limit(0);
		$upload_dir = wp_upload_dir(); 
		$user_dirname = $upload_dir['basedir'].'/feed-data';
		if( ! file_exists( $user_dirname ) )
			wp_mkdir_p( $user_dirname );

		$destination = $user_dirname.'/td.csv';
		$fp = fopen($destination, "w+");
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
		curl_exec($ch);
		if(!curl_errno($ch))
		{
			$out['status'] = 1;	
		}
		curl_close($ch);
		fclose($fp);

		if( $out['status'] == 1 ) {
			if(($handle = fopen( $destination, 'r')) !== false)
			{
				global $wpdb;
				
				// get the first row, which contains the column-titles (if necessary)
				$header = fgetcsv($handle);
				print_var( $header );
				$out['status'] = 1;	
				// loop through the file line-by-line
				$i = 0;
				while(($test = fgetcsv($handle, 0, '|') && $i == 0 ) !== false)
				{
					$i++;
					print_var( $test );	
					/*$data = array(
						'ID'        => (string) sanitize_text_field( $product->offers[0]->id ),
						'aff'     	=> 'tradedoubler-hb',    
						'title'     => (string) sanitize_text_field( trim( ucwords( strtolower( $product->name ) ) ) ),
						'brand'     => (string) sanitize_text_field( trim( ucwords( strtolower( $product->offers[0]->programName ) ) ) ),
						'img'       => (string) esc_url($product->productImage->url),
						'desc'      => (string) sanitize_text_field( $product->description ),
						'price'     => (int) number_format( $product->offers[0]->priceHistory[0]->price->value, 2, '.', '' ),
						'rrp'       => (int) number_format( $product->offers[0]->priceHistory[0]->price->value, 2, '.', '' ),
						'link'      => (string) esc_url($product->offers[0]->productUrl)
					);*/
					//print_var( $data );
					//print_var($product);
					
					/*$table_name = $wpdb->prefix . "feed_data";
					$replace = $wpdb->insert( $table_name, array( 
							'product_id' => $merchant.'_'.$data['ID'], 
							'product_aff' => $data['aff'],
							'product_merch' => $merchant,
							'product_title' => $data['title'],
							'product_brand' => $merch,
							'product_image' => $data['img'],
							'product_desc' => $data['desc'],
							'product_price' => $data['price'],
							'product_rrp' => $data['rrp'],
							'product_link' => $data['link'], 
						)
					);
					$error = $wpdb->last_error;
					//echo $replace;
					switch ($replace) {
						case false :
							//die( $wpdb->last_query );
							$out['message'][] = $wpdb->last_query;
							$out['error'] ++;
							break;
						case 1 :
							$out['message'][] = 'Inserted '.$merchant.'_'.$data['ID'];
							$out['success'] ++;
							break;
						default :
							$out['message'][] = 'Replaced '.$merchant.'_'.$data['ID'];
							break;	
					}
					unset( $data );
					unset( $product );*/
				}
			} else { $out = 'Failed'; }
		}
		
		return $out;
	}
	
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_TradeDoubler_HB', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_TradeDoubler_HB', 'deactivation' ) );