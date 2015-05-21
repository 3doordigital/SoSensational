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
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Awin', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Awin', 'deactivation' ) );