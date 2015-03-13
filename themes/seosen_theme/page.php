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
        <div class="col-md-16" id="content">
            <?php the_content(); ?>
            
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-7">
            <?php dynamic_sidebar( 'page_sidebar' ); ?> 
        </div>
    </div>
</div>
<?php get_footer(); ?>