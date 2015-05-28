<?php
	if( !function_exists( 'print_var' ) ) {
		function print_var($var) {
			echo '<pre>'.print_r($var, true).'</pre>';   
		}
	}