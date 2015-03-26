<?php

/**
 * Helper function for the SoSensational plugin
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
 */


/**
 * A function that outputs custom system messages based on the URL query var
 * 
 * @return string
 */
function displaySystemNotice()
{
    $actionStatus = isset($_GET['adminmsg']) ? $_GET['adminmsg'] : '';
    
    if ( empty($actionStatus) ) {
        return;
    }
    
    if ( $actionStatus === 's' ) {
        $displayMessage =  'You have successfully saved a product.';
        $alertClass = 'success';
    } elseif ( $actionStatus === 'f' )         {
        $displayMessage =  'Something went wrong when saving a product. Please try again.';
        $alertClass = 'warning';
        
    } elseif ($actionStatus === 'd') {
        $displayMessage =  'A product has been deleted.';
        $alertClass = 'success';        
    }
        
    return "<div class='alert alert-$alertClass' role='alert'>$displayMessage</div>";
    
}

function checkIfSelected($currentCategoryId, $selectedCategories)
{
    if (is_array($selectedCategories)) {
        if (in_array($currentCategoryId, $selectedCategories)) {
            return true;
        }           
    }
    return false;
}

/**
 * Make the default in_array() function recursive
 * 
 * @author jwueller
 * @source http://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array
 * 
 * @param mixed $needle
 * @param array $haystack
 * @param bool $strict
 * @return boolean
 */
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function truncateDescription($description, $slug)
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