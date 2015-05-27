<form role="search" method="get" class="search-form form-inline" action="<?php echo home_url( '/' ); ?>">
	<div class="form-group">	
        <div id="searchform-top">
            <label for="sosen-searchbox">Hi, I'm interested in:</label>
            <input type="search" id="sosen-searchbox" class="search-field input form-control" placeholder="<?php echo esc_attr_x( 'Search …', 'placeholder' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label' ) ?>" />
            <span class="input-group-btn" id="btnSubmit">
                <button type="submit" class="search-submit btn btn-primary"><i class="fa fa-chevron-right"></i></button>
            </span>
        </div>
        <div id="searchform-bottom">
            <div id="related-searches">
                <p><span id="suggestions-label">related searches: </span><span class="suggestion">red evening dresses</span></p>
            </div>
        </div>
    </div>
</form>
