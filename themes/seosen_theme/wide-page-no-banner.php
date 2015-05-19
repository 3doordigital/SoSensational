<?php 
/*
Template Name: Wide Page No Banner
*/
?>
<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-24" id="content">
            <?php the_content(); 
            $c = the_content();
            var_dump($c);
            
            ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>