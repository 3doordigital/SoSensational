<?php
/**
 * The Template for displaying all single posts.
 */

get_header(); ?>

		<div id="content">
                                <div id="content_wrapper">
            	                       <div class="contentPage">
                                        <?php echo do_shortcode('[ss_view_category]'); ?>
                                    </div>
		  </div>
                            </div>
<?php get_footer(); ?>