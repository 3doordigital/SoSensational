<?php

/**
 * Helper function for the SoSensational plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
 */

function displayRelatedAdvertisersCarousel($currentCategory)
{
    $fcarousel = new RelatedCarousel($currentCategory);
    $carousel = $fcarousel->getCarousel();
    $carousel->display();
}

function displayFeaturedAdvertisers($currentCategory) 
{
    $fcarousel = new FeaturedCarousel($currentCategory);
    $carousel = $fcarousel->getCarousel();
    $carousel->display();
}


function displayFeaturedAdvertisersXXX($currentCategory)
{
       
    $imgDimensions = array( 'width' => 366, 'height' => 240, 'crop' => true );     
    
    $args = array(
        'numberposts'   =>  9,
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
    
    /**
     * Return if there are no featured advertisers for a given category
     */
    if (!isset($meta) || empty($meta)) {
        return;
    }
    
    /**
     * Stop the script if no advertiser is featured in the current category
     */
    if ( ! in_array_r($currentCategory[0]->term_id, $meta)) {
        return;
    }
    
    /**
     * Find advertisers featured in the current category
     */
    foreach ($meta as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $v) {
                if ($v === $currentCategory[0]->term_id) {
                    $featuredAdvertisersIds[] = $key;
                }
            }            
        } else {
            $featuredAdvertisersIds[] = $key;            
        }
    }
    
    echo '<hr>';
    echo '<h1>Featured Brands</h1>';
    echo '<div class="flexslider">';
        echo '<ul class="slides">';  
            foreach($featuredAdvertisersIds as $advertiserId) {
                $advertiserData = get_post($advertiserId);
                
 
                $args = array(      
                  'post_type'   =>  array('advertisers_cats'),
                  'post_status' =>  array('publish', 'draft'),
                  'ss_category' =>  $currentCategory[0]->name,
                  'author' =>  $advertiserData->post_author
                ); 
                
                $featuredAdvertiserCategory = get_posts($args);
                
                /**
                 * Skip this iteration if the brand does not have an image for this category
                 */
                if( ! isset ($featuredAdvertiserCategory[0]->ID)) {                    
                    continue;
                }                      
                $image = bfi_thumb( get_post_meta( $featuredAdvertiserCategory[0]->ID, 'ss_advertisers_cats_image', true ), $imgDimensions );                      
            ?>    
                
                <li>
                    <div class='related-item ss_border'>
                        <a href='<?php echo get_site_url() . '/brands-and-boutiques/' . $advertiserData->post_name; ?>'>
                            <img src='<?php echo $image;  ?>' />
                            <div class='title-bar'><h2><?php echo $advertiserData->post_title; ?></h2></div>
                        </a>           
                    </div>     
                </li>                
                
            <?php                    
            }     
            
        echo '</div>'; // .slides
    echo '</div>';  // .flexslider
    

}