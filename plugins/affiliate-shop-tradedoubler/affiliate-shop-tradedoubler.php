<?php
/*
	Plugin Name: Affiliate Shop &raquo; TradeDoubler API
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

class WordPress_Affiliate_Shop_TradeDoubler {
	static $options;	
	public function __construct() {
		global $wp_aff;
		$this->option = $wp_aff->get_option();
		self::$options = $wp_aff->get_option();
		$this->token = '515067448FB36AF3071C9DB318BBE9D5093E43B5';
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
			$array['apis']['tradedoubler'] = array(
				'name' 		=> 'tradedoubler',
				'nicename'	=> 'TradeDoubler',
				'class' 	=> 'WordPress_Affiliate_Shop_TradeDoubler'
			);
		} else {
			$array = $wp_aff->get_option();
			$array['apis']['tradedoubler'] = array(
				'name' 		=> 'tradedoubler',
				'nicename'	=> 'TradeDoubler',
				'class' 	=> 'WordPress_Affiliate_Shop_TradeDoubler'
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
		unset( $array['apis']['tradedoubler'] );
		update_option( $wp_aff->option_name, $array );
	}
	
	
		
	/**
	* Returns array of merchants
	*
	* @return array	$array
	*/ 
	
	public function merchants() {
		
		
		$url = 'http://api.tradedoubler.com/1.0/productFeeds?token='.$this->token;
		
		$data = file_get_contents( $url );
		
		$req = json_decode( $data );
				
		foreach ($req->feeds as $item) {
			
			$array['ID-'.$item->feedId] = array(
				'ID'        => ( string ) $item->feedId,
				'name'     	=> ( string ) $item->name,
				'aff'     	=> 'tradedoubler',
			);
		}
		return $array;
		
	}
	
	public function update_feed( $merchant, $merch = null ) {
		
		$data = array();
		$out = array();
		$out['status'] = 0;
		
		$url = 'http://api.tradedoubler.com/1.0/productsUnlimited;fid='.$merchant.'?token='.$this->token;
		if ( function_exists( 'ini_set' ) ) {
			@ini_set('memory_limit', '2048M');
		}
		set_time_limit(0);
		$upload_dir = wp_upload_dir(); 
		$user_dirname = $upload_dir['basedir'].'/feed-data';
		if( ! file_exists( $user_dirname ) )
			wp_mkdir_p( $user_dirname );

		$destination = $user_dirname.'/temp.json';
		$fp = fopen($destination, "w+");
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
		curl_exec($ch); 
		curl_close($ch);
		fclose($fp);

		if( $file = file_get_contents( $destination ) ) $out['status'] = 1;
		
		$req = json_decode( $file );
		
		foreach( $req->products as $product ) {
			//print_var( $product );	
			$data = array(
				'ID'        => $merchant.'_'.$product->offers[0]->id,
				'aff'     	=> 'tradedoubler',    
				'title'     => trim( ucwords( strtolower( $product->name ) ) ),
				'brand'     => trim( ucwords( strtolower( $product->offers[0]->programName ) ) ),
				'img'       => $product->productImage->url,
				'desc'      => $product->description,
				'price'     => number_format( $product->offers[0]->priceHistory[0]->price->value, 2, '.', '' ),
				'rrp'       => number_format( $product->offers[0]->priceHistory[0]->price->value, 2, '.', '' ),
				'link'      => $product->offers[0]->productUrl
			);
			//print_var( $data );
			global $wpdb;
			//print_var($product);
			$table_name = $wpdb->prefix . "feed_data";
			$replace = $wpdb->insert( $table_name, array( 
					'product_id' => $merchant.'_'.$data['ID'], 
					'product_aff' => $data['aff'],
					'product_merch' => $merchant,
					'product_title' => $data['title'],
					'product_brand' => $data['brand'],
					'product_image' => $data['img'],
					'product_desc' => $data['desc'],
					'product_price' => $data['price'],
					'product_rrp' => $data['rrp'],
					'product_link' => $data['link'], 
				)
			);
			//echo $replace;
			switch ($replace) {
				case false :
					$out['message'][] = $wpdb->print_error();
					break;
				case 1 :
					$out['message'][] = 'Inserted '.$data['ID'];
					break;
				default :
					$out['message'][] = 'Replaced '.$data['ID'];
					break;	
			}
			unset( $data );
		}
		return $out;
	}
	
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_TradeDoubler', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_TradeDoubler', 'deactivation' ) );