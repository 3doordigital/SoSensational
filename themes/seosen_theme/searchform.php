<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
	<div class="input-group">	
    <input type="search" class="search-field input form-control" placeholder="<?php echo esc_attr_x( 'Search …', 'placeholder' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label' ) ?>" />
    <span class="input-group-btn" id="btnSubmit">
        <button type="submit" class="search-submit btn btn-primary"><i class="fa fa-chevron-right"></i></button>
        </span>
    </div>
</form>