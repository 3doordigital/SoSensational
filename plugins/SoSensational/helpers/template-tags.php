<?php

/**
 * Helper function for the SoSensational plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
 */


/**
 * A template tag that displays a carousel with random advertisers from the
 * surrent category
 * 
 * @param stdClass $currentCategory The currently selected category
 */
function displayRelatedAdvertisersCarousel($currentCategory)
{          
    $args = array(      
      'post_type'   =>  array('advertisers_cats'),
      'post_status' =>  array('publish', 'draft'),
      'numberposts' =>  9,
      'ss_category' =>  $currentCategory->name
    );

    $advertiserCategories = get_posts( $args );    
    $imgDimensions = array( 'width' => 366, 'height' => 240, 'crop' => true ); 
    $advertiserTmp = array();
    
    global $wpdb;
    echo '<hr>';
    
    if ( ! empty($advertiserCategories) ) {
        echo "<h1>See More $currentCategory->name in Brands & Boutiques</h1>";          
    }
  
    echo '<div class="flexslider">';
        echo '<ul class="slides">';
    
    foreach ($advertiserCategories as $advertiserCategory) {
        $image = bfi_thumb( get_post_meta( $advertiserCategory->ID, 'ss_advertisers_cats_image', true ), $imgDimensions );  
        $description = get_post_meta( $advertiserCategory->ID, 'ss_advertisers_cats_description', true );        
        $advertiser = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts}
                                           WHERE(post_type='brands' OR post_type='boutiques') 
                                           AND post_author='{$advertiserCategory->post_author}' ", OBJECT );  
        
        // Skip an iteration of the loop if the advertiser has already been displayed                                   
        if ( ! isset($advertiserTmp[$advertiser[0]->post_title]) ) {
            $advertiserTmp[$advertiser[0]->post_title] = true;            
        } else {
            continue;
        }                                          

        
        $advertiserRedirectionLink = get_post_meta( $advertiserCategory->ID, 'ss_advertisers_cats_link', true );                                           
                                           
        if (strlen($description) > 180) {
            // truncate string
            $shortDescription = substr($description, 0, 180);
            // make sure the string ends in a word
            $description = substr($shortDescription, 0, strrpos($shortDescription, ' '));     
            $description = $description . '...';
        }
            $description = $description . "<br/><a href='" . get_site_url() . '/brands-and-boutiques/' . $advertiser[0]->post_name . "'>Read More</a>";
        ?>
        <li>
            <div class='related-item ss_border'>
                <a href='<?php echo get_site_url() . '/brands-and-boutiques/' . $advertiser[0]->post_name?>'>
                    <img src='<?php echo $image; ?>' />
                    <div class='title-bar'><h2><?php echo $advertiser[0]->post_title; ?></h2></div>
                </a>           
                <div class='related-description'>
                    <p><?php echo $description; ?></p>
                </div>
                <a href="<?php echo $advertiserRedirectionLink ?>" class='button_ss large_ss'>Visit Website</a>
            </div>     
        </li>
    <?php
    }
    ?>
        </div> <!-- .slides -->
    </div>' <!-- .flexslider -->
<?php  
}

function displayFeaturedAdvertisers($currentCategory)
{
    $args = array(
        'numberposts'   =>  -1,
        'post_type'     =>  array('brands', 'boutiques'),
        'meta_key'      =>  '_categories_featured',
    );
    
    /**
     * Select all advertisers that have been featured
     */
    $advertisers = get_posts($args);
    
    /**
     * Extract each advertiser's meta data to see where he should be featured
     */
    foreach ($advertisers as $advertiser) {
        $meta[$advertiser->ID] = get_post_meta($advertiser->ID, '_categories_featured', true);
    }    
    
    if (!$meta) {
        return;
    }
    
    /**
     * Stop the script if no advertiser is featured in the current category
     */
    if ( ! in_array_r($currentCategory[0]->term_id, $meta)) {
        exit();
    }
    
    /**
     * Find advertisers featured in the current category
     */
    foreach ($meta as $key => $values) {
        foreach ($values as $value) {
            if ($value === $currentCategory[0]->term_id) {
                $featuredAdvertisersIds[] = $key;
            }
        }
    }
    echo '<hr>';
    echo '<h1>Featured Brands</h1>';
    echo '<div class="flexslider">';
        echo '<ul class="slides">';  
            foreach($featuredAdvertisersIds as $advertiserId) {
                $advertiserData = get_post($advertiserId);
                $advertiserMeta = get_post_meta($advertiserId);
            ?>    
                
                <li>
                    <div class='related-item ss_border'>
                        <a href='<?php echo get_site_url() . '/brands-and-boutiques/' . $advertiserData->post_name?>'>
                            <img src='<?php echo $advertiserMeta['ss_logo'][0];  ?>' />
                            <div class='title-bar'><h2><?php echo $advertiserData->post_title; ?></h2></div>
                        </a>           
                    </div>     
                </li>                
                
            <?php
            }            
        echo '</div>'; // .slides
    echo '</div>';  // .flexslider
    

}