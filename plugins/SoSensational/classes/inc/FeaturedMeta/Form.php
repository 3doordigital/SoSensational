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
    private $categoriesToRender;
    private $selectedCategories;
    
    /**
     * @param array $categoriesToRender Main categories from 'ss_category' taxonomy
     * @param array $selectedCategories Categories that were previously saved in the database
     */
    public function __construct($categoriesToRender, $selectedCategories) 
    {
        $this->categoriesToRender = $categoriesToRender;
        $this->selectedCategories = $selectedCategories;
    }
    
    public function renderForm()
    {
        foreach ($this->categoriesToRender as $categoryToRender) {
            
            $checked= checkIfSelected($categoryToRender->term_id, $this->selectedCategories) ? 'checked' : '';
        ?>            
            <div class="input-group">
                <span class="input-group-addon">
                    <input type="checkbox" name="categoriesFeatured[]" value="<?php echo $categoryToRender->term_id ?>" <?php echo $checked; ?>/>
                </span>
                <input type="text" value="<?php echo $categoryToRender->name ?>" disabled/>
            </div>
        <?php
        }
    }
}