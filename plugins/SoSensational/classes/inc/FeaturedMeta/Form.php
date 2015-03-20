<?php
/**
 * A class that renders a checkboxes form on brands and boutiques admin pages
 * 
 * The class is included by FeaturedMeta.php that lice in /classes/
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data March 2015
 */

class Form
{
    public function __construct() 
    {
        $this->renderForm();
    }
    
    private function renderForm()
    {
        echo "<label for='featured-meta'>";
        echo 'Choose categories the advertiser should be featured in';        
        echo '</label>';
        echo '<input type="checkbox" />';
    }
}