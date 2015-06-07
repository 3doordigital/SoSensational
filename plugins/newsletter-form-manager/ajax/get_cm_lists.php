<?php
	define('WP_USE_THEMES', false);
	require($_SERVER['DOCUMENT_ROOT'].'./wp-blog-header.php');
	if( $_REQUEST['action'] == 'clients' ) {
		require_once ( '../campaign-monitor/csrest_general.php' );
		$option = get_option( 'wp_newsletter_man' );
		$apikey = ( isset( $option['cm-api'] ) ? $option['cm-api'] : 'db073d15d60ca2279ed792532264c19e' );
		
		$auth = array( 'api_key' => $apikey );
		$wrap = new CS_REST_General( $auth );
		
		$result = $wrap->get_clients();
		
		echo "Result of /api/v3.1/clients\n<br />";
		if($result->was_successful()) {
			echo '
					<th>Select Client</th>
					<td>
						<select>';
						foreach( $result->response as $res ) {
							echo '<option value="'.$res->ClientID.'">'.$res->Name.'</option>';
						}
						echo '</select>
					</td>';
					
		} else {
			echo 'Failed with code '.$result->http_status_code."\n<br /><pre>";
			var_dump($result->response);
		}
		echo '</pre>';
	}	