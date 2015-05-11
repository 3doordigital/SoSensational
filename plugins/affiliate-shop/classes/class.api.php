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
			$output = $temp;
			foreach( $temp as $key=>$input ) {
				if( isset( $input['items'] ) ) {
					//$output['items'] = array_replace( $output['items'], $input['items'] );
				}
				if( isset( $input['total'] ) ) {
					//$output['total'] =  array_replace( $output['total'], $input['total'] );;
				}
			}
			
			
			
			uasort( $output['items'], array( $this, 'usort_reorder' ) );
			
			foreach( $output['total'] as $value ) {
				$output['total']['total'] = $output['total']['total'] + $value;
			}
			print_var ($output);
			//return $output;
			
			
			
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
					$totalCount = $xml->TotalMatches;
				}
				$brands = $this->linkshare_merchants();
				
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
						'price'     => number_format( $price, 2, '.', '' ),
						'rrp'       => number_format( $rrp, 2, '.', ''  ),
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
		
		public function update_product( $id = null, $prod_id = null, $aff = null, $title = null, $merch = null ) {
			
			$data = array();
			
			if( !isset( $aff ) || $aff == null || $aff == '' ) {
				
				//$data = $this->update_awin_product( $prod_id, $title, $merch );		
				if( $data['status'] == 0 ) {
					//$data = $this->update_linkshare_product( $prod_id, $title, $merch );		
				}
			} else {
				switch( $aff ) {
					case 'awin' :
						//$data = $this->update_awin_product( $prod_id, $title, $merch );
						break;
					case 'linkshare' :
						//$data = $this->update_linkshare_product( $prod_id, $title, $merch );
						break;	
				}
			}
			
			if( isset( $id ) && $id != '' && $id != null ) {
				$oldprice = get_post_meta( $id, 'wp_aff_product_price', true );
				$newprice = str_replace( ',', '', $oldprice );	
				update_post_meta( $id, 'wp_aff_product_price', $newprice );
				
				$oldrrp = get_post_meta( $id, 'wp_aff_product_rrp', true );
				$newrrp = str_replace( ',', '', $oldrrp );	
				update_post_meta( $id, 'wp_aff_product_rrp', $newrrp );
				
				if( $newprice < $newrrp ) {
					update_post_meta( $id, 'wp_aff_product_sale', 1 );	
					$sale = 1;
				} else {
					update_post_meta( $id, 'wp_aff_product_sale', 0 );	
					$sale = 0;
				}
			}
			
			$out = '';
			if( !empty( $data['item'] ) ) {
				foreach( $data['item'] as $item ) {
				if( ( $item['price'] == '' || $item['price'] == 0 || $item['price'] == '0.00' ) && $item['rrp'] != '' ) {
					update_post_meta($id, 'wp_aff_product_price', $item['rrp']);
				} else {
					update_post_meta($id, 'wp_aff_product_price', $item['price']);
				}
				
				
				update_post_meta($id, 'wp_aff_product_id', $item['ID']);
				update_post_meta($id, 'wp_aff_product_aff', $item['aff']);
				update_post_meta($id, 'wp_aff_product_rrp', $item['rrp']);
				update_post_meta($id, 'wp_aff_product_merch', ( $item['aff'] == 'linkshare' ? ( array ) $item['merch'][0] : $item['merch'] ) );
				
					$out .= '<tr>
								<td><a href="/wp-admin/post.php?post='.$id.'&action=edit">Post ID: '.$id.'</a></td>
								<td>'.$title;
					if( isset( $prod_id ) && $prod_id != '' && $prod_id != null ){
						$out .= ' ('.$prod_id.')';
					}
					$out .= '</td>
								<td>'.$merch.'</td>
								<td>'.$item['ID'].'</td>
								<td>'.$item['title'].'</td>
								<td>'.$item['aff'].'</td>
								<td>'.$item['foundby'].'</td>
								<td><i class="fa fa-check"></i></td>
								<td>'.( $sale == 1 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>' ).'</td>
							 </tr>';
				// Do something with $data
				}
			} else {
				//wp_trash_post( $id  );
				$out .= '<tr>
								<td><a href="/wp-admin/post.php?post='.$id.'&action=edit">Post ID: '.$id.'</a></td>
								<td>'.$title.'</td>
								<td>'.$merch.'</td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td><i class="fa fa-close"></i></td>
								<td>'.( $sale == 1 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>' ).'</td>
							 </tr>';	
			}
			return $out;
			
			
		}
		
		private function update_awin_product( $id, $title, $merch ) {
			
			//echo 'Search: '.$title;
			$merchants = $this->awin_merchants();
			$merchid = '';
			foreach( $merchants as $merchant ) {
				//echo $merch.' :: '.$merchant['name'] ;
				if( strtolower( $merch ) == strtolower( $merchant['name'] ) ) {
					$merchid = $merchant['ID'];
				}
			}
			if( isset( $id ) && $id != '' && $id != null ){
			
				$params = array( 'iProductId'	=> array( $id ), 'iAdult' => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ) );
			
				$client = ClientFactory::getClient();
				$response = $client->call('getProduct', $params);
				
				$foundby = 'ID';
				
			} elseif( isset( $title ) ) {
				
				$foundby = 'title';
				
				//echo $merch.'('.$merchid.')';
				$oRefineBy = new stdClass();
					$oRefineBy->iId = 3;
					$oRefineBy->sName = '';
					
					$oRefineByDefinition = new stdClass();
					$oRefineByDefinition->sId = $merchid;
					$oRefineByDefinition->sName = '';
					
					$oRefineBy->oRefineByDefinition = $oRefineByDefinition;
					$title = str_replace( array( '-', '*', ',' ), '', $title );
					$title = explode( "'", $title );
					$title = $title[0];
					$params = array("sQuery" => stripslashes($title), "iLimit" => 1, "bAdult" => false, 'sColumnToReturn' => array('sAwImageUrl', 'sMerchantImageUrl', 'sBrand', 'sDescription', 'fRrpPrice' ),  "oActiveRefineByGroup"	=>	$oRefineBy);
					//print_var($params);
					$client = ClientFactory::getClient();
					$response = $client->call('getProductList', $params);
			} else {
				$data['status'] = 0;
				return $data;
			}
				
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
								'price'     => number_format($product->fPrice, 2, '.', '' ),
								'rrp'       => number_format($product->fRrpPrice, 2, '.', '' ),
								'link'      => addslashes($product->sAwDeepLink),
								'merch' 	=> $merchid,
								'foundby'	=> $foundby
							);
							return $data;
					} else {
						//$data['item'] = $this->update_linkshare_product( $id, $lstitle, $lsmerch );
						$data['status'] = 0;
						return $data;
					}
				}
				
			} else {
				//$data['item'] = $test;
				//$data['item'] = $this->update_linkshare_product( $id, $lstitle, $lsmerch );
				if( isset( $id ) && $id != '' && $id != null ){ 
					$this->update_awin_product( null, $title, $merch );
				} else {
					$data['status'] = 0;
					return $data;
				}
			}
		}
		
		private function update_linkshare_product( $id, $title, $merch ) {
			$title = trim( str_replace( array( '-', '*', ',', '%5C', '%27', "'", '\\', '/' ), '', $title ) );
			
			//$title=urlencode($title);
			
			$url = 'http://productsearch.linksynergy.com/productsearch';
			$token = $this->option['linkshare']; //Change this to your token
			$resturl = $url."?"."token=".$token."&"."exact=".$title."&max=1";
			
			$brands = $this->linkshare_merchants();
			$merchid = null;		
			foreach( $brands as $brand ) {
				//echo $merch.' :: '.$merchant['name'] ;
				$brand['name'] = ( string ) $brand['name'];
				
				//echo 'Brand Search: '.strtolower( $merch ) .' ::: '.strtolower( $brand['name'] );
				$match = strcmp ( strtolower( trim( $merch ) ), strtolower( trim( $brand['name'] ) ) );

				if( $match == 0 ) {
					$brand = (string) $brand['ID'];
					$merchid = $brand;
				} 
			}
			
			if( $merchid != NULL && $merchid != '' ) {
				$resturl .= '&mid='.$merchid;
			}
			//echo $resturl. ' ::::::::::::M:'. $merch .' :::::::::::::::::MID:'.$merchid;
			$SafeQuery = urlencode($resturl);
			$xml = simplexml_load_file($SafeQuery);
			//print_var( $xml );
			if ( $xml ) {
				$array = array();
				
				if( $xml->TotalMatches > 0 ) {
					$array['status'] = 1;
				} else {
					$array['status'] = 0;
					return $array;
				}
				if( is_array( $xml->item ) ) {
					$items = $xml->item;
				} else {
					$items[0] = $xml->item;
				}
				foreach ( $items as $item) {
					$foundby = 'title';
					$saleprice = (string) $item->saleprice;
					$normalprice = (string) $item->price;
					
					if( isset( $saleprice) && $saleprice != '' ) {
						$rrp = $normalprice;
						$price = $saleprice;	
					} else {
						$rrp = $normalprice;
						$price = $normalprice;						
					}
					$id = (array) $item->linkid;
					/*if( in_array( $id, $this->get_all_products() ) ) {
						$exists = 1;
					} else {
						$exists = 0;
					}*/
					
					$mid = ( string ) $item->mid;
					
					$brand = $brands['ID-'.$mid]['name'];
						$array['item'][] = array(
							'ID'        => addslashes($item->linkid),
							'aff'     	=> 'linkshare',    
							'title'     => addslashes( trim( ucwords( strtolower( $item->productname ) ) ) ),
							'brand'     => addslashes( $brand ),
							'img'       => addslashes( $item->imageurl ),
							'desc'      => addslashes( $item->description->short ),
							'price'     => number_format( $price, 2, '.', '' ),
							'rrp'       => number_format( $rrp, 2, '.', ''  ),
							'link'      => addslashes( $item->linkurl )	,
							'merch' 	=> $mid,
							'foundby'	=> $foundby
						);
				}
			} else {
				$array['status'] = 0;
			}
			//print_var( $array );
			return $array;
		}
		
		private function usort_reorder( $a, $b ){
			$orderby = ( !empty( $this->sortby ) ) ? $this->sortby : 'title'; //If no sort, default to title
			$order = ( !empty( $this->sort ) ) ? $this->sort : 'asc'; //If no order, default to asc
			$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
			return ( $order==='asc' ) ? $result : -$result; //Send final sort direction to usort
		}
		
	}