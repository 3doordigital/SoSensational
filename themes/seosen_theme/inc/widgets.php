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
                	<form id="form" class="newsletter_signup" action="https://umc.usearch.co.il/index.php" method="post">
                    	<?php echo $args['before_title']; ?><?php echo $instance['title']; ?><?php echo $args['after_title']; ?>
                        <span class="fieldRowContainer">
                            <span class="fieldRow">
                        <div class="input-group">
                          <?php
                                if(isset($_GET['signedup'])) {
                            ?>
                            <input type="text" class="form-control " value="Thank you for signing up!" disabled>
                          <span class="input-group-btn" id="btnSubmit">
                            <button class="btn btn-primary success" type="submit"><i class="fa fa-check"></i></button>
                          </span>
                            <?php } else { ?>
                            <input type="text" class="input form-control" placeholder="<?php echo $instance['placeholder']; ?>" data-error-container="#alertBox" value="" name="fieldid[em003]" data-required="true" data-notblank="true" data-error-message="Email Address - שדה חובה" data-maxlength="120" data-type="email">
                          <span class="input-group-btn" id="btnSubmit">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-chevron-right"></i></button>
                          </span>
                            <?php } ?>
                        </div><!-- /input-group -->
                        </span>
                        </span>
                        <input type="hidden" name="clid" value="526" />
                        <input type="hidden" name="lID" value="143" />

                        <input type="hidden" name="object" value="mastercampaingleadspost" />
                        <input type="hidden" name="reqSource" value="" />
                        <input type="hidden" name="backurl" id="backurl" value="" />
                        <input type="hidden" name="params" id="params" value="" />
                        <input type="hidden" name="params_ga" id="params_ga" value="" />
                        <input type="hidden" name="jsOn" id="jsOn" value="false" />
                        <!--################### END DON NOT CHANGE ##############################################################-->

                        <!-- ANY STRING TO BE RECORDED -->
                        <input type="hidden" name="extra_1" id="" value="" />
                        <!-- FULL URL FOR REDIRECTING THE USER AFTER A SUCCESS SUBMIT -->
                        <input type="hidden" name="redirect_success" id="redirect_success" value="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>?signedup" />
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
	});