<?php
/**
 * The Template for displaying all single posts.
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>
					<h2 class="productTitle"><?php the_title(); ?></h2>
		                                           <p>Website: <?php echo get_post_meta( get_the_ID(), 'ss_product_link', true ); ?></p>
		                                           <p>Price: <?php echo get_post_meta( get_the_ID(), 'ss_product_price', true ); ?></p>
		                                           <p><img src="<?php echo get_post_meta( get_the_ID(), 'ss_product_image', true ); ?>" /></p>
		                                           <p>IF autor =  <a href="http://thestreets93.com.hr/add-product/?action=edit&product_id=<?php  the_ID(); ?>">Edit </a>
				<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>