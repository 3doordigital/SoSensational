<?php
	define('WP_USE_THEMES', false);
	require('./wp-blog-header.php');
	require( 'affiliate-shop-manager.php' );

	$feed_man->cron_process();