<?php
/*
	Plugin Name: Affiliate Shop &raquo; Linkshare API
	Plugin URI: 
	Description: Linkshare API for WP Affiliate Shop
	Version: 1.0.0
	Author: 3 Door Digital
	Author URI: http://www.3doordigital.com
	License: GPL V3
*/
/**
* Adds Linkshare API to Affiliate Shop
*
*
* @copyright  2015 3 Door Digital
* @license    GPL v3
* @version    Release: 1.0.0
* @since      Class available since Release 1.0.0
*/ 
class WordPress_Affiliate_Shop_Linkshare {
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
			$array['apis']['linkshare'] = array(
				'name' 		=> 'linkshare',
				'nicename'	=> 'Linkshare',
				'class' 	=> 'WordPress_Affiliate_Shop_Linkshare'
			);
		} else {
			$array = $wp_aff->get_option();
			$array['apis']['linkshare'] = array(
				'name' 		=> 'awin',
				'nicename'	=> 'Linkshare',
				'class' 	=> 'WordPress_Affiliate_Shop_Linkshare'
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
		unset( $array['apis']['linkshare'] );
		update_option( $wp_aff->option_name, $array );
	}
	
	/**
	* Take search term and outputs a formatted array
	*
	* @param  string	$term  		This is the search term
	* @param  string  	$merchant 	The merchant ID (if required)
	* @param  string  	$depth 		Number of results to return
	* @param  string  	$page 		The page number to return
	* @param  string  	$sortby 	The field to sort by
	* @param  string  	$sort 		How to sort
	* 
	* @return array		$array 
	*/ 
	public function search( $term, $merchant = null, $depth = 100, $page = 1, $sortby, $sort = 'asc' ) {
			
			switch ( $sortby ) {
				case 'title' :
					$_sortby = 'productname';
					break;
				case 'price' :
					$_sortby = 'retailprice';
					break;
				case 'relevance' :
					$_sortby = 'productname';
			}
			
			$url = 'http://productsearch.linksynergy.com/productsearch';
			$token = $this->option['linkshare']; //Change this to your token
			$resturl = $url."?"."token=".$token."&"."keyword=".$term."&max=".$depth."&pagenumber=".$page;
			if( $merchant != NULL && $merchant != 0 ) {
				$resturl .= '&mid='.$merchant;
			}
			
			$resturl .= "&sort=".$_sortby."&sorttype=".$sort;
			//echo $resturl;
			$SafeQuery = urlencode($resturl);
			$xml = simplexml_load_file($SafeQuery);

			if ( $xml ) {
				$array = array();
				
				if( $xml->TotalMatches == -1 ) {
					$totalCount = 4000;
				} else {
					$totalCount = (int) $xml->TotalMatches;
				}
				$brands = $this->merchants();
				
				$array['total']['linkshare'] = $totalCount;
				foreach ($xml->item as $item) {
					$saleprice = (string) $item->saleprice;
					$normalprice = (string) $item->price;
					
					if( isset( $saleprice ) && $saleprice != '' ) {
						$rrp = $normalprice;
						$price = $saleprice;	
					} else {
						$rrp = $normalprice;
						$price = $normalprice;						
					}
					$id = (string) $item->linkid;
					/*$allp_attr = array(
							'post_type' => 'wp_aff_products',
							'meta_key'	=> 'wp_aff_product_id',
							'meta_value' => $id
						);
						
						$appp_query = new WP_Query( $allp_attr );
						
						if( $appp_query->have_posts() ) {
							$exists = 1;
						} else {
							$exists = 0;
						}*/
					$exists = 0;
					$brand = $brands['ID-'.$item->mid]['name'];
					$array['items']['ID-'.$id] = array(
						'ID'        => addslashes($item->linkid),
						'aff'     	=> 'linkshare',    
						'title'     => addslashes( trim( ucwords( strtolower( $item->productname ) ) ) ),
						'brand'     => addslashes( $brand ),
						'img'       => addslashes( $item->imageurl ),
						'desc'      => addslashes( $item->description->short ),
						'price'     => number_format( $price, 2, '.', '' ),
						'rrp'       => number_format( $rrp, 2, '.', ''  ),
						'link'      => addslashes( $item->linkurl )	,
						'exists'	=> $exists
					);
				}
			}
			return $array;
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
		public function feed_data( $merchant ) {
			$out = array();
			$upload_dir = wp_upload_dir(); 
			$user_dirname = $upload_dir['basedir'].'/feed-data';
			if( ! file_exists( $user_dirname ) )
				wp_mkdir_p( $user_dirname );
	
			$local_file = $user_dirname.'/local.xml.gz';
			$server_file = $merchant.'_2476350_mp.xml.gz';
			$contents = '';
			$data = array();
			
			$conn_id = @ftp_connect('aftp.linksynergy.com');
			$login_result = @ftp_login($conn_id, 'cyndylessing', 'zbrbZdyk');
					
			if (@ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
				if ( function_exists( 'ini_set' ) ) {
					@ini_set('memory_limit', '2048M');
				}
				$out['status'] = 1;
				ftp_close($conn_id);
				
				$fp = gzopen( $local_file, "r");
				while ($line = gzgets($fp,1024)) {
					$contents .= $line;
				}
				gzclose($fp);
				
				$xml = simplexml_load_string( $contents );
				foreach( $xml->product as $product ) {
					if( isset( $product->price->sale ) && $product->price->sale < $product->price->retail ) {
						$price = number_format( (int) $product->price->sale, 2, '.', '' );	
						$rrp = number_format( (int) $product->price->retail, 2, '.', '' );
					} else {
						$price = number_format( (int) $product->price->retail, 2, '.', '' );
						$rrp = number_format( (int) $product->price->retail, 2, '.', '' );
					}
					
					$data[] = array(
						'ID'        => (string) $product['product_id'],
						'aff'     	=> 'linkshare',    
						'title'     => trim( ucwords( strtolower( (string) $product['name'] ) ) ),
						'brand'     => trim( ucwords( strtolower( (string) $xml->header->merchantName ) ) ),
						'img'       => (string) $product->URL->productImage,
						'desc'      => (string) $product->description->short,
						'price'     => $price,
						'rrp'       => $rrp,
						'link'      => (string) $product->URL->product
					);
				}
				//print_var( $data );
				global $wpdb;
				foreach( $data as $product ) {
					//print_var($product);
					$table_name = $wpdb->prefix . "feed_data";
					$replace = $wpdb->replace( $table_name, array( 
							'product_id' => $merchant.'_'.$product['ID'], 
							'product_aff' => $product['aff'],
							'product_merch' => $merchant,
							'product_title' => $product['title'],
							'product_brand' => $product['brand'],
							'product_image' => $product['img'],
							'product_desc' => $product['desc'],
							'product_price' => $product['price'],
							'product_rrp' => $product['rrp'],
							'product_link' => $product['link'], 
						)
					);
					
					switch ($replace) {
						case false :
							if( is_wp_error( $replace ) ) {
								$out['status'] = 0;
								$out['message'][] = $replace->get_error_message();
							}
							break;
						case 1 :
							$out['message'][] = 'Inserted '.$product['ID'];
							break;
						default :
							$out['message'][] = 'Replaced '.$product['ID'];
							break;	
					}
				}
				
			} else {
				$out['status'] = 0;
				$out['message']	= 'FTP Failed';
			}
			return $out;
		}

		/**
		* Calls $this->feed_data
		*
		* @param  string	$ID	  The ID of the merchant to be updated.
		* 
		* @return array
		*/ 		
		public function update_feed( $ID ) {
			return $this->feed_data( $ID );
		}
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Linkshare', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Linkshare', 'deactivation' ) );