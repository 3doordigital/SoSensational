<?php
/**
 * Single Product Rating
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' )
	return;

$count   = $product->get_rating_count();
$average = $product->get_average_rating();

if ( $count > 0 ) : ?>

	<div class="woocommerce-product-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
		<div class="star-rating" title="<?php printf( __( 'Rated %s out of 5', 'woocommerce' ), $average ); ?>">
			<?php
				switch($average) {
					case 0 :
						echo '<span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span>';
						break;
					case 0.5 :
						echo '<span class="fa fa-star-half-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span>';
						break;
					case 1 :
						echo '<span class="fa fa-star"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span>';
						break;
					case 1.5 :
						echo '<span class="fa fa-star"></span> <span class="fa fa-star-half-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span>';
						break;
					case 2 :
						echo '<span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star-o"> <span class="fa fa-star-o"> <span class="fa fa-star-o">';
						break;
					case 2.5 :
						echo '<span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star-half-o"> <span class="fa fa-star-o"> <span class="fa fa-star-o">';
						break;
					case 3 :
						echo '<span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star-o"> <span class="fa fa-star-o">';
						break;
					case 3.5 :
						echo '<span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star-half-o"> <span class="fa fa-star-o">';
						break;
					case 4 :
						echo '<span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star-o">';
						break;
					case 4.5 :
						echo '<span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star-half-o">';
						break;
					case 5 :
						echo '<span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star"> <span class="fa fa-star">';
						break;	
				}
			?>
            <span class="rating">
				<strong itemprop="ratingValue"><?php echo esc_html( $average ); ?></strong> <?php _e( '/5', 'woocommerce' ); ?>
			</span>
		</div>
		<a href="#reviews" class="woocommerce-review-link" rel="nofollow"><?php printf( _n( 'Read %s review', 'Read all %s reviews', $count, 'woocommerce' ), '<span itemprop="ratingCount" class="count">' . $count . '</span>' ); ?></a>
	</div>

<?php endif; ?>