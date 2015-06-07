<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>

<form action="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" method="post">
<?php do_action( 'woocommerce_before_cart_table' ); ?>
<!--<button type="submit" class="button  btn btn-primary pull-right updatecart" name="update_cart"><span class="fa fa-refresh"></span></button>-->
<table class="shop_table cart table" cellspacing="0">
	<thead>
		<tr>
			
			<th class="product-thumbnail">&nbsp;</th>
			<th class="product-name"></th>
			<th class="product-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
			<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="product-subtotal"><?php _e( 'Total', 'woocommerce' ); ?></th>
            <th class="product-remove">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

					

					<td class="product-thumbnail">
						<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

							if ( ! $_product->is_visible() )
								echo $thumbnail;
							else
								printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
						?>
					</td>

					<td class="product-name">
                    <?php
						$attributes = $_product->get_attributes('contents');
						//print_r($attributes);
					?>
						<?php
							if ( ! $_product->is_visible() ) {
								echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key );
								echo '<span class="cart_attr">'. $attributes['contents']['value'] .'</span>';
							} else {
								echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', $_product->get_permalink(), $_product->get_title() ), $cart_item, $cart_item_key );
							}
							// Meta data
							echo WC()->cart->get_item_data( $cart_item );

               				// Backorder notification
               				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
               					echo '<p class="backorder_notification">' . __( 'Available on backorder', 'woocommerce' ) . '</p>';
						?>
					</td>

					<td class="product-price">
						<?php
							echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						?>
					</td>

					<td class="product-quantity">
						<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
							} else {
								$product_quantity = woocommerce_quantity_input( array(
									'input_name'  => "cart[{$cart_item_key}][qty]",
									'input_value' => $cart_item['quantity'],
									'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
									'min_value'   => '0'
								), $_product, false );
							}

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
						?>
                        
					</td>

					<td class="product-subtotal">
						<?php
							echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						?>
					</td>
                    
                    <td class="product-remove">
						<?php
							echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s"><span class="fa fa-times-circle-o"></span></a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
						?>
                        
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_cart_contents' );
		?>
        </tbody>
        </table>
		
<div class="row cartfooter">
<div class="col-md-14">
	<div id="boosterCart">
    	<h1>Are You a First Time Buyer?</h1>
        <h2>Speed up your memory improvement with our new Lipogen PA.</h2>
        	<div class="col-xs-6"><?php echo get_the_post_thumbnail( 92, 'thumbnail', array('class' => 'img-responsive') ); ?> </div>
            <div class="col-xs-18 infobox">
            	<h3>ADD OUR MONTH ONE BOOSTER KIT</h3>
                
                <p>50% OFF and No Extra Shipping!</p>
                <?php if(tdd_in_cart(92) == false) { ?>
                <button type="submit"
                    data-quantity="1" data-product_id="92"
                    class="btn btn-default add_to_cart_button product_type_simple pull-right">
                    Add Booster
                </button>
                <?php } else { ?>
                	<button type="submit" disabled class="pull-right btn btn-default">Already Added!</button>
                <?php } ?>
				<p>ONLY $39.95!</p>
                
            </div>
        	<div class="col-xs-12">= Optimal Brian Power</div>
            <div class="col-xs-12 textright" ><a href="#">Learn more about Lipogen PA <i class="fa fa-question-circle"></i></a></div>
            <div class="clearfix"></div>
    </div>
				<?php if ( WC()->cart->coupons_enabled() ) { ?>
					<div class="coupon">

						<h2>Coupon Code</h2>
                        <p>If you have a coupon code, enter it in the box below and click 'Go'.</p>
                        <div class="input-group">
                          <input type="text" class="form-control" id="coupon_code" name="coupon_code" value="" placeholder="<?php _e( 'Coupon code', 'woocommerce' ); ?>">
                          <span class="input-group-btn">
                            <input type="submit" class="btn btn-primary" name="apply_coupon" value="<?php _e( 'Apply', 'woocommerce' ); ?>" />
                          </span>
                        </div><!-- /input-group -->
                        
						<?php do_action('woocommerce_cart_coupon'); ?>

					</div>
				<?php } ?>
</div>
<div class="col-md-10">
				

				<?php wp_nonce_field( 'woocommerce-cart' ); ?>
			

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>


<?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<div class="cart-collaterals">

	<?php do_action( 'woocommerce_cart_collaterals' ); ?>

	<?php woocommerce_cart_totals(); ?>

	<?php woocommerce_shipping_calculator(); ?>
    
    <input type="submit" class="checkout-button button alt wc-forward btn btn-primary pull-right" name="proceed" value="<?php _e( 'Proceed to Checkout', 'woocommerce' ); ?>" />
	 
				<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
                <?php wp_nonce_field( 'woocommerce-cart' ); ?>

</div>
</div>
</div>
<hr>
<div class="row">
<div class="col-md-9 continueshopp">
<a href="/buy-now/"><i class="fa fa-chevron-left"></i> Continue Shopping</a>
</div>
<div class="col-md-5 col-md-offset-4">
</div>
</div>
<?php do_action( 'woocommerce_after_cart' ); ?>
