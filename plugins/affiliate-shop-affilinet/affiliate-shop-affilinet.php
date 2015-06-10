<?php
/*
	Plugin Name: Affiliate Shop &raquo; Affilinet API
	Plugin URI: 
	Description: Affilinet API for WP Affiliate Shop
	Version: 1.0.0
	Author: 3 Door Digital
	Author URI: http://www.3doordigital.com
	License: GPL V3
*/
/**
* Adds Affilinet API to Affiliate Shop
*
*
* @copyright  2015 3 Door Digital
* @license    GPL v3
* @version    Release: 1.0.0
* @since      Class available since Release 1.0.0
*/

class WordPress_Affiliate_Shop_Affilinet {
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
			$array['apis']['affilinet'] = array(
				'name' 		=> 'affilinet',
				'nicename'	=> 'Affilinet',
				'class' 	=> 'WordPress_Affiliate_Shop_Affilinet'
			);
		} else {
			$array = $wp_aff->get_option();
			$array['apis']['affilinet'] = array(
				'name' 		=> 'affilinet',
				'nicename'	=> 'Affilinet',
				'class' 	=> 'WordPress_Affiliate_Shop_Affilinet'
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
		unset( $array['apis']['affilinet'] );
		update_option( $wp_aff->option_name, $array );
	}
	
	
		
	/**
	* Returns array of merchants
	*
	* @return array	$array
	*/ 
	
	public function merchants() {
		
		
		$logon = "https://api.affili.net/V2.0/Logon.svc?wsdl";
		$stats = "https://api.affili.net/V2.0/PublisherProgram.svc?wsdl";
		
		$Username   = "705085"; // the publisher ID
		$Password   = "CCyNKACOOXZCBhwmYbwT"; // the publisher web services password
		
		$SOAP_LOGON = new SoapClient($logon);
		$Token      = $SOAP_LOGON->Logon(array(
					 'Username'  => $Username,
					 'Password'  => $Password,
					 'WebServiceType' => 'Publisher'
					 ));
		
		$params = array(
				   'Query' => '' 
				  );
		
		$SOAP_REQUEST = new SoapClient($stats);
		$req = $SOAP_REQUEST->GetMyPrograms(array(
					'CredentialToken' => $Token,
					'GetProgramsRequestMessage' => $params
					));
		
		//print_var($req);
		foreach ($req->Programs->ProgramSummary as $item) {
			
			$array['ID-'.$item->ProgramId] = array(
				'ID'        => ( string ) $item->ProgramId,
				'name'     	=> ( string ) $item->ProgramTitle,
				'aff'     	=> 'affilinet',
			);
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
		$out['success'] = 0;
		$out['error'] = 0;
		$out = array();
		$upload_dir = wp_upload_dir(); 
		$user_dirname = $upload_dir['basedir'].'/feed-data';
		if( ! file_exists( $user_dirname ) )
			wp_mkdir_p( $user_dirname );

		$local_file = $user_dirname.'/local.xml.gz';
		$uc_local_file = $user_dirname.'/local1.xml';
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
			$fp1 = fopen($uc_local_file, "w");
			$fp = gzopen( $local_file, "r");
			while ($line = gzread($fp,1024)) {
				fwrite($fp1, $line, strlen($line));
			}
			fclose( $fp1 );
			gzclose($fp);
			
			//$xml = simplexml_load_file( $uc_local_file );
			$reader = new XMLReader();
			$reader->open($uc_local_file);
			
			while ($reader->read() && $reader->name !== 'product');
			while ($reader->name === 'product')
			{
				$product = simplexml_load_string($reader->readOuterXML());
				
			
				//print_var( $product );
				if( isset( $product->price->sale ) && $product->price->sale < $product->price->retail ) {
					$price = number_format( (int) $product->price->sale, 2, '.', '' );	
					$rrp = number_format( (int) $product->price->retail, 2, '.', '' );
				} else {
					$price = number_format( (int) $product->price->retail, 2, '.', '' );
					$rrp = number_format( (int) $product->price->retail, 2, '.', '' );
				}
				
				$data = array(
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
				global $wpdb;
				//print_var($product);
				$table_name = $wpdb->prefix . "feed_data";
				$replace = $wpdb->insert( $table_name, array( 
						'product_id' => $merchant.'_'.$data['ID'], 
						'product_aff' => $data['aff'],
						'product_merch' => $merchant,
						'product_title' => $data['title'],
						'product_brand' => $merch,
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
				unset( $data );
				$reader->next('product');
			}
			//print_var( $data );
			
			
		} else {
			$out['status'] = 0;
			$out['message']	= 'FTP Failed';
		}
		return $out;
	}
}
register_activation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Affilinet', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WordPress_Affiliate_Shop_Affilinet', 'deactivation' ) );