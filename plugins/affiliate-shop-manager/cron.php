<?php
	define('WP_USE_THEMES', false);
	require($_SERVER['DOCUMENT_ROOT'].'/wp-blog-header.php');
	global $feed_man;

	$feed_man->cron_process();