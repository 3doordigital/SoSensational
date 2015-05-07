<?php 
/*
Template Name: Contact Us
*/
?>
<?php get_header(); ?>
<div class="container contact_page">
    <h1><span><?php the_title(); ?></span></h1>
    <?php
    if (function_exists('yoast_breadcrumb')) {
        //yoast_breadcrumb('<div id="breadcrumbs">', '</div>');
    }
    ?>
	<div id="breadcrumbs" class="breadcrumbs" xmlns:v="http://rdf.data-vocabulary.org/#">
		<?php if(function_exists('bcn_display'))
        {
            bcn_display();
        }?>
    </div>
</div>
<div class="container">

    <div class="row">
        <div class="col-md-7" id="content">
            <?php the_content(); ?>

        </div>
        <div class="col-md-9" id="content">
            <?php echo do_shortcode('[contact-form-7 id="12481" title="Contact form 1"]'); ?>
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-7">
            <?php dynamic_sidebar('contact_sidebar'); ?> 
        </div>
    </div>
</div>
<?php get_footer(); ?>