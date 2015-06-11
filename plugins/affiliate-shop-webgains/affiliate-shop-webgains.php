<?php
/*
	Plugin Name: Affiliate Shop &raquo; Webgains API
	Plugin URI: 
	Description: Webgains API for WP Affiliate Shop
	Version: 1.0.0
	Author: 3 Door Digital
	Author URI: http://www.3doordigital.com
	License: GPL V3
*/
/**
* Adds Webgains API to Affiliate Shop
*
*
* @copyright  2015 3 Door Digital
* @license    GPL v3
* @version    Release: 1.0.0
* @since      Class available since Release 1.0.0
*/ 
class WordPress_Affiliate_Shop_Webgains {
	
	static $options;
	private $option_name = 'wp_aff_webgains_merchants';	
	private $merchants;
	
	public function __construct() {
		global $wp_aff;
		$this->option = $wp_aff->get_option();
		self::$options = $wp_aff->get_option();
		//echo $option_name;
		$this->merchants = get_option( $this->option_name );
		
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
			$array['apis']['webgains'] = array(
				'name' 		=> 'webgains',
				'nicename'	=> 'Webgains',
				'class' 	=> 'WordPress_Affiliate_Shop_Webgains'
			);
		} else {
			$array = $wp_aff->get_option();
			$array['apis']['webgains'] = array(
				'name' 		=> 'webgains',
				'nicename'	=> 'Webgains',
				'class' 	=> 'WordPress_Affiliate_Shop_Webgains'
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
		unset( $array['apis']['webgains'] );
		update_option( $wp_aff->option_name, $array );
	}
	
	
		
	/**
	* Returns array of merchants
	*
	* @return array	$array
	*/ 
	
	public function merchants() {
		if( ( isset( $this->merchants['updated'] ) && $this->merchants['updated'] != date('d-m-Y') ) || !isset( $this->merchants['updated'] ) ) {
			
			//update_option( $this->option_name, '' );
			
			$url = 'http://www.webgains.com/affiliates/datafeed.html?action=download&campaign=71942&programs=all&categories=all&fields=standard&fieldIds=program_id,program_name&format=csv&separator=pipe&zipformat=none&stripNewlines=1&apikey=f04b19e18a7c601da209cee4036e4608';
			
			//$array = $this->merchants;
			$upload_dir = wp_upload_dir(); 
			$user_dirname = $upload_dir['basedir'].'/feed-data';
			if( ! file_exists( $user_dirname ) )
				wp_mkdir_p( $user_dirname );
	
			$uc_local_file = $user_dirname.'/webgainsmerchants.csv';
			
			$fp = fopen($uc_local_file, "w+");
			$ch = curl_init($url);
			$options = array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER         => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_AUTOREFERER    => true,
				CURLOPT_CONNECTTIMEOUT => 120,
				CURLOPT_TIMEOUT        => 120,
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_FILE		   => $fp
			);
			curl_setopt_array( $ch, $options );
			curl_exec($ch);
			if(!curl_errno($ch))
			{
				$out['status'] = 1;	
			}
			curl_close($ch);
			fclose($fp);
	
			
			if ( function_exists( 'ini_set' ) ) {
				@ini_set('memory_limit', '2048M');
			}
			
			$array = array();
			if(($handle = fopen( $uc_local_file, 'r')) !== false) {
				$header = fgetcsv($handle, 0, '|');
				while(($data = fgetcsv($handle, 0, '|')) !== false )
				{
					if( !in_array( $data[0], $array ) ) {
						$array['items'][$data[0]] = array(
							'ID'        => ( string ) $data[0],
							'name'     	=> ( string ) $data[1],
							'aff'     	=> 'webgains',	
						);
					}
				}
				 $array['updated'] = date( 'd-m-Y' );
				 
				 update_option( $this->option_name, $array );	
			}
		}
		//print_var( $this->merchants );
		return $this->merchants['items'];
	}
	
	/**
	* Retrieves feed from affilaite for a merchant ($merchant) and replaces the entry in the database.
	*
	* @param  string 	$merchant 	The ID of the merchant
	* 
	* @return array	$out
	*/ 
	public function update_feed( $merchant, $merch ) {
		
			
			$upload_dir = wp_upload_dir(); 
			$user_dirname = $upload_dir['basedir'].'/feed-data';
			if( ! file_exists( $user_dirname ) )
				wp_mkdir_p( $user_dirname );
	
			$uc_local_file = $user_dirname.'/webgainsproducts-'.$merchant.'.csv';
			
			$url = 'http://www.webgains.com/affiliates/datafeed.html?action=download&campaign=71942&programs='.$merchant.'&categories=all&fields=extended&fieldIds=deeplink,description,image_url,price,product_id,product_name,program_id,program_name,recommended_retail_price,Full_merchant_price&format=csv&separator=comma&zipformat=none&stripNewlines=1&apikey=f04b19e18a7c601da209cee4036e4608';
			$fp = fopen($uc_local_file, "w+");
			$ch = curl_init($url);
			$options = array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER         => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_AUTOREFERER    => true,
				CURLOPT_CONNECTTIMEOUT => 120,
				CURLOPT_TIMEOUT        => 120,
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_FILE		   => $fp
			);
			curl_setopt_array( $ch, $options );
			curl_exec($ch);
			if(!curl_errno($ch))
			{
				$out['status'] = 1;	
			}
			curl_close($ch);
			fclose($fp);
			//$contents = file_get_contents( $url );
			//echo $contents;
			$data = array();
			
			if ( function_exists( 'ini_set' ) ) {
				@ini_set('memory_limit', '2048M');
			}
			$out['status'] = 1;
			/*
			$fp = gzopen( $local_file, "r");
			while ($line = gzread($fp,1024)) {
				fwrite($fp1, $line, strlen($line));
			}
			fclose( $fp1 );
			gzclose($fp);
			*/
			if(($handle = fopen( $uc_local_file, 'r')) !== false) {
				global $wpdb;
				// get the first row, which contains the column-titles (if necessary)
				$header = fgetcsv($handle, 0, ',');
				//print_var( $header );
				$out['status'] = 1;	
				$i = 0 ;
				// loop through the file line-by-line
				while(($data = fgetcsv($handle, 0, ',')) !== false )
				{
					if( $data[3] != '' && $data[8] != '' ) {
						set_time_limit(0);
						$i ++;
						
						if( $data[3] == 0 || $data[3] = '' || $data[3] == '0.00' ) {
							$price = $data[9];
						} else {
							$price = $data[3];	
						}
						
						if( $data[8] == $data[6] ) {
							$rrp = $price;
						} elseif( $data[8] != 0 || $data[8] != '' || $data[8] != '0.00' ) {
							$rrp = $data[8];	
						} else {
							$rrp = $price; 
						}
						
						$table_name = $wpdb->prefix . "feed_data";
						
						$datainsert = array( 
								'product_id' => $data[6].'_'.$data[4], 
								'product_aff' => 'webgains',
								'product_merch' => sanitize_text_field( $data[6] ),
								'product_title' => sanitize_text_field( $data[5] ),
								'product_brand' => sanitize_text_field( $data[7] ),
								'product_image' => esc_url( $data[2] ),
								'product_desc' => sanitize_text_field( $data[1] ),
								'product_price' => $price,
								'product_rrp' => $rrp,
								'product_link' => esc_url( $data[0] ), 
							);
						$replace = $wpdb->insert( $table_name, $datainsert );
						
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
					}
					unset($data);
				}
				fclose($handle);
				
				
						 
			
		} 
		return $out;
	}
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Webgains', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Webgains', 'deactivation' ) );