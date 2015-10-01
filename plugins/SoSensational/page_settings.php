<?php

add_action( 'admin_menu', 'ss_add_admin_menu' );
add_action( 'admin_init', 'ss_settings_init' );


function ss_add_admin_menu(  ) { 

	add_options_page( 'ss_settings', 'SS Directory Settings', 'manage_options', 'ss_settings', 'ss_settings_options_page' );

}


function ss_settings_init(  ) { 

	register_setting( 'pluginPage', 'ss_settings' );

	add_settings_section(
		'ss_pluginPage_section', 
		__( 'So Senstational Setting', 'wordpress' ), 
		'ss_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'ss_products_per_brand', 
		__( 'Products Per Brand', 'wordpress' ), 
		'ss_products_per_brand_render', 
		'pluginPage', 
		'ss_pluginPage_section' 
	);

	add_settings_field( 
		'ss_products_per_boutique', 
		__( 'Products Per Boutique', 'wordpress' ), 
		'ss_products_per_boutique_render', 
		'pluginPage', 
		'ss_pluginPage_section' 
	);

	add_settings_field( 
		'ss_categories_per_brand', 
		__( 'Categories Per Brand', 'wordpress' ), 
		'ss_categories_per_brand_render', 
		'pluginPage', 
		'ss_pluginPage_section' 
	);

	add_settings_field( 
		'ss_categories_per_boutique', 
		__( 'Categories Per Boutique', 'wordpress' ), 
		'ss_categories_per_boutique_render', 
		'pluginPage', 
		'ss_pluginPage_section' 
	);

	add_settings_field( 
		'ss_checkbox_field_4', 
		__( 'Allow Updates', 'wordpress' ), 
		'ss_checkbox_field_4_render', 
		'pluginPage', 
		'ss_pluginPage_section' 
	);


}


function ss_products_per_brand_render(  ) { 

	$options = get_option( 'ss_settings' );
	?>
	<input type='text' name='ss_settings[ss_products_per_brand]' value='<?php echo $options['ss_products_per_brand']; ?>'>
	<?php

}


function ss_products_per_boutique_render(  ) { 

	$options = get_option( 'ss_settings' );
	?>
	<input type='text' name='ss_settings[ss_products_per_boutique]' value='<?php echo $options['ss_products_per_boutique']; ?>'>
	<?php

}


function ss_categories_per_brand_render(  ) { 

	$options = get_option( 'ss_settings' );
	?>
	<input type='text' name='ss_settings[ss_categories_per_brand]' value='<?php echo $options['ss_categories_per_brand']; ?>'>
	<?php

}


function ss_categories_per_boutique_render(  ) { 

	$options = get_option( 'ss_settings' );
	?>
	<input type='text' name='ss_settings[ss_categories_per_boutique]' value='<?php echo $options['ss_categories_per_boutique']; ?>'>
	<?php

}


function ss_checkbox_field_4_render(  ) { 

	$options = get_option( 'ss_settings' );
	?>
	<input type='checkbox' name='ss_settings[ss_checkbox_field_4]' <?php checked( $options['ss_checkbox_field_4'], 1 ); ?> value='1'>
	<?php

}


function ss_settings_section_callback(  ) { 

	echo __( 'Options for handling brands and boutiques', 'wordpress' );

}


function ss_settings_options_page(  ) { 

	?>
	<form action='options.php' method='post'>
		
		<h2></h2>
		
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
		
	</form>
	<?php

}