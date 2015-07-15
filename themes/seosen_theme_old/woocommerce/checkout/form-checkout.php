<?php
/**
 * Checkout Form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
	return;
}

// filter hook for include new pages inside the payment method
$get_checkout_url = apply_filters( 'woocommerce_get_checkout_url', WC()->cart->get_checkout_url() ); ?>

<div class="modal fade login-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">Login</h4>
          </div>
          <div class="modal-body">
            <form id="ajaxlogin" action="login" method="post"><h3 class="h1"><!?php _e('Login To', 'blueleaf') ?></h3>

            <p><label for="username"><?php _e('Email', 'blueleaf'); ?></label> <input class="form-control" id="username" name="username" type="text" ></p>
            <p><label for="password"><?php _e('Password', 'blueleaf'); ?></label> <input class="form-control" id="password" name="password" type="password" ></p>
    		
            
             <p><i class="fa fa-lock"></i> <a class="register" href="<?php bloginfo('url'); ?>/register/"><?php _e('Register', 'blueleaf'); ?></a></p>
               
                	
               
            
            
            <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
            
        
          </div>
          <div class="modal-footer">
            <a class="lost pull-left" href="<?php echo wp_lostpassword_url(); ?>"><i class="fa fa-question-circle"></i> <?php _e('Forgot password?' , 'blueleaf'); ?></a> 
            <button name="submit" type="submit" class="btn btn-primary" value="<?php _e('Login', 'blueleaf') ?>"><?php _e('Login', 'blueleaf') ?></button>
            </form>
          </div>
      
    </div>
  </div>
</div>

<form name="checkout" method="post" class="checkout form-horizontal" action="<?php echo esc_url( $get_checkout_url ); ?>" enctype="multipart/form-data">
	<?php if ( sizeof( $checkout->checkout_fields ) > 0 ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="panel-group" id="accordion">
  <!--<div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
          Step 1 : Sign Up/Guest Checkout
        </a>
        <div class="fa fa-pencil pull-right"></div>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse <?php if ( !is_user_logged_in() ) echo 'in'; ?>">
      <div class="panel-body">
        <div class="row">
        <?php
				if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
					echo '<div class="col-md-18"><p>You are logged in please continue.</p><p><a href="#collapseTwo" class="btn btn-default" data-toggle="collapse" data-parent="#accordion">
									Next Step
								</a></p></div>';	
				} else {
			?>
        	<div class="col-md-9 newcust">
            	<h2>I'm A New Customer</h2>
                <p>Register with us for a faster checkout, to track the status of your order and more. You can also checkout as a guest.</p>
                <div class="radio">
                  <label>
                    <input type="radio" name="optionsRadios" class="createaccount" value="no" checked>
                    Checkout as guest
                  </label>
                </div>
                <div class="radio">
                  <label>
                    <input type="radio" name="optionsRadios" class="createaccount" value="yes">
                    Register an account
                  </label>
                </div>
                <?php if ( ! empty( $checkout->checkout_fields['account'] ) ) : ?>
<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>
			<div class="create-account-new">

				<p><?php _e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'woocommerce' ); ?></p>

				<?php foreach ( $checkout->checkout_fields['account'] as $key => $field ) : ?>

					<?php 
						$field['class'] = array('form-group');
						$field['input_class'] = array('form-control');
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); 
					?>

				<?php endforeach; ?>
<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
				<div class="clear"></div>

			</div>

		<?php endif; ?>
                <a href="#collapseTwo" class="btn btn-default" data-toggle="collapse" data-parent="#accordion">
          			Continue <span class="fa fa-chevron-right"></span>
        		</a>
            </div>
            
            <div class="col-md-9 retcust">
            	<h2>I'm A Returning Customer</h2>
                <?php
					woocommerce_login_form(
						array(
							'message'  => __( 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer please proceed to the Billing &amp; Shipping section.', 'woocommerce' ),
							'redirect' => get_permalink( wc_get_page_id( 'checkout' ) ),
							'hidden'   => false
						)
					);
				?>
            </div>
            <?php } ?>
        </div>
      	</div>
    </div>
  </div>-->
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          Step 1 : Billing/Shipping Details
        </a>
        <div class="fa fa-pencil pull-right"></div>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse in">
      <div class="panel-body">
        <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
		<?php do_action( 'woocommerce_checkout_billing' ); ?>
        <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
        <?php do_action( 'woocommerce_checkout_shipping' ); ?>
        <a href="#collapseThree" class="btn btn-primary" data-toggle="collapse" data-parent="#accordion">
          Next Step <span class="fa fa-chevron-right"></span>
        </a>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
          Step 2 : Delivery Options
        </a>
        <div class="fa fa-pencil pull-right"></div>
      </h4>
    </div>
    <div id="collapseThree" class="panel-collapse collapse">
      <div class="panel-body">
      <table>

				<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

				<?php wc_cart_totals_shipping_html(); ?>

				<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

            </table>
            <a href="#collapseFour" class="btn btn-primary" data-toggle="collapse" data-parent="#accordion">
          Next Step <span class="fa fa-chevron-right"></span>
        </a>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">
          Step 3 : Order Confirmation/Payment
        </a>
        <div class="fa fa-pencil pull-right"></div>
      </h4>
    </div>
    <div id="collapseFour" class="panel-collapse collapse">
      <div class="panel-body">
      	
        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
      </div>
    </div>
  </div>
</div>

	<?php endif; ?>

	<?php //do_action( 'woocommerce_checkout_order_review' ); ?>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>