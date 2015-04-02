<?php
require_once 'inc/RelatedCarousel/Carousel.php';

class RelatedCarousel
{

    private $imgDimensions = array( 'width' => 380, 'height' => 250, 'crop' => true );
    
    private $currentCategory;
    private $advertiserCategories;    
    private $dataForDisplay;

    
    public function __construct($currentCategory)
    {
        $this->currentCategory = $currentCategory;
        $this->collectAdvertiserCategories();
        if ( ! empty ($this->advertiserCategories)) {
            $this->collectDataForDisplay();            
        }

    }
    
    private function collectAdvertiserCategories()
    {      
        global $wpdb;

        
        $categories = $wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
            LEFT JOIN {$wpdb->terms} as wpt
            ON wpt.term_id=wptt.term_id
            WHERE wptt.taxonomy='ss_category' ", OBJECT);   
            
        $corelatedCategories = sortCategoriesByPriority($categories);
        
        foreach ($corelatedCategories as $corelatedCategory) {    
            if (is_array($corelatedCategory->ss_aff_categories) && in_array($this->currentCategory->term_id, $corelatedCategory->ss_aff_categories)) {                            
                $terms[] = $corelatedCategory->term_id;        
            }            
        }
        
        if (! isset($terms) ) {
            return false;
        }

        $args = array(
            'post_type' => array('advertisers_cats'),
            'tax_query' => array(
                array(
                    'taxonomy'  =>  'ss_category',
                    'field' => 'id',
                    'terms' =>  $terms
                )
            )
        );
        
        $advertiserCategories = get_posts($args);
             
        if ( ! empty($corelatedCategories)) {            
            $this->advertiserCategories = $advertiserCategories;                        
            return true;
        } else {
            $this->advertiserCategories = false;            
        }
        
        return false;
        
    }
    
    private function collectDataForDisplay()
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
    
    public function getCarousel()
    {
        return new RelatedCarousel\Carousel($this->dataForDisplay, $this->currentCategory);
    }
    
    
}