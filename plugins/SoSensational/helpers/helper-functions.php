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