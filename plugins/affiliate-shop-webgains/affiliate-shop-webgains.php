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
		
		$url = 'http://findadvertisers.linksynergy.com/merchantsearch';
		$token = $this->option['linkshare']; 
		$resturl = $url."?"."token=".$token;
		$SafeQuery = urlencode($resturl);
		$xml = simplexml_load_file($SafeQuery);
		if ( $xml ) {
			$array = array();
			
			
			foreach ($xml->midlist->merchant as $item) {
				$mid = ( array ) $item->mid;
				$mname = ( array ) $item->merchantname;
				
				$array['ID-'.$item->mid] = array(
					'ID'        => ( string ) $item->mid,
					'name'     	=> ( string ) $item->merchantname,
					'aff'     	=> 'linkshare',
				);
			}
		}
		return $array;
		
	}
	
	/**
	* Retrieves feed from affilaite for a merchant ($merchant) and replaces the entry in the database.
	*
	* @param  string 	$merchant 	The ID of the merchant
	* 
	* @return array	$out
	*/ 
	public function update_feed( $merchant, $merch ) {
		
		$out = array();
		$upload_dir = wp_upload_dir(); 
		$user_dirname = $upload_dir['basedir'].'/feed-data';
		if( ! file_exists( $user_dirname ) )
			wp_mkdir_p( $user_dirname );

		$local_file = $user_dirname.'/local.xml.gz';
		$uc_local_file = $user_dirname.'/webgainsproducts.csv';
		$server_file = $merchant.'_2476350_mp.xml.gz';
		$contents = '';
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
			$header = fgetcsv($handle, 0, '|');
			print_var( $header );
			$out['status'] = 1;	
			$i = 0 ;
			// loop through the file line-by-line
			while(($data = fgetcsv($handle, 0, '|')) !== false && $i < 5 )
			{
				if( $data[3] != '' && $data[8] != '' ) {
					set_time_limit(0);
					print_var( $data );
					flush();
					$i ++;
				}
				/*$table_name = $wpdb->prefix . "feed_data";
				$replace = $wpdb->replace( $table_name, array( 
						'product_id' => $data[11].'_'.$data[0], 
						'product_aff' => 'awin',
						'product_merch' => sanitize_text_field( $data[11] ),
						'product_title' => sanitize_text_field( $data[7] ),
						'product_brand' => sanitize_text_field( $data[10] ),
						'product_image' => esc_url( $data[9] ),
						'product_desc' => sanitize_text_field( $data[6] ),
						'product_price' => $data[5],
						'product_rrp' => $data[23],
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
						$out['message'][] = 'Inserted '.$merchant.'_'.$data['ID'];
						$out['success'] ++;
						break;
					default :
						$out['message'][] = 'Replaced '.$merchant.'_'.$data['ID'];
						break;	
				}*/
				
				unset($data);
			}
			fclose($handle);
		}
		return $out;
	}
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Webgains', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Webgains', 'deactivation' ) );