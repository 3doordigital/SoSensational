<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

define( 'WP_ROCKET_ADVANCED_CACHE', true );
$rocket_cache_path = 'F:\GIT\sosen/wp-content/cache/wp-rocket/';
$rocket_config_path = 'F:\GIT\sosen/wp-content/wp-rocket-config/';

if ( file_exists( 'F:\GIT\sosen\wp-content\plugins\wp-rocket\inc\front/process.php' ) ) {
	include( 'F:\GIT\sosen\wp-content\plugins\wp-rocket\inc\front/process.php' );
} else {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
}