<?php

/**
  ReduxFramework Sample Config File
  For full documentation, please visit: https://docs.reduxframework.com
 * */

if (!class_exists('admin_folder_Redux_Framework_config')) {

    class admin_folder_Redux_Framework_config {

        public $args        = array();
        public $sections    = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {

            if (!class_exists('ReduxFramework')) {
                return;
            }

            // This is needed. Bah WordPress bugs.  ;)
            if ( true == Redux_Helpers::isTheme( __FILE__ ) ) {
                $this->initSettings();
            } else {
                add_action('plugins_loaded', array($this, 'initSettings'), 10);
            }

        }

        public function initSettings() {

            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();

            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
            $this->setHelpTabs();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            // If Redux is running as a plugin, this will remove the demo notice and links
            add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
            
            // Function to test the compiler hook and demo CSS output.
            // Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
            //add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 2);
            
            // Change the arguments after they've been declared, but before the panel is created
            //add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
            
            // Change the default value of a field after it's been set, but before it's been useds
            //add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );
            
            // Dynamically add a section. Can be also used to modify sections/fields
            //add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        /**

          This is a test function that will let you see when the compiler hook occurs.
          It only runs if a field	set with compiler=>true is changed.

         * */
        function compiler_action($options, $css) {
            //echo '<h1>The compiler hook has run!';
            //print_r($options); //Option values
            //print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )

            /*
              // Demo of how to use the dynamic CSS and write your own static CSS file
              $filename = dirname(__FILE__) . '/style' . '.css';
              global $wp_filesystem;
              if( empty( $wp_filesystem ) ) {
                require_once( ABSPATH .'/wp-admin/includes/file.php' );
              WP_Filesystem();
              }

              if( $wp_filesystem ) {
                $wp_filesystem->put_contents(
                    $filename,
                    $css,
                    FS_CHMOD_FILE // predefined mode settings for WP files
                );
              }
             */
        }

        /**

          Custom function for filtering the sections array. Good for child themes to override or add to the sections.
          Simply include this function in the child themes functions.php file.

          NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
          so you must use get_template_directory_uri() if you want to use any of the built in icons

         * */
        function dynamic_section($sections) {
            //$sections = array();
            $sections[] = array(
                'title' => __('Section via hook', 'redux-framework-demo'),
                'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo'),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
            );

            return $sections;
        }

        /**

          Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

         * */
        function change_arguments($args) {
            //$args['dev_mode'] = true;

            return $args;
        }

        /**

          Filter hook for filtering the default value of any given field. Very useful in development mode.

         * */
        function change_defaults($defaults) {
            $defaults['str_replace'] = 'Testing filter hook!';

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::instance(), 'plugin_metalinks'), null, 2);

                // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
                remove_action('admin_notices', array(ReduxFrameworkPlugin::instance(), 'admin_notices'));
            }
        }

        public function setSections() {

            /**
              Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
             * */
            // Background Patterns Reader
            $sample_patterns_path   = ReduxFramework::$_dir . '../sample/patterns/';
            $sample_patterns_url    = ReduxFramework::$_url . '../sample/patterns/';
            $sample_patterns        = array();

            if (is_dir($sample_patterns_path)) :

                if ($sample_patterns_dir = opendir($sample_patterns_path)) :
                    $sample_patterns = array();

                    while (( $sample_patterns_file = readdir($sample_patterns_dir) ) !== false) {

                        if (stristr($sample_patterns_file, '.png') !== false || stristr($sample_patterns_file, '.jpg') !== false) {
                            $name = explode('.', $sample_patterns_file);
                            $name = str_replace('.' . end($name), '', $sample_patterns_file);
                            $sample_patterns[]  = array('alt' => $name, 'img' => $sample_patterns_url . $sample_patterns_file);
                        }
                    }
                endif;
            endif;

            ob_start();

            $ct             = wp_get_theme();
            $this->theme    = $ct;
            $item_name      = $this->theme->get('Name');
            $tags           = $this->theme->Tags;
            $screenshot     = $this->theme->get_screenshot();
            $class          = $screenshot ? 'has-screenshot' : '';

            $customize_title = sprintf(__('Customize &#8220;%s&#8221;', 'redux-framework-demo'), $this->theme->display('Name'));
            
            ?>
            <div id="current-theme" class="<?php echo esc_attr($class); ?>">
            <?php if ($screenshot) : ?>
                <?php if (current_user_can('edit_theme_options')) : ?>
                        <a href="<?php echo wp_customize_url(); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr($customize_title); ?>">
                            <img src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview'); ?>" />
                        </a>
                <?php endif; ?>
                    <img class="hide-if-customize" src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview'); ?>" />
                <?php endif; ?>

                <h4><?php echo $this->theme->display('Name'); ?></h4>

                <div>
                    <ul class="theme-info">
                        <li><?php printf(__('By %s', 'redux-framework-demo'), $this->theme->display('Author')); ?></li>
                        <li><?php printf(__('Version %s', 'redux-framework-demo'), $this->theme->display('Version')); ?></li>
                        <li><?php echo '<strong>' . __('Tags', 'redux-framework-demo') . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
                    </ul>
                    <p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
            <?php
            if ($this->theme->parent()) {
                printf(' <p class="howto">' . __('This <a href="%1$s">child theme</a> requires its parent theme, %2$s.') . '</p>', __('http://codex.wordpress.org/Child_Themes', 'redux-framework-demo'), $this->theme->parent()->display('Name'));
            }
            ?>

                </div>
            </div>

            <?php
            $item_info = ob_get_contents();

            ob_end_clean();

            $sampleHTML = '';
            if (file_exists(dirname(__FILE__) . '/info-html.html')) {
                /** @global WP_Filesystem_Direct $wp_filesystem  */
                global $wp_filesystem;
                if (empty($wp_filesystem)) {
                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    WP_Filesystem();
                }
                $sampleHTML = $wp_filesystem->get_contents(dirname(__FILE__) . '/info-html.html');
            }

            // ACTUAL DECLARATION OF SECTIONS
            $this->sections[] = array(
                'title'     => __('Homepage', 'redux-framework-demo'),
                'desc'      => __('', 'redux-framework-demo'),
                'icon'      => 'el-icon-home',
                // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
                'fields'    => array(
					array(
						   'id' => 'home-top-start',
						   'type' => 'section',
						   'title' => __('Homepage Top', 'redux-framework-demo'),
						   'indent' => true 
					   ),
					array(
						'id'       => 'home_image_1',
						'type'     => 'media', 
						'url'      => false,
						'title'    => __('Top Left Upper Image', 'redux-framework-demo'),
					),
					array(
						'id'=>'home_image_1_text',
						'type' => 'text',
						'title' => __('Top Left Upper Text', 'redux-framework-demo'), 
					),
					array(
						'id'=>'home_image_1_link',
						'type' => 'text',
						'title' => __('Top Left Upper Link', 'redux-framework-demo'), 
					),
					array(
						'id'=>'home_image_1_button',
						'type' => 'text',
						'title' => __('Top Left Upper Button', 'redux-framework-demo'), 
					),
					array(
						'id'       => 'home_image_2',
						'type'     => 'media', 
						'url'      => false,
						'title'    => __('Top Left Lower Image', 'redux-framework-demo'),
					),
					array(
						'id'=>'home_image_2_text',
						'type' => 'text',
						'title' => __('Top Left lower Text', 'redux-framework-demo'), 
					),
					array(
						'id'=>'home_image_2_link',
						'type' => 'text',
						'title' => __('Top Left Lower Link', 'redux-framework-demo'), 
						 
					),
					array(
						'id'=>'home_image_2_button',
						'type' => 'text',
						'title' => __('Top Left Upper Button', 'redux-framework-demo'),
					),
					array(
						'id'          => 'home_slider',
						'type'        => 'slides',
						'title'       => __('Homepage Slider', 'redux-framework-demo'),
						'desc'        => __('Usage: Title - main text, Descripton - Not in use, Link - where the browse collection button links to.', 'redux-framework-demo'),
						'placeholder' => array(
							'title'           => __('Slider text', 'redux-framework-demo'),
							'description'     => __('Button Text', 'redux-framework-demo'),
							'url'             => __('URL to link to', 'redux-framework-demo'),
						)
					),
					array(
						'id'          => 'hp_button_one',
						'type'        => 'text',
						'title'       => 'Homepage Button Top Picture',
                        'fields'      => array(
                            array (
                                'id'    =>  'hp_button_1',
                                'type'  =>  'text',
                                'title' =>  'Homepage Slider Button Text',
                                'default'   =>  'Browse Collection',
                            ),                                      
                        )
					),    
					array(
						'id'          => 'hp_button_two',
						'type'        => 'text',
						'title'       => 'Homepage Button Bottom Picture',
                        'fields'      => array(
                            array (
                                'id'    =>  'hp_button_2',
                                'type'  =>  'text',
                                'title' =>  'Homepage Button Text Two',
                                'default'   =>  'Browse Collection',
                            ),                                      
                        )
					),                       
					array(
						'id'     => 'home-top-end',
						'type'   => 'section',
						'indent' => false,
					),
					array(
						   'id' => 'home-cat-start',
						   'type' => 'section',
						   'title' => __('Category Boxes', 'redux-framework-demo'),
						   'indent' => true 
					   ),
					array(
						'id'          => 'home_cats',
						'type'        => 'slides',
						'title'       => __('Category links', 'redux-framework-demo'),
						'desc'        => __('Usage: Title - main text, Descripton - Not in use, Link - where the box links to.', 'redux-framework-demo'),
						'placeholder' => array(
							'title'           => __('Slider text', 'redux-framework-demo'),
							'description'     => __('Not in use!', 'redux-framework-demo'),
							'url'             => __('URL to link to', 'redux-framework-demo'),
						)
					),
					array(
						'id'     => 'home-cat-end',
						'type'   => 'section',
						'indent' => false,
					),
					array(
						   'id' => 'feat-brands-start',
						   'type' => 'section',
						   'title' => __('Featured Brands', 'redux-framework-demo'),
						   'indent' => true 
					   ),
					array(
						'id'          => 'feat_brands',
						'type'        => 'slides',
						'title'       => __('Featured Brands', 'redux-framework-demo'),
						'desc'        => __('Usage: Title - main text, Descripton - Not in use, Link - where the box links to.', 'redux-framework-demo'),
						'placeholder' => array(
							'title'           => __('Slider text', 'redux-framework-demo'),
							'description'     => __('Not in use!', 'redux-framework-demo'),
							'url'             => __('URL to link to', 'redux-framework-demo'),
						)
					),
					array(
						'id'     => 'feat-brands-end',
						'type'   => 'section',
						'indent' => false,
					),
				),
            );
			
            $this->sections[] = array(
                'type' => 'divide',
            );
			$this->sections[] = array(
                'icon'      => 'el-icon-cogs',
                'title'     => __('General Settings', 'redux-framework-demo'),
                'fields'    => array(
					array(
						'id'       => 'tel_number',
						'type'     => 'text',
						'title'    => __('Telephone Number', 'redux-framework-demo'),
					),
					array(
					   'id' => 'social-start',
					   'type' => 'section',
					   'title' => __('Social Media Profiles', 'redux-framework-demo'),
					   'subtitle'  => __('Add the URLs to your social profiles here.', 'redux-framework-demo'),
					   'indent' => true 
				   ),
					array(
						'id'       => 'social_youtube',
						'type'     => 'text',
						'title'    => __('YouTube Profile URL', 'redux-framework-demo'),
						'default'  => 'http://'
					),
					array(
						'id'       => 'social_twitter',
						'type'     => 'text',
						'title'    => __('Twitter Profile URL', 'redux-framework-demo'),
						'default'  => 'http://'
					),
					array(
						'id'       => 'social_facebook',
						'type'     => 'text',
						'title'    => __('Facebook Profile URL', 'redux-framework-demo'),
						'default'  => 'http://'
					),
					array(
						'id'       => 'social_pinterest',
						'type'     => 'text',
						'title'    => __('Pinterest Profile URL', 'redux-framework-demo'),
						'default'  => 'http://'
					),
					array(
						'id'       => 'social_stumbleupon',
						'type'     => 'text',
						'title'    => __('Stumble Upon Profile URL', 'redux-framework-demo'),
						'default'  => 'http://'
					),
					array(
						'id'       => 'social_instagram',
						'type'     => 'text',
						'title'    => __('Instagram Profile URL', 'redux-framework-demo'),
						'default'  => 'http://'
					),
					array(
						'id'     => 'social-end',
						'type'   => 'section',
						'indent' => false,
					),
					array(
						'id'               => 'footer_text',
						'type'             => 'editor',
						'title'            => __('Footer Text', 'redux-framework-demo'), 
						'args'   => array(
							'teeny'            => true,
							'textarea_rows'    => 10,
							'media_buttons'    => false
						)
					)
				)
			);
            $this->sections[] = array(
                'icon'      => 'el-icon-cogs',
                'title'     => __('Advanced Settings', 'redux-framework-demo'),
                'fields'    => array(
					array(
						'id'       => 'custom_html',
						'type'     => 'ace_editor',
						'title'    => __('Custom HTML Header Code', 'redux-framework-demo'),
						'subtitle' => __('Paste your HTML here.', 'redux-framework-demo'),
						'description' => __('This can be used to add meta tags for social networks etc'),
						'mode'     => 'html',
						'theme'    => 'chrome',
						'default'  => ""
					),
					array(
						'id'       => 'custom_css',
						'type'     => 'ace_editor',
						'title'    => __('Custom CSS Code', 'redux-framework-demo'),
						'subtitle' => __('Paste your CSS code here.', 'redux-framework-demo'),
						'mode'     => 'css',
						'theme'    => 'chrome',
						'default'  => ""
					),
					array(
						'id'       => 'custom_js',
						'type'     => 'ace_editor',
						'title'    => __('Custom Javascript Code', 'redux-framework-demo'),
						'subtitle' => __('Paste your Javascript code here.', 'redux-framework-demo'),
						'mode'     => 'javascript',
						'theme'    => 'chrome',
						'default'  => ""
					),
				)
            );

            /**
             *  Note here I used a 'heading' in the sections array construct
             *  This allows you to use a different title on your options page
             * instead of reusing the 'title' value.  This can be done on any
             * section - kp
             */
            

            $theme_info  = '<div class="redux-framework-section-desc">';
            $theme_info .= '<p class="redux-framework-theme-data description theme-uri">' . __('<strong>Theme URL:</strong> ', 'redux-framework-demo') . '<a href="' . $this->theme->get('ThemeURI') . '" target="_blank">' . $this->theme->get('ThemeURI') . '</a></p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-author">' . __('<strong>Author:</strong> ', 'redux-framework-demo') . $this->theme->get('Author') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-version">' . __('<strong>Version:</strong> ', 'redux-framework-demo') . $this->theme->get('Version') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-description">' . $this->theme->get('Description') . '</p>';
            $tabs = $this->theme->get('Tags');
            if (!empty($tabs)) {
                $theme_info .= '<p class="redux-framework-theme-data description theme-tags">' . __('<strong>Tags:</strong> ', 'redux-framework-demo') . implode(', ', $tabs) . '</p>';
            }
            $theme_info .= '</div>';

            if (file_exists(dirname(__FILE__) . '/../README.md')) {
                $this->sections['theme_docs'] = array(
                    'icon'      => 'el-icon-list-alt',
                    'title'     => __('Documentation', 'redux-framework-demo'),
                    'fields'    => array(
                        array(
                            'id'        => '17',
                            'type'      => 'raw',
                            'markdown'  => true,
                            'content'   => file_get_contents(dirname(__FILE__) . '/../README.md')
                        ),
                    ),
                );
            }
            
            // You can append a new section at any time.
                                
                    
            $this->sections[] = array(
                'type' => 'divide',
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-info-sign',
                'title'     => __('Theme Information', 'redux-framework-demo'),
                'fields'    => array(
                    array(
                        'id'        => 'opt-raw-info',
                        'type'      => 'raw',
                        'content'   => $item_info,
                    )
                ),
            );

            if (file_exists(trailingslashit(dirname(__FILE__)) . 'README.html')) {
                $tabs['docs'] = array(
                    'icon'      => 'el-icon-book',
                    'title'     => __('Documentation', 'redux-framework-demo'),
                    'content'   => nl2br(file_get_contents(trailingslashit(dirname(__FILE__)) . 'README.html'))
                );
            }
        }

        public function setHelpTabs() {

            // Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-1',
                'title'     => __('Theme Information 1', 'redux-framework-demo'),
                'content'   => __('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo')
            );

            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-2',
                'title'     => __('Theme Information 2', 'redux-framework-demo'),
                'content'   => __('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo')
            );

            // Set the help sidebar
            $this->args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', 'redux-framework-demo');
        }

        /**

          All the possible arguments for Redux.
          For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

         * */
        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.

            $this->args = array(
                'opt_name' => 'seosen_options',
                'page_slug' => '_seosen_options_options',
                'page_title' => 'SeoSensational Theme Options',
                'admin_bar' => true,
                'menu_type' => 'menu',
                'menu_title' => 'Theme Options',
                'allow_sub_menu' => true,
                'page_parent' => 'options-general.php',
                'page_parent_post_type' => 'your_post_type',
                'default_mark' => '*',
                'hints' => 
                array(
                  'icon' => 'el-icon-cogs',
                  'icon_position' => 'left',
                  'icon_size' => 'large',
                  'tip_style' => 
                  array(
                    'color' => 'light',
                    'style' => 'bootstrap',
                  ),
                  'tip_position' => 
                  array(
                    'my' => 'top left',
                    'at' => 'bottom right',
                  ),
                  'tip_effect' => 
                  array(
                    'show' => 
                    array(
                      'duration' => '500',
                      'event' => 'mouseover',
                    ),
                    'hide' => 
                    array(
                      'duration' => '500',
                      'event' => 'mouseleave unfocus',
                    ),
                  ),
                ),
                'output' => true,
                'output_tag' => true,
                'page_icon' => 'icon-themes',
                'page_permissions' => 'manage_options',
                'save_defaults' => true,
                'show_import_export' => false,
                'database' => 'options',
                'transient_time' => '3600',
                'network_sites' => true,
              );

            // SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
            $this->args['share_icons'][] = array(
                'url'   => 'https://github.com/ReduxFramework/ReduxFramework',
                'title' => 'Visit us on GitHub',
                'icon'  => 'el-icon-github'
                //'img'   => '', // You can use icon OR img. IMG needs to be a full URL.
            );
            $this->args['share_icons'][] = array(
                'url'   => 'https://www.facebook.com/pages/Redux-Framework/243141545850368',
                'title' => 'Like us on Facebook',
                'icon'  => 'el-icon-facebook'
            );
            $this->args['share_icons'][] = array(
                'url'   => 'http://twitter.com/reduxframework',
                'title' => 'Follow us on Twitter',
                'icon'  => 'el-icon-twitter'
            );
            $this->args['share_icons'][] = array(
                'url'   => 'http://www.linkedin.com/company/redux-framework',
                'title' => 'Find us on LinkedIn',
                'icon'  => 'el-icon-linkedin'
            );

        }

    }
    
    global $reduxConfig;
    $reduxConfig = new admin_folder_Redux_Framework_config();
}

/**
  Custom function for the callback referenced above
 */
if (!function_exists('admin_folder_my_custom_field')):
    function admin_folder_my_custom_field($field, $value) {
        print_r($field);
        echo '<br/>';
        print_r($value);
    }
endif;

/**
  Custom function for the callback validation referenced above
 * */
if (!function_exists('admin_folder_validate_callback_function')):
    function admin_folder_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value = 'just testing';

        /*
          do your validation

          if(something) {
            $value = $value;
          } elseif(something else) {
            $error = true;
            $value = $existing_value;
            $field['msg'] = 'your custom error message';
          }
         */

        $return['value'] = $value;
        if ($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }
endif;
