<?php 
/*
Template Name: Wide Page
*/
?>
<?php get_header(); ?>
<div class="container">
    <h1><span><?php the_title(); ?></span></h1>
    <?php 
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
        } 
    ?>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-19" id="content">
            <?php the_content(); ?>
        </div>
        <div class="col-md-3 col-md-offset-2">
            <?php dynamic_sidebar('deals_sidebar'); ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
