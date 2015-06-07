<?php
/**
 * A class that renders a form for saving SEO title and description on brands and boutiques
 * 
 * The class is included by SeodMeta.php that lice in /classes/
 * 
 * @author Lukasz Tarasiewicz <lukasz.tarasiewicz@polcode.net>
 * @data May 2015
 */

class SeoForm
{
    private $currentSeoData;
    
    /**
     * @param array $currentSeoData SEO data saved in the database
     */
    public function __construct($currentSeoData) 
    {
        $this->currentSeoData = $currentSeoData;
    }
    
    public function renderForm()
    {            
        ?>            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="seo-title-field">SEO Title</label>
                        </th>
                        <td>
                            <input class="large-text ui-autocomplete-input" type="text" id="seo-title-field" name="seo-title" value="<?php echo isset($this->currentSeoData['seo-title']) ? $this->currentSeoData['seo-title'] : '' ?>"/>
                        </td>                            
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="seo-description-field">SEO Description</label>
                        </th>
                        <td>
                            <input class="large-text ui-autocomplete-input" type="text" id="seo-description-field" name="seo-description" value="<?php echo isset($this->currentSeoData['seo-description']) ? $this->currentSeoData['seo-description'] : '' ?>"/>
                        </td>                            
                    </tr>      
                    <tr>
                        <th scope="row">
                            <label for="seo-canonical-field">SEO Canonical Link</label>
                        </th>
                        <td>
                            <input class="large-text ui-autocomplete-input" type="text" id="seo-canonical-field" name="seo-canonical" value="<?php echo isset($this->currentSeoData['seo-canonical']) ? $this->currentSeoData['seo-canonical'] : '' ?>"/>
                        </td>                            
                    </tr>                        
                </tbody>
            </table>
        <?php
    }
}