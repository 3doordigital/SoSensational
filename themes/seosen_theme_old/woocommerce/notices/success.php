<?php
/**
 * Show messages
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! $messages ) return;
?>

<?php foreach ( $messages as $message ) : ?>
<?php if ( strpos( $message, 'added to your cart' ) === false ) : ?>
	<div class="woocommerce-message alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><?php echo wp_kses_post( $message ); ?></div>
<?php endif; ?>    
<?php endforeach; ?>
