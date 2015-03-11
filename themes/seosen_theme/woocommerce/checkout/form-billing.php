<?php
/**
 * Checkout billing information form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="woocommerce-billing-fields">
	<?php if ( WC()->cart->ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

		<h3><?php _e( 'Billing &amp; Shipping', 'woocommerce' ); ?></h3>

	<?php else : ?>

		<h3><?php _e( 'Billing Details', 'woocommerce' ); ?></h3>

	<?php endif; ?>
	<?php
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			//print_r($current_user);
			echo '<div class="alert alert-info" role="alert">Logged in as: '.$current_user->display_name.'</div>';
		} else {
			echo '<div class="alert alert-warning" role="alert">You are not logged in, continue below to checkout as a guest or <a href="#" data-toggle="modal" data-target=".login-modal">Click Here to Login</a></div>';	
		}
	?>
    
	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>
	<?php //print_r($checkout->checkout_fields['billing']); ?>
    
	<?php 
		$i = 0;
		foreach ( $checkout->checkout_fields['billing'] as $key => $field ) : 

			$field['class'] = array('form-group');
			$field['input_class'] = array('form-control', 'col-md-12');
			if($i == 1) {
				$field['label_class'] = array('col-md-6', 'control-label');
			} else {
				$field['label_class'] = array('col-md-12', 'control-label');
			}
			if($i ==0) {
				echo '<div class="row">';
			}
				echo '<div class="col-md-11">';	
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); 
				echo '</div>';
			if($i == 1) {
				echo '</div>';
				$i = 0;
			} elseif ($i==0) {
				$i++;	
			}
			
		endforeach; ?>

	<?php do_action('woocommerce_after_checkout_billing_form', $checkout ); ?>
	<?php if ( ! is_user_logged_in() && $checkout->enable_signup ) : ?>

		<?php if ( $checkout->enable_guest_checkout ) : ?>

			<p class="form-row form-row-wide create-account">
				<input class="input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true) ?> type="checkbox" name="createaccount" value="1" /> <label for="createaccount" ><?php _e( 'Create an account?', 'woocommerce' ); ?></label>
			</p>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( ! empty( $checkout->checkout_fields['account'] ) ) : ?>
			<div class="row">
                <div class="create-account col-md-24">
    
                    <p><?php _e( 'Create an account by entering the information below.', 'woocommerce' ); ?></p>
    
                    <?php foreach ( $checkout->checkout_fields['account'] as $key => $field ) : 
                    
                        $field['class'] = array('form-group');
                        $field['input_class'] = array('form-control', 'col-md-16');
                        $field['label_class'] = array('col-sm-8', 'control-label');				
                    
                    ?>
                       <div class="col-md-11">	
                        <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
    					</div>
                    <?php endforeach; ?>
    
                    <div class="clear"></div>
    
                </div>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>

	<?php endif; ?>
	

		

	
</div>