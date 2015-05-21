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
	
	public function search( $term, $merchant, $depth, $page, $sortby, $sort ) {
			
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
		
		public function merchants() {
			
			$url = 'http://findadvertisers.linksynergy.com/merchantsearch';
			$token = $this->option['linkshare']; //Change this to your token
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
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Linkshare', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Linkshare', 'deactivation' ) );