<?php
/*
	Plugin Name: Affiliate Shop &raquo; Affiliate Window API
	Plugin URI: 
	Description: Affiliate Window API for WP Affiliate Shop
	Version: 1.0.0
	Author: 3 Door Digital
	Author URI: http://www.3doordigital.com
	License: GPL V3
*/
/**
* Adds Affilate Window API to Affiliate Shop
*
*
* @copyright  2015 3 Door Digital
* @license    GPL v3
* @version    Release: 1.0.0
* @since      Class available since Release 1.0.0
*/ 
class WordPress_Affiliate_Shop_Awin {
	static $options	;
	public function __construct() {
		global $wp_aff;
		$this->option = $wp_aff->get_option();
		self::$options = $wp_aff->get_option();
		
		ini_set("soap.wsdl_cache_enabled", 0);
		define('API_VERSION', 3);
		define('API_USER_TYPE', 'affiliate'); // (affiliate || merchant)
		define('API_KEY', $this->option['awin'] );
		define('PS_WSDL', 'http://v'.API_VERSION.'.core.com.productserve.com/ProductServeService.wsdl');
		define('PS_NAMESPACE', 'http://api.productserve.com/');
		define('PS_SOAP_TRACE', false);	// turn OFF when finished testing
		define('API_WSDL', PS_WSDL);
		define('API_NAMESPACE', PS_NAMESPACE);
		define('API_SOAP_TRACE', PS_SOAP_TRACE);
		define('API', 'PS');
		require_once('classes/class.ClientFactory.php');
		
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
			$array['apis']['awin'] = array(
				'name' 		=> 'awin',
				'nicename'	=> 'Affiliate Window',
				'class' 	=> 'WordPress_Affiliate_Shop_Awin'
			);
		} else {
			$array = $wp_aff->get_option();
			$array['apis']['awin'] = array(
				'name' 		=> 'awin',
				'nicename'	=> 'Affiliate Window',
				'class' 	=> 'WordPress_Affiliate_Shop_Awin'
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
		unset( $array['apis']['awin'] );
		update_option( $wp_aff->option_name, $array );
	}
	
	public function search( $term, $merchant, $depth, $page, $sortby, $sort ) {
			
			if( $sortby == 'title' && $sort == 'asc' ) {
				$_sort = 'az';
			} elseif( $sortby == 'title' && $sort == 'desc' ) {
				$_sort = 'az';
			} elseif( $sortby = 'relevance' ) {
				$_sort = 'relevancy';	
			}
			
			$array = array();
			
			$offset = $depth * ( $page - 1);
			if( $page < ( 1000 / $depth ) ) {
			if( $merchant == NULL || $merchant == 0 ) {
				$params = array("sQuery" => $term, "sSort" => $_sort, "iLimit" => $depth, "iOffset" => $offset, "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ));
				
			} else {
				$oRefineBy = new stdClass();
				$oRefineBy->iId = 3;
				$oRefineBy->sName = '';
				
				$oRefineByDefinition = new stdClass();
				$oRefineByDefinition->sId = $merchant;
				$oRefineByDefinition->sName = '';
				
				$oRefineBy->oRefineByDefinition = $oRefineByDefinition;
				
				$params = array("sQuery" => $term, "sSort" => $_sort, "iLimit" => $depth, "iOffset" => $offset, "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ), "oActiveRefineByGroup"	=>	$oRefineBy );
				
			}
				
				$client = ClientFactory::getClient();
				$response = $client->call('getProductList', $params);
				$test = (array) $response;
				if( !empty( $test ) ) {
					if($response->iTotalCount < 1000) {
						$totalCount = $response->iTotalCount;
					} else {
						$totalCount = 1000;
					}
					
					$array['total']['awin'] = $totalCount;
					
					foreach($response->oProduct AS $product) {
						$merchparams = array('iMerchantId'	=> $product->iMerchantId);
						$merch = $client->call('getMerchant', $merchparams);
						
						$id = $product->iId;

						$exists = 0;
						$array['items']['ID-'.$id] = array(
								'ID'        => addslashes($product->iId),
								'aff'     => 'awin',    
								'title'     => addslashes ( trim( ucwords( strtolower( $product->sName ) ) ) ),
								'brand'     => addslashes($merch->oMerchant->sName),
								'img'       => addslashes($product->sAwImageUrl),
								'desc'      => addslashes($product->sDescription),
								'price'     => number_format($product->fPrice, 2, '.', '' ),
								'rrp'       => number_format($product->fRrpPrice, 2, '.', '' ),
								'link'      => addslashes($product->sAwDeepLink),
								'exists'	=> $exists
							);
					}
				}
				return $array;
			}
		}
		
		public function merchants() {
			
			$array = array();
			
			$attr = array( 'iMaxResult' => 1000 );
			
			$client = ClientFactory::getClient();
			$response = $client->call('getMerchantList', $attr);
			
			foreach( $response->oMerchant as $item ) {
				$array['ID-'.$item->iId] = array (
					'ID'	=> $item->iId,
					'name'	=> $item->sName,
					'aff'	=> 'awin'
				);	
			}
			
			return $array;
			
		}
		
		private function get_file( $mid ) {
			$source = 'http://datafeed.api.productserve.com/datafeed/download/apikey/8e0fad1a3c293b71a418d5adeedad4d7/cid/97,98,142,144,146,129,595,539,147,149,613,626,135,163,168,159,169,161,167,170,137,171,548,174,183,178,179,175,172,623,139,614,189,194,141,205,198,206,203,208,199,204,201,61,62,72,73,71,74,75,76,77,78,63,80,82,64,83,84,85,65,86,87,88,90,89,91,67,92,94,33,54,53,57,55,58,52,603,60,56,66,128,130,133,212,207,209,210,211,68,69,213,215,217,218,220,221,222,70,224,225,226,227,228,229,4,5,10,11,537,12,13,19,15,14,6,551,20,21,553,22,23,24,25,26,27,7,30,29,32,619,34,8,35,618,40,42,43,9,652,45,46,651,47,48,49,44,50,634,230,538,233,235,238,550,236,240,585,237,239,241,556,245,242,521,576,575,577,579,281,283,297,554,285,303,286,282,287,288,627,173,193,637,639,640,642,643,644,641,650,177,196,379,648,181,645,384,387,646,598,611,391,393,647,395,631,602,570,600,405,187,411,412,413,414,415,416,417,649,418,419,420,99,100,101,107,110,111,113,114,115,116,118,121,122,127,581,624,123,594,125,421,605,604,599,422,433,530,434,435,532,533,428,474,475,476,477,423,608,437,438,440,441,442,444,445,446,447,607,424,451,448,453,449,452,450,425,455,457,459,460,456,458,426,616,463,464,465,466,467,427,625,597,473,469,617,470,429,430,481,615,483,484,485,488,529,596,431,432,489,606,490,361,633,362,366,367,368,371,369,363,372,373,374,377,375,536,535,364,378,380,381,365,383,385,386,390,392,394,396,397,399,402,404,406,407,540,542,544,546,547,246,558,247,252,559,255,248,256,265,593,258,259,632,260,261,262,557,249,266,267,268,269,612,251,277,250,272,270,271,273,561,560,347,348,354,350,349,355,356,357,358,359,360,586,592,588,591,589,328,629,329,338,493,635,495,507,563,564,567,569/fid/'.$mid.'/columns/aw_product_id,merchant_product_id,merchant_category,aw_deep_link,merchant_image_url,search_price,description,product_name,merchant_deep_link,aw_image_url,merchant_name,merchant_id,category_name,category_id,delivery_cost,currency,store_price,Fashion:suitable_for,Fashion:category,Fashion:size,Fashion:material,Fashion:pattern,Fashion:swatch,rrp_price,brand_name,brand_id,number_stars,is_for_sale,in_stock,display_price,data_feed_id/format/csv/delimiter/,/compression/zip/adultcontent/1/';
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
	
			$destination = $user_dirname.'/local.xml.gz';
			$file = fopen($destination, "w+");
			fputs($file, $data);
			fclose($file);	
			
			return $destination;
		}
		
		public function update_feed( $ID ) {
			$out = array();
			$local_file = $this->get_file( $ID );
			// get the absolute path to $file
			$path = pathinfo(realpath($local_file), PATHINFO_DIRNAME);
			
			$zip = new ZipArchive;
			$res = $zip->open($local_file);
			if ($res === TRUE) {
			  // extract it to the path we determined above
			  $zip->extractTo($path);
			  for ($i = 0; $i < $zip->numFiles; $i++) {
				 $filename = $zip->getNameIndex($i);
				 // ...
			 }
			  $zip->close();
			} else {
				$out ['status'] = 0;
				return $out;
			}
			$upload_dir = wp_upload_dir(); 
			$user_dirname = $upload_dir['basedir'].'/feed-data';
			
			if(($handle = fopen( $user_dirname.'/'.$filename, 'r')) !== false)
			{
				global $wpdb;
				// get the first row, which contains the column-titles (if necessary)
				$header = fgetcsv($handle);
				// loop through the file line-by-line
				while(($data = fgetcsv($handle)) !== false)
				{
					set_time_limit(0);
					$table_name = $wpdb->prefix . "feed_data";
					$replace = $wpdb->replace( $table_name, array( 
							'product_id' => $data[11].'_'.$data[0], 
							'product_aff' => 'awin',
							'product_merch' => $data[11],
							'product_title' => $data[7],
							'product_brand' => $data[10],
							'product_image' => $data[9],
							'product_desc' => $data[6],
							'product_price' => $data[5],
							'product_rrp' => $data[23],
							'product_link' => $data[3], 
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
							$out['status'] = 1;
							$out['message'][] = 'Inserted '.$data[0];
							break;
						default :
							$out['status'] = 1;
							$out['message'][] = 'Replaced '.$data[0];
							break;	
					}
					
					unset($data);
				}
				fclose($handle);
			}
			return $out;
		}
	}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Awin', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Awin', 'deactivation' ) );