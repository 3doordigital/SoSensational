<?php

	class wpAffAPI {
		
		public function __construct() {
			global $wp_aff;
			$this->option = $wp_aff->get_option();
			
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
			
		}
		
		private function get_all_products() {
			
			$args = array(
				'post_type' => 'wp_aff_products',
				'posts_per_page' => -1, 
				'post_status' => 'publish'
			 );
			$postslist = get_posts( $args );
			
			$posts = array();
			
			foreach( $postslist as $post ) {
				$meta = get_post_meta( $post->ID, 'wp_aff_product_id', true );
				$posts[] = $meta;
			}
			return $posts;
		}
		
		public function search( $term = '', $api, $merchant = NULL,  $depth = 25, $page = 1, $sortby = 'title', $sort = 'asc') {
			
			$temp = array();
			
			$this->sortby = $sortby;
			$this->sort = $sort;
			
			$this->all_products = $this->get_all_products();
			
			if( $api == 'awin' || $api == 'all' ) {
				$temp[] = $this->awin_search( $term, $merchant, $depth, $page, $sortby, $sort );
			}
			
			if( $api == 'linkshare' || $api == 'all' ) {
				$temp[] = $this->linkshare_search( $term, $merchant, $depth, $page, $sortby, $sort );
			}

			$output = array();	
			$output['items'] = array();
			$output['total'] = array();	
			$output['total']['total'] = '';
			
			foreach( $temp as $key=>$input ) {
				if( isset( $input['items'] ) ) {
					$output['items'] = array_replace( $output['items'], $input['items'] );
				}
				if( isset( $input['total'] ) ) {
					$output['total'] =  array_replace( $output['total'], $input['total'] );;
				}
			}
			
			
			
			uasort( $output['items'], array( $this, 'usort_reorder' ) );
			
			foreach( $output['total'] as $value ) {
				$output['total']['total'] = $output['total']['total'] + $value;
			}
			
			return $output;
			
		}
		
		private function awin_search( $term, $merchant, $depth, $page, $sortby, $sort ) {
			
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
				
				//$params = array("sQuery" => $term, "sSort" => $_sort, "iLimit" => $depth, "iOffset" => $offset, "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ));
				
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

						if( in_array( $id, $this->all_products ) ) {
							$exists = 1;
						} else {
							$exists = 0;
						}
						//echo '<pre>'.print_r($merch, true).'</pre>';
						
						$array['items']['ID-'.$id] = array(
								'ID'        => addslashes($product->iId),
								'aff'     => 'awin',    
								'title'     => addslashes ( trim( ucwords( strtolower( $product->sName ) ) ) ),
								'brand'     => addslashes($merch->oMerchant->sName),
								'img'       => addslashes($product->sAwImageUrl),
								'desc'      => addslashes($product->sDescription),
								'price'     => number_format($product->fPrice, 2),
								'rrp'       => number_format($product->fRrpPrice, 2),
								'link'      => addslashes($product->sAwDeepLink),
								'exists'	=> $exists
							);
					}
				}
				
				return $array;
			}
		}
		
		private function linkshare_search( $term, $merchant, $depth, $page, $sortby, $sort  ) {
			
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
			$token = "4bee73f0e12eb04b83e7c5d01a5b8e4a7ccf0e1fbdeec4f171a2e5ca4fe2a568"; //Change this to your token
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
					$totalCount = $xml->TotalMatches;
				}
				$brands = $this->linkshare_merchants();
				
				$array['total']['linkshare'] = $totalCount;
				foreach ($xml->item as $item) {
					$saleprice = (array) $item->saleprice;
					$normalprice = (array) $item->price;
					
					if( isset( $saleprice[0] ) && $saleprice[0] != '' ) {
						$rrp = $normalprice[0];
						$price = $saleprice[0];	
					} else {
						$rrp = $normalprice[0];
						$price = $normalprice[0];						
					}
					$id = (array) $item->linkid;
					if( in_array( $id, $this->all_products ) ) {
						$exists = 1;
					} else {
						$exists = 0;
					}
					
					$brand = $brands['ID-'.$item->mid]['name'];
					$array['items']['ID-'.$id[0]] = array(
						'ID'        => addslashes($item->linkid),
						'aff'     	=> 'linkshare',    
						'title'     => addslashes( trim( ucwords( strtolower( $item->productname ) ) ) ),
						'brand'     => addslashes( $brand ),
						'img'       => addslashes( $item->imageurl ),
						'desc'      => addslashes( $item->description->short ),
						'price'     => number_format( $price, 2),
						'rrp'       => number_format( $rrp, 2 ),
						'link'      => addslashes( $item->linkurl )	,
						'exists'	=> $exists
					);
				}
			}
			return $array;
		}
		
		public function get_merchants( $api, $merchant, $array = false ) {
			
			$temp = array();
			
			$this->sortby = 'name';
			$this->sort = 'asc';
			
			if( $api == 'awin' || $api == 'all' ) {
				$temp[] = $this->awin_merchants( $merchant );
			}
			
			if( $api == 'linkshare' || $api == 'all' ) {
				$temp[] = $this->linkshare_merchants( $merchant );
			}

			$output = array();	
				
			foreach( $temp as $input ) {
				$output = array_replace( $output, $input );
			}
			
			
			uasort( $output, array( $this, 'usort_reorder' ) );
			if( $array == false ) {
				$echo =  '';
				foreach( $output as $item ) {
					$echo .= '<option '.( $merchant == $item['ID'] ? ' selected ' : '' ).'value="'.$item['ID'].'">'.$item['name'].' ('.$item['aff'].')</option>';
				}
				
				echo $echo;
			} else {
				return $output;	
			}
						
		}
		
		private function awin_merchants() {
			
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
		
		public function linkshare_merchants() {
			
			$url = 'http://findadvertisers.linksynergy.com/merchantsearch';
			$token = "4bee73f0e12eb04b83e7c5d01a5b8e4a7ccf0e1fbdeec4f171a2e5ca4fe2a568"; //Change this to your token
			$resturl = $url."?"."token=".$token;
			$SafeQuery = urlencode($resturl);
			$xml = simplexml_load_file($SafeQuery);
			if ( $xml ) {
				$array = array();
				foreach ($xml->midlist->merchant as $item) {
					$array['ID-'.$item->mid] = array(
						'ID'        => $item->mid,
						'name'     	=> $item->merchantname,    
						'aff'     	=> 'linkshare',
					);
				}
			}
			return $array;
			
		}
		
		public function update_product( $id = null, $prod_id = null, $aff = null, $title = null, $merch = null ) {
			//if( !isset( $aff ) || $aff == null || $aff == '' ) {
				$data = $this->update_awin_product( $prod_id, $title, $merch );
			/*	if( $data['status'] == 0 ) {
					//$data = $this->update_linkshare_product( $id );	
				}
			} else {
				switch( $aff ) {
					case 'awin' :
						$data = $this->update_awin_product( $id );
						break;
					case 'linkshare' :
						//$data = $this->update_linkshare_product( $id );
						break;	
				}
			}
			*/
			$out = '';
			if( !empty( $data['item'] ) ) {
				foreach( $data['item'] as $item ) {
				/*update_post_meta($id, 'wp_aff_product_id', $item['ID']);
				update_post_meta($id, 'wp_aff_product_aff', $item['aff']);
				update_post_meta($id, 'wp_aff_product_price', $item['price']);
				update_post_meta($id, 'wp_aff_product_rrp', $item['rrp']);
				update_post_meta($id, 'wp_aff_product_merch', ( array ) $item['merch'][0]);*/
				
					$out .= '<tr>
								<td><a href="/wp-admin/post.php?post='.$id.'&action=edit">Post ID: '.$id.'</a></td>
								<td>'.$title.'</td>
								<td>'.$item['ID'].'</td>
								<td>'.$item['title'].'</td>
								<td>'.$item['aff'].'</td>
								<td>Updated!</td>
							 </tr>';
				// Do something with $data
				}
			} else {
				$out .= '<tr>
								<td><a href="/wp-admin/post.php?post='.$id.'&action=edit">Post ID: '.$id.'</a></td>
								<td>'.$title.'</td>
								<td colspan="4">No data found</td>
							 </tr>';	
			}
			return $out;
			
			
		}
		
		private function update_awin_product( $id, $title, $merch ) {
			//echo 'Search: '.$title;
			/*$params = array( 'iProductId'	=> array( $id ), 'iAdult' => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ) );
			
			print_var($params);
			$client = ClientFactory::getClient();
			print_var($client);
			$response = $client->call('getProduct', $params);
			*/
			$lsmerch = $merch;
			$merchants = $this->awin_merchants();
			foreach( $merchants as $merchant ) {
				//echo $merch.' :: '.$merchant['name'] ;
				if( strtolower( $merch ) == strtolower( $merchant['name'] ) ) {
					$merchid = $merchant['ID'];
				} else {
					$merchid = '';
				}
			}
			//echo $merch.'('.$merchid.')';
			$oRefineBy = new stdClass();
				$oRefineBy->iId = 3;
				$oRefineBy->sName = '';
				
				$oRefineByDefinition = new stdClass();
				$oRefineByDefinition->sId = $merchid;
				$oRefineByDefinition->sName = '';
				
				$oRefineBy->oRefineByDefinition = $oRefineByDefinition;
				$title = explode( "'", $title );
				$title = $title[0];
				$params = array("sQuery" => stripslashes($title), "iLimit" => 1, "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ),  "oActiveRefineByGroup"	=>	$oRefineBy);
				//print_var($params);
				$client = ClientFactory::getClient();
				$response = $client->call('getProductList', $params);
				
			//print_var($response);
			$data = array();
			
			$test = (array) $response;
				
			if( !empty( $test ) ) {
				$data['status'] = 1;
				if( is_array( $response->oProduct ) ) {
					$products = $response->oProduct;
				} else {
					$products[] = $response->oProduct;	
				}
				foreach($products AS $product) {
					$merchparams = array('iMerchantId'	=> $product->iMerchantId);
					$merch = $client->call('getMerchant', $merchparams);
					//echo '<pre>'.print_r($merch, true).'</pre>';
					$id = $product->iId;
					
					if( substr( strtolower( $title ), 0, 5 ) == substr( strtolower( $product->sName  ), 0, 5 ) ) {
					
					$data['item'][] = array(
							'ID'        => addslashes($product->iId),
							'aff'     => 'awin',    
							'title'     => addslashes ( trim( ucwords( strtolower( $product->sName ) ) ) ),
							'brand'     => addslashes($merch->oMerchant->sName),
							'img'       => addslashes($product->sAwImageUrl),
							'desc'      => addslashes($product->sDescription),
							'price'     => number_format($product->fPrice, 2),
							'rrp'       => number_format($product->fRrpPrice, 2),
							'link'      => addslashes($product->sAwDeepLink),
							'merch' 	=> $merchid
						);
					} else {
						$data['item'] = $this->update_linkshare_product( $id, $title, $lsmerch );
					}
				}
				
			} else {
				//$data['item'] = $test;
				$data['item'] = $this->update_linkshare_product( $id, $title, $lsmerch );
				//$data['status'] = 0;
				//print_var($data);
			}
			
			return $data;
			
		}
		
		private function update_linkshare_product( $id, $title, $merch ) {
			
			$title=urlencode($title);
			
			$url = 'http://productsearch.linksynergy.com/productsearch';
			$token = "4bee73f0e12eb04b83e7c5d01a5b8e4a7ccf0e1fbdeec4f171a2e5ca4fe2a568"; //Change this to your token
			$resturl = $url."?"."token=".$token."&"."exact=".$title."&max=1";
			
			$brands = $this->linkshare_merchants();
			
			foreach( $brands as $brand ) {
				//echo $merch.' :: '.$merchant['name'] ;
				if( strtolower( $merch ) == strtolower( $brand['name'] ) ) {
					$merchid = $brand['ID'];
				} else {
					$merchid = '';
				}
			}
			
			if( $merchid != NULL && $merchid != 0 ) {
				//$resturl .= '&mid='.$merchid;
			}
						echo $resturl. ' :::::::::::: '. $merch .' :::::::::::::::::';
			$SafeQuery = urlencode($resturl);
			$xml = simplexml_load_file($SafeQuery);
			//print_var( $xml );
			if ( $xml ) {
				$array = array();
				
				if( $xml->TotalMatches == -1 ) {
					$totalCount = 4000;
				} else {
					$totalCount = $xml->TotalMatches;
				}
				
				foreach ($xml->item as $item) {
					$saleprice = (array) $item->saleprice;
					$normalprice = (array) $item->price;
					
					if( isset( $saleprice[0] ) && $saleprice[0] != '' ) {
						$rrp = $normalprice[0];
						$price = $saleprice[0];	
					} else {
						$rrp = $normalprice[0];
						$price = $normalprice[0];						
					}
					$id = (array) $item->linkid;
					if( in_array( $id, $this->all_products ) ) {
						$exists = 1;
					} else {
						$exists = 0;
					}
					
					$brand = $brands['ID-'.$item->mid]['name'];
					$array['ID-'.$id[0]] = array(
						'ID'        => addslashes($item->linkid),
						'aff'     	=> 'linkshare',    
						'title'     => addslashes( trim( ucwords( strtolower( $item->productname ) ) ) ),
						'brand'     => addslashes( $brand ),
						'img'       => addslashes( $item->imageurl ),
						'desc'      => addslashes( $item->description->short ),
						'price'     => number_format( $price, 2),
						'rrp'       => number_format( $rrp, 2 ),
						'link'      => addslashes( $item->linkurl )	,
						'exists'	=> $exists,
						'merch' 	=> $item->mid
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