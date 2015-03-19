<?php

/**
 * Helper function for the SoSensational plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
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
    echo "<h1>See More $currentCategory->name in Brands & Boutiques</h1>";    
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
        }

        ?>
        <li>
            <div class='related-item ss_border'>
                <a href='<?php echo get_site_url() . '/brands-and-boutiques/' . $advertiser[0]->post_name?>'>
                    <img src='<?php echo $image; ?>' />
                    <div class='title-bar'><h2><?php echo $advertiser[0]->post_title; ?></h2></div>
                </a>           
                <div class='related-description'>
                    <p><?php echo strip_tags($description); ?></p>
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