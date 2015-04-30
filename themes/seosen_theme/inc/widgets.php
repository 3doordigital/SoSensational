<?php
	class tim_newsletter_widget extends WP_Widget {
	
		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'tim_news', // Base ID
				__('Newsletter Signup', 'text_domain'), // Name
				array( 'description' => __( 'Shows the newsletter sign up widget in the sidebar', 'text_domain' ), ) // Args
			);
		}
	
		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			echo $args['before_widget'];
			?>
                	<form id="form" class="newsletter_signup newsform_send" action="" method="post">
                    	<?php echo $args['before_title']; ?><?php echo $instance['title']; ?><?php echo $args['after_title']; ?>
                        <span class="fieldRowContainer">
                            <span class="fieldRow">
                        <div class="input-group">
                         
                            <input type="email" class="input form-control" placeholder="<?php echo $instance['placeholder']; ?>"  name="email">
                          <span class="input-group-btn" id="btnSubmit">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-chevron-right"></i></button>
                          </span>
                           
                        </div><!-- /input-group -->
                        
                    </form>
            <?php
			echo $args['after_widget'];
		}
	
		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'RECIEVE YOUR FREE MEMORY IMPROVEMENT GUIDE:' ) );
            $title = $instance['title'];
            $placeholder = $instance['placeholder'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('text'); ?>">Placeholder: <input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name('placeholder'); ?>" type="text" value="<?php echo esc_attr($placeholder); ?>" /></label></p>
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            $instance['placeholder'] = $new_instance['placeholder'];
            return $instance;
		}
	}
	
	class tim_TLC_widget extends WP_Widget {
	
		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'tim_tlc', // Base ID
				__('Top Level Categories', 'text_domain'), // Name
				array( 'description' => __( 'Shows the top level categories widget in the sidebar', 'text_domain' ), ) // Args
			);
		}
	
		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			echo $args['before_widget'];
			?>
            
            <?php echo $args['before_title']; ?><?php echo $instance['title']; ?><?php echo $args['after_title']; ?>
            
            <?php 
				$args = array(
					'orderby'            => 'name',
					'order'              => 'ASC',
					'style'              => 'list',
					'title_li'           => __( '' ),
					'depth'              => 1,
					'taxonomy'           => 'category',
				);
				
				wp_list_categories( $args ) ; 
				
			?>
                       
            <?php echo $args['after_widget'];
		}
	
		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Blog Categories' ) );
            $title = $instance['title'];
            $placeholder = $instance['placeholder'];
?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
		}
	}

class tim_ad_block extends WP_Widget {
	
		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'tim_ad', // Base ID
				__('Advert Block', 'text_domain'), // Name
				array( 'description' => __( 'Display an advert from an Advertiser', 'text_domain' ), ) // Args
			);
		}
	
		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			echo $args['before_widget'];
			echo $instance['advert']; 
			echo $args['after_widget'];
		}
	
		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'advert' => '' ) );
            $advert = $instance['advert'];
?>
            <p><label for="<?php echo $this->get_field_id('advert'); ?>">Ad Code: <textarea rows="10" class="widefat" id="<?php echo $this->get_field_id('advert'); ?>" name="<?php echo $this->get_field_name('advert'); ?>" ><?php echo esc_attr($advert); ?></textarea></label></p>
        <?php
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
            $instance['advert'] = $new_instance['advert'];
            return $instance;
		}
	}

    add_action( 'widgets_init', function(){
		 register_widget( 'tim_newsletter_widget' );
		 register_widget( 'tim_ad_block' );
		 register_widget( 'tim_TLC_block' );
	});
	