<?php
/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
$title = get_comment_meta( $comment->comment_ID, 'comment_title', true );
?>
<li itemprop="reviews" itemscope itemtype="http://schema.org/Review" <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">

	<div id="comment-<?php comment_ID(); ?>" class="comment_container">

		<div class="comment-text">
		
			<?php if ( $rating && get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) : ?>

				<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating pull-right" title="<?php echo sprintf( __( 'Rated %d out of 5', 'woocommerce' ), $rating ) ?>">
					<?php
						switch($rating) {
							case 0 :
								echo '0';
								break;
							case 1 :
								echo '<span class="fa fa-star"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span> </span><span class="fa fa-star-o"></span>';
								break;
							case 2 :
								echo '<span class="fa fa-star"></span> <span class="fa fa-star"> </span><span class="fa fa-star-o"> </span><span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span>';
								break;
							case 3 :
								echo '<span class="fa fa-star"></span> <span class="fa fa-star"></span> <span class="fa fa-star"></span> <span class="fa fa-star-o"></span> <span class="fa fa-star-o"></span>';
								break;
							case 4 :
								echo '<span class="fa fa-star"></span> <span class="fa fa-star"> </span><span class="fa fa-star"> </span><span class="fa fa-star"></span> <span class="fa fa-star-o"></span>';
								break;
							case 5 :
								echo '<span class="fa fa-star"></span> <span class="fa fa-star"></span> <span class="fa fa-star"> </span><span class="fa fa-star"></span> <span class="fa fa-star"></span>';
								break;	
						}
					?>
                    <span class="text-rating">
                    	<span itemprop="ratingValue"><?php echo $rating; ?></span>
                    </span>
				</div>
			
			<?php endif; ?>
			<h1><?php echo $title; ?></h1>
			<?php if ( $comment->comment_approved == '0' ) : ?>

				<p class="meta"><em><?php _e( 'Your comment is awaiting approval', 'woocommerce' ); ?></em></p>

			<?php else : ?>

				<p class="meta">
					<span itemprop="author"><?php comment_author(); ?></span> <?php

						if ( get_option( 'woocommerce_review_rating_verification_label' ) === 'yes' )
							if ( wc_customer_bought_product( $comment->comment_author_email, $comment->user_id, $comment->comment_post_ID ) )
								echo '<em class="verified">(' . __( 'verified owner', 'woocommerce' ) . ')</em> ';

					?>&ndash; <time itemprop="datePublished" datetime="<?php echo get_comment_date( 'c' ); ?>"><?php echo get_comment_date( __( get_option( 'date_format' ), 'woocommerce' ) ); ?></time>:
				</p>

			<?php endif; ?>

			<div itemprop="description" class="description"><?php comment_text(); ?></div>
		</div>
	</div>
