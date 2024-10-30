<?php

/**
 * The widget-specific functionality of the plugin.
 *
 * @link       https://instawell.com
 * @since      1.0.0
 *
 * @package    Instawell_widget
 * @subpackage Instawell_widget/includes
 */

/**
 * The widget-specific functionality of the plugin.
 *
 * Contains the widget that's extended from the base widget object
 *
 * @package    Instawell_widget
 * @subpackage Instawell_widget/admin
 * @author     Instawell <support@instawell.com>
 */
class Instawell_Widget_Widget extends WP_Widget {

	private $text_domain; 
	
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'instawell_widget_widget',
			'description' => 'Adds Instawell to your sidebar inside a handy widget',
		);
		parent::__construct( 'instawell_widget_widget', 'Instawell Widget', $widget_ops );
		
		$this->iw_request	= new Instawell_request();
		$this->iw_domain	= $this->iw_request->host;;
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters('widget_title', $instance['title']);

        echo $args['before_widget'];
        if (!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
            
        $height		= empty( $instance['height'] ) ? 500 : $instance['height'];
        $width		= empty( $instance['width'] ) ? 280 : $instance['width'];   
		
		
		$iw_request	= new Instawell_request();
		$campaign_code	= $iw_request->get_campaign_code_for_site();
		$campaign_id	= $iw_request->get_campaign_id_for_site();
		
		$hostname_parts	= parse_url(get_home_url());
		$hostname		= $hostname_parts['host'];
		if ($campaign_code) {
			$embed_url		= $this->iw_domain . '/campaign/' . $campaign_code . '?widget_offsite=1&widget=wordpress&host=' . $hostname;
		}
		
		echo "
			<style>
				.instawell-wordpress-widget-iframe-outerwrap {
					border: 1px solid #ddd;
				}
				
				.instawell-wordpress-widget-iframe-wrap {
					width: 100%!important;
					height: {$height}px!important;
					margin: 0!important;
					padding: 0!important;
				}
				@media only screen and (-webkit-min-device-pixel-ratio: 1.5),
				only screen and (-o-min-device-pixel-ratio: 3/2),
				only screen and (min--moz-device-pixel-ratio: 1.5),
				only screen and (min-device-pixel-ratio: 1.5) {

					.instawell-wordpress-widget-iframe-wrap {
						overflow: auto;
						-webkit-overflow-scrolling:touch;
					}
				}

				.instawell-wordpress-widget-iframe-wrap iframe {
				    height: {$height}px!important;
					border: none;
					width: 100%;
					outline: none;
				}
			</style>
			<div class='instawell-wordpress-widget-iframe-outerwrap'>
				<div class='instawell-wordpress-widget-iframe-wrap'>
					<iframe src='{$embed_url}'></iframe>
				</div>
			</div>
		";
		
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		
		$title		= !empty($instance['title']) ? $instance['title'] : _('Instawell');
        $height		= !empty($instance['height'] ) ? $instance['height'] : 500;
        $width		= !empty($instance['width']) ? $instance['width'] : 280;
		$embed_code	= !empty($instance['embed_code']) ? $instance['embed_code'] : '';
		
		$iw_request	= new Instawell_request();
		$campaign_code	= $iw_request->get_campaign_code_for_site();
		$campaign_id	= $iw_request->get_campaign_id_for_site();
		
		
		$hostname_parts	= parse_url(get_home_url());
		$hostname		= $hostname_parts['host'];
    ?>
        <p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        
        <p>
			<label for="<?php echo $this->get_field_name( 'height' ); ?>"><?php _e( 'Widget Height:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>" />
        </p>
        
			
		<p style="display:none;">
			<label for="<?php echo $this->get_field_name( 'embed_code' ); ?>"><?php _e( 'Embed Code:' ); ?></label> 
			<textarea id="<?php echo $this->get_field_id( 'embed_code' ); ?>" name="<?php echo $this->get_field_name( 'embed_code' ); ?>" 
					  class="widefat"><?php echo esc_attr( $embed_code ); ?></textarea>
        </p>
		
		<?php if (empty($campaign_id)) : ?>
			<p>
				If you haven't created a Campaign yet you can create one for free. <br>
				<a href="index.php?page=instawell-widget-admin-page.php">Create Campaign</a>
			</p>
		<?php endif;  ?>
		
        <?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
        $instance['title']  = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
        $instance['height'] = !empty($new_instance['height']) ? (int) $new_instance['height'] : 280;  
        $instance['width']  = !empty($new_instance['width']) ? (int) $new_instance['width'] : 500;
		$instance['embed_code']  = !empty($new_instance['embed_code']) ? $new_instance['embed_code'] : null;
		
		if (empty($instance['embed_code'])) {
			$instance['campaign_code']	= $old_instance['campaign_code'];
		} else {
			$embed_code	= $instance['embed_code'];
			$matches	= array();
			preg_match('/src=(\'|")(.+)(\'|")/', $embed_code, $matches);
			$url		= isset($matches['2']) ? $matches['2'] : null;
			$parts		= parse_url($url);
			$qs			= $parts['query'];
			parse_str($qs, $get_array);
			$instance['campaign_code']	= isset($get_array['campaign']) ? $get_array['campaign'] : null;
		}
		
		return $instance;
	}
	
	/**
	 * Registeres the widget 
	 */
	public function register_widget() {
		register_widget( 'Instawell_Widget_Widget' );
	}
}