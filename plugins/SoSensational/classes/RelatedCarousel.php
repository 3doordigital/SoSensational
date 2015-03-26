<?php
require_once 'inc/RelatedCarousel/Carousel.php';

class RelatedCarousel
{

    private $imgDimensions = array( 'width' => 366, 'height' => 240, 'crop' => true );
    
    private $currentCategory;
    private $advertiserCategories;    
    private $dataForDisplay;

    
    public function __construct($currentCategory)
    {
        $this->currentCategory = $currentCategory;
        $this->getAdvertiserCategories();
        if ( ! empty ($this->advertiserCategories)) {
            $this->getDataForDisplay();            
        }

    }
    
    private function getAdvertiserCategories()
    {
        $name  = $this->currentCategory->name === 'Accessories Boutique' ? 'accessories' : $this->currentCategory->name;
        $args = array(      
            'post_type'   =>  array('advertisers_cats'),
            'post_status' =>  array('publish', 'draft'),
            'numberposts' =>  9,
            'ss_category' =>  $name,
            'orderby'     =>  'rand'
        );                
        
        $advertiserCategories = get_posts( $args );         
        
        if ( ! empty($advertiserCategories)) {            
            $this->advertiserCategories = $advertiserCategories;                        
            return true;
        } else {
            $this->advertiserCategories = false;            
        }
        
        return false;
        
    }
    
    private function getDataForDisplay()
    {
        
        global $wpdb;
        
        $advertiserTmp = array();
        
        foreach($this->advertiserCategories as $advertiserCategory) {     
                        
            $this->dataForDisplay[$advertiserCategory->post_title]['image'] = bfi_thumb(get_post_meta( $advertiserCategory->ID, 
                                                                            'ss_advertisers_cats_image', true ), 
                                                                            $this->imgDimensions  
                                                                    );  
            
            $this->dataForDisplay[$advertiserCategory->post_title]['description'] = get_post_meta($advertiserCategory->ID,
                                                                        'ss_advertisers_cats_description', 
                                                                        true 
                                                                        );        
            
            $this->dataForDisplay[$advertiserCategory->post_title]['advertiser'] = $wpdb->get_results( "SELECT DISTINCT * FROM {$wpdb->posts}
                                                                            WHERE(post_type='brands' OR post_type='boutiques') 
                                                                            AND post_author='{$advertiserCategory->post_author}' ", OBJECT 
                                                                            ); 
                                                                            
            $this->dataForDisplay[$advertiserCategory->post_title]['advertiserRedirectionLink'] = get_post_meta( $advertiserCategory->ID, 
                                                                                    'ss_advertisers_cats_link', 
                                                                                    true 
                                                                                    ); 
            
                                                                                                                                        
            // Skip an iteration of the loop if the advertiser has already been displayed                                   
            if ( ! isset($advertiserTmp[$this->dataForDisplay[$advertiserCategory->post_title]['advertiser'][0]->post_title]) ) {
                $advertiserTmp[$this->dataForDisplay[$advertiserCategory->post_title]['advertiser'][0]->post_title] = true;            
            } else {
                continue;
            }  
            
        }
    }
    
    public function displayCarousel()
    {
        new RelatedCarousel\Carousel($this->dataForDisplay, $this->currentCategory);
    }
    
    
}