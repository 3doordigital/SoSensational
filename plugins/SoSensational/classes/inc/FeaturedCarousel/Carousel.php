<?php
namespace FeaturedCarousel;

class Carousel
{
    public function __construct($dataForDisplay)
    {
        $this->displayCarousel($dataForDisplay);
    }
    
    private function displayCarousel($dataForDisplay)
    {
        echo '<hr>';
        echo '<h1>Featured Brands</h1>';
        echo '<div class="flexslider">';
            echo '<ul class="slides">';         
                foreach($dataForDisplay as $singleBox) {
                    ?>
                    <li>
                        <div class='related-item ss_border'>
                            <a href='<?php echo get_site_url() . '/brands-and-boutiques/' . $singleBox['post_name']; ?>'>
                                <img src='<?php echo $singleBox['image'];  ?>' />
                                <div class='title-bar'><h2><?php echo $singleBox['post_title']; ?></h2></div>                                
                            </a>  
                            <div class='related-description'>
                                <p><?php echo $this->truncateDescription($singleBox); ?></p>
                            </div>
                            <a href="<?php echo $singleBox['advertiser_redirection_link']; ?>" class='button_ss large_ss'>Visit Website</a>                            
                        </div>     
                    </li>                     
                    <?php
                }
            echo '</div>'; // .slides
        echo '</div>';  // .flexslider                
    }  
    
    private function truncateDescription($singleBox)
    {
        if (strlen($singleBox['description']) > 180) {
            // truncate string
            $shortDescription = substr($singleBox['description'], 0, 180);
            // make sure the string ends in a word
            $singleBox['description'] = substr($shortDescription, 0, strrpos($shortDescription, ' '));     
            $singleBox['description'] = $singleBox['description'] . '...';
        }
            $singleBox['description'] = $singleBox['description']. "<br/><a href='" . get_site_url() . '/brands-and-boutiques/' . $singleBox['post_name'] . "'>Read More</a>";        
            
            return $singleBox['description'];
    }    
}