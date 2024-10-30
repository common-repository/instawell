<?php
class Instawell_request {

	public $api_host;
	public $host;
	
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name = 'instawell-widget', $version = '1.0.0' ) {
		$this->plugin_name	= $plugin_name;
		$this->version		= $version;
		
		if (!empty($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'util.locdev') {
			$this->api_host			= 'http://api.instawell.locdev';
			$this->host				= 'http://instawell.locdev';
			
//			$this->api_host			= 'https://api-iws.instawell.com';
//			$this->host				= 'https://iws.instawell.com';
			
		} else {
			$this->api_host			= 'https://api.instawell.com';
			$this->host				= 'https://instawell.com';
		}
	}
	
	public function set_option($key, $value) {
		$opt	= "{$this->plugin_name}_{$key}";
		update_option($opt, $value);
	}
	
	public function get_option($key) {
		$opt	= "{$this->plugin_name}_{$key}";
		return get_option($opt);
	}
	
	public function set_token($token) {
		$token_opt	= "{$this->plugin_name}_iw_api_token";
		update_option($token_opt, $token);
	}
	
	public function get_token() {
		$token_opt	= "{$this->plugin_name}_iw_api_token";
		return get_option($token_opt);
	}
	
	public function post($url, $data = array()) {
		$opts	= array(
			'method'	=> 'POST',
			'headers'	=> array(
				'Content-Type'		=> 'application/json',
				'X-instawell-token'	=> $this->get_token()
			),
			'body'		=> json_encode($data)
		);
		$url			= $this->api_host . $url;
		$response		= wp_remote_post($url, $opts);
		$body_respone	= wp_remote_retrieve_body($response);
		return json_decode($body_respone);
	}
	
	public function get($url, $data = array()) {
		$opts	= array(
			'method'	=> 'GET',
			'headers'	=> array(
				'Content-Type'		=> 'application/json',
				'X-instawell-token'	=> $this->get_token()
			),
			'body'		=> $data
		);
		$url			= $this->api_host . $url;
		$response		= wp_remote_get($url, $opts);
		$body_respone	= wp_remote_retrieve_body($response);
		return json_decode($body_respone);
	}
	
	
	
	
	/**
	 * Helper methods so we don't call the api directly
	 */
	public function set_campaign_for_site($campaign) {
		$campaign_id_opt	= "{$this->plugin_name}_admin_campaign_id";
		update_option($campaign_id_opt, $campaign->id);
		
		$campaign_code_opt	= "{$this->plugin_name}_admin_campaign_code";
		update_option($campaign_code_opt, $campaign->code);
	}
	public function get_campaign_code_for_site() {
		$campaign_code_opt	= "{$this->plugin_name}_admin_campaign_code";
		$campaign_code		= get_option($campaign_code_opt);
		return $campaign_code;
	}
	public function get_campaign_id_for_site() {
		$campaign_id_opt	= "{$this->plugin_name}_admin_campaign_id";
		$campaign_id		= get_option($campaign_id_opt);
		return $campaign_id;
	}
	
	public function get_campaign_for_site() {
		$campaign_id_opt	= "{$this->plugin_name}_admin_campaign_id";
		$campaign_id		= get_option($campaign_id_opt);
		$response			= $this->get('/v3/teams/get', array(
			'team_id'	=> $campaign_id
		));
		return $response;
	}
	
	public function login($user_data) {
		$response	= $this->post('/v3/users/token', $user_data);
		return $response;
	}
	
	public function create_campaign($user_data) {
		$response	= $this->post('/v3/teams/create', $user_data);
		return $response;
	}
	
	public function update_campaign($user_data) {
		$response	= $this->post('/v3/teams/update', $user_data);
		return $response;
	}
	public function create_user($user_data) {
		$response	= $this->post('/v3/users/create', $user_data);
		return $response;
	}
	
	/**
	 * @return type
	 * @deprecated since version 2018.04.17
	 */
	public function add_plan($plan_id) {
		$campaign_id		= $this->get_campaign_id_for_site();
		$response			= $this->post('/v3/campaigns/add_plan', array(
			'remove_added'	=> 1,
			'campaign_id'	=> $campaign_id,
			'plan_id'		=> $plan_id
		));
		return $response;
	}
	
	/**
	 * @return type
	 * @deprecated since version 2018.04.17
	 */
	public function add_helper($username) {
		$campaign_id		= $this->get_campaign_id_for_site();
		$response			= $this->post('/v3/campaigns/add_helper', array(
			'remove_added'	=> 1,
			'campaign_id'	=> $campaign_id,
			'username'		=> $username
		));
		return $response;
	}
	
	
	/**
	 * @return type
	 * @deprecated since version 2018.04.17
	 */
	public function remove_plan($plan_id) {
		$campaign_id		= $this->get_campaign_id_for_site();
		$response			= $this->post('/v3/campaigns/remove_plan', array(
			'campaign_id'	=> $campaign_id,
			'plan_id'		=> $plan_id
		));
		return $response;
	}
	

	/**
	 * @return type
	 * @deprecated since version 2018.04.17
	 */
	public function get_recommended_plans() {
		$response	= $this->get('/v3/campaigns/get_recommended_plans');
		return $response;
	}
	
	
	
}
