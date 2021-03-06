<?php get_header(); ?>
<div class="container">
    <h1 class="pagetitle"><span>Competitions</span></h1>
    
    <?php 
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
        } 
    ?>
</div>
<div class="container comps">
    <div class="row">
        <div class="col-md-16" id="content">
            
            <?php
                $i = 1;
                $x = 0;
                if( have_posts() ) : while( have_posts() ) : the_post();
				//print_var( get_post_meta( get_the_ID() ) );
            ?>
            
            <?php if( $i ==1 ) { ?>
                <div class="row toppost">
                    <div class="col-md-24">
                    	<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <?php sosen_post_meta(); ?>
                        <?php 
                            if ( has_post_thumbnail( ) ) { // check if the post has a Post Thumbnail assigned to it.
                                 ?>
                                <a href="<?php the_permalink(); ?>">
                                <?php
								the_post_thumbnail( 'blog-large', array( 'class' => 'img-responsive' ) );
								?>
                                </a>
                                <?php
                            } 
                        ?>
                        
                        <?php
                            the_excerpt_max_charlength(340, true);
                        ?>
                    </div>
                </div>
            <?php } else { ?>
                <?php if( $x == 0 ) { ?>
                    <div class="row">
                <?php } ?>
                    <div class="col-md-12 blog-small">
                    	
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <?php sosen_post_meta(); ?>
                        <?php 
                            if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
                                 ?>
                                <a href="<?php the_permalink(); ?>">
                                <?php
								the_post_thumbnail( 'blog-small', array( 'class' => 'img-responsive' ) );
								?>
                                </a>
                                <?php
                            } 
                        ?>
                        
                        <?php
                            the_excerpt_max_charlength(170);
                        ?>
                    </div>   
                <?php if( $x == 1 || $wp_query->post_count == $i ) { ?>
                    </div>
                    
                <?php } ?>
                <?php if( $x == 1) { $x = 0; } else { $x++; } ?>
            <?php  } 
                $i++;
            ?>
            <?php endwhile; ?>
            <?php wp_pagenavi(); ?>
            <?php endif; ?>
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-7">
            <?php dynamic_sidebar( 'blog_sidebar' ); ?> 
        </div>
    </div>
</div>
<?php get_footer(); ?>