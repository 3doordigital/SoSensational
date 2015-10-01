<?php
/** Template name:  List all categories */

get_header(); ?>

		<div id="content">
                                <div id="content_wrapper">
            	                       <div class="contentPage">
                                        <?php require_once (SOSENSATIONAL_DIR.'/web/show-categories.php'); ?>
                                    </div>
		  </div>
                            </div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
