<?php

class Formatter
{
    public function truncateDescription($description, $slug)
    {
        if (strlen($description) > 180) {
            // truncate string
            $shortDescription = substr($description, 0, 180);
            // make sure the string ends in a word
            $description = substr($shortDescription, 0, strrpos($shortDescription, ' '));     
        }
            $description = trim($description);
            $description = $description. ".. <a href='" . get_site_url() . '/brands-and-boutiques/' . $slug . "'>Read more</a>";        
            
            
            return $description;        
    }
}