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
        
        if ($this->collectAdvertiserCategories()) {
            $this->collectDataForDisplay();
        }

    }
    
    private function collectAdvertiserCategories()
    {      
        global $wpdb;

        /**
         * Retrieve all terms from 'ss_category' taxonomy
         */
        $categories = $wpdb->get_results( "SELECT * FROM {$wpdb->term_taxonomy} wptt 
            LEFT JOIN {$wpdb->terms} as wpt
            ON wpt.term_id=wptt.term_id
            WHERE wptt.taxonomy='ss_category' ", OBJECT);   

            
        $corelatedCategories = $this->attachShopCategoriesToSSCategories($categories);
        $terms = $this-> retrieveSSTermsCorelatedToCurrentTerm($corelatedCategories);               
               
        if ($terms === false) {
            return false;
        }
        
        $advertiserCategoriesToDisplay = $this->retrieveAdvertiserCategoriesToDisplay($terms);
                 
        $this->advertiserCategories = $advertiserCategoriesToDisplay;  
        
        if ($advertiserCategoriesToDisplay) {                    
            return true;
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
    
    private function attachShopCategoriesToSSCategories($categories)
    {
        foreach($categories as $singleCategory) {    
            $singleCategoriesMeta = get_option( "taxonomy_$singleCategory->term_id" );
            $ssAffCategories = isset($singleCategoriesMeta['ss_aff_categories']) ? $singleCategoriesMeta['ss_aff_categories'] : false;
            $singleCategory->ss_aff_categories = $ssAffCategories;
            $corelatedCategories[] = $singleCategory;

            return $corelatedCategories;
        }        
    }
    
    private function retrieveSSTermsCorelatedToCurrentTerm($corelatedCategories)
    {
        foreach ($corelatedCategories as $corelatedCategory) {    
            if (is_array($corelatedCategory->ss_aff_categories) && 
                in_array($this->currentCategory->term_id, $corelatedCategory->ss_aff_categories)) {                            
                $terms[] = $corelatedCategory->term_id;        
            }            
        }        
        if (isset($terms)) {
            return $terms;
        }
        return false;
    }
    
    private function retrieveAdvertiserCategoriesToDisplay($terms)
    {
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
        
        $adverTisersToDisplay = get_posts($args);        
        
        return $adverTisersToDisplay;
    }
    
}