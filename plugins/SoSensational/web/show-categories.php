<?php

do_action('ss_css');
$cat_params = array( 'width' => 367, 'height' => 240, 'crop' => true ); 
global $wpdb;
$categories=$wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
    LEFT JOIN {$wpdb->terms} as wpt
    ON wpt.term_id=wptt.term_id
    WHERE wptt.taxonomy='ss_category' ", OBJECT);
    

       
       
?>

    <h1><span><?php the_title(); ?></span></h1>
    <?php 
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb('<div id="breadcrumbs">','</div>');
        } 
    ?>
    
        <h4 class="ss_title-category-list "></h4>

            <div class="row margintop">

        <?php
           $counterCategories = 1;   
           $counterColor = 1;
            foreach($categories as $category):
               
            if($category->parent==0):?>
                
                <?php
                    /**
                     * Generate a redirect slug when a parent category has only one child.
                     * If this is the case, redurect the user directly to the child category
                     */
                    foreach($categories as $childCategory) {
                        if ($childCategory->parent == $category->term_id) {
                            $subCategoires[$category->slug][] = $childCategory->slug;
                        }
                    }
                    $redirectSlug = count($subCategoires[$category->slug]) === 1 ? $subCategoires[$category->slug][0] : '';
                ?>
    
                    <?php $children = get_term_children($category->term_id, get_query_var('taxonomy')); // get children 
                          $term_meta = get_option( "taxonomy_$category->term_id" );
               ?>
                   <div class="col-md-8 fadebox showme animated fadeIn" style="visibility: visible;">
               
                                <a href="<?php echo get_site_url().'/brands-and-boutiques/'. $category->slug.'/' . $redirectSlug; ?>" class="aHolderImgSS">
                 
                 				<?php $image =  bfi_thumb( $term_meta['ss_cat_image'], $cat_params ); ?>

                                <img src="<?php echo $image; ?>" class="img-responsive" />   
                          
                          
                                   <?php if($counterColor % 2): echo '<div class="whitebar  ss_whitebar" style="display: block;">'; else: echo '<div class="blackbar ss_blackbar" style="display: block;">'; endif; ?> 
                                        <h2><span> <?php echo $category->name;?></span></h2>
                                        
                                    </div>
                            </a>

                    </div>
                    <?php $counterColor++; $counterCategories++;  if($counterCategories == 4): $counterCategories=1; endif; ?>
                    <?php if($counterCategories == 1): echo '</div> <div class="row margintop">'; endif; ?>
          <?php
            endif;            
        ?>
    <?php endforeach; ?>

    <div class="ss_clear"></div>
</div>
