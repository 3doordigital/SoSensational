<?php

	class wpAffAPI {
		
		public function __construct() {
			global $wp_aff;
			$this->option = $wp_aff->get_option();
		}
		
		public function search( $term = '', $apis = array(), $depth = 100, $page = 1, $sortby = 'title', $sort = 'asc') {
			
			$temp = array();
			
			$this->sortby = $sortby;
			$this->sort = $sort;
			
			if( in_array( 'awin', $apis ) || in_array( 'all', $apis ) ) {
				$temp[] = $this->awin_search( $term );
			}
			
			if( in_array( 'linkshare', $apis ) || in_array( 'all', $apis ) ) {
				$temp[] = $this->linkshare_search( $term );
			}
			
			$output = array();	
				
			foreach( $temp as $input ) {
				$output = array_merge( $output, $input );
			}
			
			
			usort( $output, array( $this, 'usort_reorder' ) );
			
			return $output;
			
		}
		
		private function awin_search( $term, $depth = 100, $page = 1 ) {
			
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
			require_once('class.ClientFactory.php');
			
			$array = array();
			
			$params = array("sQuery"	=> $term, "iLimit" => $depth, "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ));
			
			$client = ClientFactory::getClient();
    		$response = $client->call('getProductList', $params);

			foreach($response->oProduct AS $product) {
				$merchparams = array('iMerchantId'	=> $product->iMerchantId);
				$merch = $client->call('getMerchant', $merchparams);
				//echo '<pre>'.print_r($merch, true).'</pre>';
				$array[] = array(
						'ID'        => addslashes($product->iId),
						'aff'     => 'awin',    
						'title'     => addslashes ( trim( ucwords( strtolower( $product->sName ) ) ) ),
						'brand'     => addslashes($merch->oMerchant->sName),
						'img'       => addslashes($product->sAwImageUrl),
						'desc'      => addslashes($product->sDescription),
						'price'     => number_format($product->fPrice, 2),
						'rrp'       => number_format($product->fRrpPrice, 2),
						'link'      => addslashes($product->sAwDeepLink)
					);
			}
			
			return $array;
		}
		
		private function linkshare_search( $term, $depth = 100, $page = 1 ) {
			$url = 'http://productsearch.linksynergy.com/productsearch';
			$token = "4bee73f0e12eb04b83e7c5d01a5b8e4a7ccf0e1fbdeec4f171a2e5ca4fe2a568"; //Change this to your token
			$resturl = $url."?"."token=".$token."&"."keyword=".$term."&max=".$depth;
			$SafeQuery = urlencode($resturl);
			$xml = simplexml_load_file($SafeQuery);
			if ( $xml ) {
				$array = array();
				
				foreach ($xml->item as $item) {
					$price = (array) $item->price;
					$array[] = array(
						'ID'        => addslashes($item->linkid),
						'aff'     	=> 'linkshare',    
						'title'     => addslashes( trim( ucwords( strtolower( $item->productname ) ) ) ),
						'brand'     => addslashes($item->mid),
						'img'       => addslashes($item->imageurl),
						'desc'      => addslashes($item->description->short),
						'price'     => number_format( $price[0], 2),
						'rrp'       => '',
						'link'      => addslashes($item->linkurl)	
					);
				}
			}
			return $array;
		}
		
		private function usort_reorder( $a, $b ){
			$orderby = ( !empty( $this->sortby ) ) ? $this->sortby : 'title'; //If no sort, default to title
			$order = ( !empty( $this->sort ) ) ? $this->sort : 'asc'; //If no order, default to asc
			$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
			return ( $order==='asc' ) ? $result : -$result; //Send final sort direction to usort
		}
		
	}