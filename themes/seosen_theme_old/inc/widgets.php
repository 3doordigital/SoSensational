<?php
	class tim_buynow_widget extends WP_Widget {
	
		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'tim_product', // Base ID
				__('Buy Now Widget', 'text_domain'), // Name
				array( 'description' => __( 'Shows the buy now widget in the sidebar', 'text_domain' ), ) // Args
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
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			?>

<div class="row">
  <div class="col-xs-9"> <img class="buynow_img" src="<?php echo $instance['img_url']; ?>"  /> </div>
  <div class="col-xs-15 buynow_text">
    <p><?php echo  $instance['text']; ?></p>
    <p><a href="/buy-now/" class="btn btn-primary">Buy Now</a></p>
  </div>
</div>
<?php
			echo $args['after_widget'];
		}
	
		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Try Lipogen PS Plus Today!', 'num' => 3 ) );
            $title = $instance['title'];
            $text = $instance['text'];
?>
<p>
  <label for="<?php echo $this->get_field_id('title'); ?>">Title:
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('text'); ?>">Text:
    <textarea rows="5" class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo attribute_escape($text); ?></textarea>
  </label>
</p>
<p><img class="tim_buy_now_img" height="100" width="100" src="<?php echo $instance['img_url']; ?>" style="border: solid 3px #f7f7f7; max-height: 100px; width: auto;" /></p>
<p>
  <label for="<?php echo $this->get_field_id('img_url'); ?>">Image URL:
    <input class="widefat tim_buy_now_img_url" type="text" name="<?php echo $this->get_field_name('img_url'); ?>" id="<?php echo $this->get_field_id('img_url'); ?>" value="<?php echo $instance['img_url']; ?>" />
  </label>
</p>
<p><a href="#" class="button tim_image_insert insert-media add_media" title="Add Media">Add Media</a></p>
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
            $instance['text'] = $new_instance['text'];
			$instance['img_url'] = $new_instance['img_url'];
            return $instance;
		}
	}
	class tim_testimonial_widget extends WP_Widget {
	
		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'tim_testi', // Base ID
				__('Show Testimonial in Sidebar', 'text_domain'), // Name
				array( 'description' => __( 'Shows a testimonial in the sidebar', 'text_domain' ), ) // Args
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
			echo '<h2>Success Stories</h2>';
			echo '<div class="row"><div class="col-md-24">';
			$my_query = new WP_Query( 'post_type=testimonials&posts_per_page=1&orderby=rand' ); 
			while ( $my_query->have_posts() ) : $my_query->the_post();
                    	echo '<a href="#" data-toggle="modal" data-target="#videolink">'.get_the_post_thumbnail( $post->ID, 'full', array('class' => 'img-responsive') ).'</a>';
						?>
<div class="modal fade" id="videolink" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
      <div class="modal-body">
        <div class="embed-responsive embed-responsive-16by9">
          <?php the_field('testi_youtube'); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
					endwhile;
					echo '</div></div>';
					wp_reset_postdata();
			echo $args['after_widget'];
		}
	
		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			echo '<p>This widget has no options.</p>';
		}
	
		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			// processes widget options to be saved
		}
	}
	
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
<form class="newsletter_signup">
  <h2><?php echo $instance['title']; ?></h2>
  <div class="input-group">
    <input type="text" class="form-control" name="news_email" placeholder="<?php echo $instance['placeholder']; ?>">
    <span class="input-group-btn">
    <button class="btn btn-primary" type="submit"><i class="fa fa-chevron-right"></i></button>
    </span> </div>
  <!-- /input-group -->
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
<p>
  <label for="<?php echo $this->get_field_id('title'); ?>">Title:
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('text'); ?>">Placeholder:
    <input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name('placeholder'); ?>" type="text" value="<?php echo attribute_escape($placeholder); ?>" />
  </label>
</p>
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
	
	class tim_address_widget extends WP_Widget {
	
		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'tim_address', // Base ID
				__('Rich Address Widget', 'text_domain'), // Name
				array( 'description' => __( 'Shows the address in the sidebar using rich snippets', 'text_domain' ), ) // Args
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
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			?>
<div itemscope itemtype="http://schema.org/LocalBusiness" class="addressWidget"> <span itemprop="name"><?php echo $instance['company']; ?></span><br/>
  <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"> <span itemprop="streetAddress"><?php echo $instance['street']; ?></span><br/>
    <span itemprop="addressLocality"><?php echo $instance['locality']; ?></span><br/>
    <span itemprop="postalCode"><?php echo $instance['postcode']; ?></span><br/>
  </div>
</div>
<div class="embed-responsive embed-responsive-4by3"> <?php echo $instance['gmap']; ?> </div>
<?php
			echo $args['after_widget'];
		}
	
		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Address' ) );
            $title = $instance['title'];
            $company = $instance['company'];
			$street = $instance['street'];
			$locality = $instance['locality'];
			$postcode = $instance['postcode'];
			$gmap = $instance['gmap'];
			
?>
<p>
  <label for="<?php echo $this->get_field_id('title'); ?>">Title:
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
  </label>
</p>
<hr/>
<p>
  <label for="<?php echo $this->get_field_id('company'); ?>">Company Name:
    <input class="widefat" id="<?php echo $this->get_field_id('company'); ?>" name="<?php echo $this->get_field_name('company'); ?>" type="text" value="<?php echo attribute_escape($company); ?>" />
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('street'); ?>">Street Address:
    <input class="widefat" id="<?php echo $this->get_field_id('street'); ?>" name="<?php echo $this->get_field_name('street'); ?>" type="text" value="<?php echo attribute_escape($street); ?>" />
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('locality'); ?>">Locality:
    <input class="widefat" id="<?php echo $this->get_field_id('locality'); ?>" name="<?php echo $this->get_field_name('locality'); ?>" type="text" value="<?php echo attribute_escape($locality); ?>" />
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('postcode'); ?>">Post Code:
    <input class="widefat" id="<?php echo $this->get_field_id('postcode'); ?>" name="<?php echo $this->get_field_name('postcode'); ?>" type="text" value="<?php echo attribute_escape($postcode); ?>" />
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('gmap'); ?>">Google Map:
    <textarea rows="6" class="widefat" id="<?php echo $this->get_field_id('gmap'); ?>" name="<?php echo $this->get_field_name('gmap'); ?>"><?php echo attribute_escape($gmap); ?></textarea>
  </label>
</p>
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
            $instance['company'] = $new_instance['company'];
			$instance['street'] = $new_instance['street'];
			$instance['locality'] = $new_instance['locality'];
			$instance['postcode'] = $new_instance['postcode'];
			$instance['gmap'] = $new_instance['gmap'];
            return $instance;
		}
	}
	add_action( 'widgets_init', function(){
		 register_widget( 'tim_buynow_widget' );
		 register_widget( 'tim_testimonial_widget' );
		 register_widget( 'tim_newsletter_widget' );
		 register_widget( 'tim_address_widget' );
         //register_widget( 'tim_recent_posts' );
	});