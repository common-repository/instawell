<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://instawell.com
 * @since      1.0.0
 *
 * @package    Instawell_Name
 * @subpackage Instawell_Name/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Instawell_Name
 * @subpackage Instawell_Name/public
 * @author     Instawell <support@instawell.com>
 */
class Instawell_Widget_Public {

	/**
	 * @var type Instawell_request
	 */
	private $iw_request;
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		$iw_reqest			= new Instawell_request($this->plugin_name, $this->version);
		$this->iw_request	= $iw_reqest;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Instawell_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instawell_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/instawell-widget-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Instawell_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instawell_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/instawell-widget-public.js', array( 'jquery' ), $this->version, false );
	}

	public function display_widget() {
		$plan_url		= $this->iw_request->get_option('widget_plan_url');
		$profile_url	= $this->iw_request->get_option('widget_profile_url');
		$campaign_id	= $this->iw_request->get_campaign_id_for_site();
		
		if (empty($campaign_id)) {
			return;
		}
		
		$qs				= array(
			'widget_campaign'		=> $campaign_id,
			'campaign_widget_host'	=> urlencode(get_site_url()),
			'team_widget_mode'		=> 'WORDPRESS'
		);
		$campaign_url	= $this->iw_request->host . '/campaign/widget/jkI_' . $campaign_id . '?' . http_build_query($qs);
		echo "<script>_IW_WIDGET.init('{$campaign_url}')</script>";
	}
}
