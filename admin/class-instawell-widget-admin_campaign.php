<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://instawell.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Instawell <support@instawell.com>
 */
class Instawell_Widget_Admin {

	/**
	 *
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version		= $version;
		$this->page_name	= $this->plugin_name . '-admin-page.php';
		$this->parent_page	= 'index.php';
		$this->page_link	= "{$this->parent_page}?page={$this->page_name}";
		
		$iw_reqest			= new Instawell_request($this->plugin_name, $this->version);
		$this->iw_request	= $iw_reqest;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/instawell-widget-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/instawell-widget-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_admin_menu() {
		add_submenu_page($this->parent_page, 'Instawell Campaign', 'Instawell', 'manage_options', $this->page_name, array($this, 'manage_page'), 'dashicons-tickets', 6  );
	}
	
	protected function get_field_name($field) {
		return $this->plugin_name . '-' .$field;
	}
	
	protected function get_post_field($field, $def = null) {
		$field_name	= $this->plugin_name . '-' . $field;
		return isset($_POST[$field_name]) ? $_POST[$field_name] : $def;
	}

	protected function get_field_id($field) {
		return $this->plugin_name . '-' .$field;
	}

	public function manage_page() {
		$mode				= isset($_GET['mode']) ? $_GET['mode'] : null;
		$mode				= isset($_POST['mode']) ? $_POST['mode'] : $mode;
		
		$campaign_id		= $this->iw_request->get_campaign_id_for_site();
		switch ($mode) {
			case null	:
				if (empty($campaign_id)) {
					$this->show_create_page();
				} else {
					$campaign	= $this->iw_request->get_campaign_for_site();
					if (!empty($campaign->error)) {
						$this->show_create_page();
					} else {
						$this->show_edit_page($campaign->campaign);
					}
				}
				break;
				
			case 'addplan'	:
				$url		= $this->get_post_field('plan_url');
				$plan_id	= $this->get_post_field('plan_id');
				if (!empty($plan_id)) {
					$response	= $this->iw_request->add_plan($plan_id);
					$campaign	= $this->iw_request->get_campaign_for_site();
					if (!empty($response->error)) {
						$this->show_edit_page($campaign->campaign, array(
							'plan_error'	=> $response->error
						));							
					} else {
						$this->show_edit_page($campaign->campaign);
					}
					return;
				}
				$campaign	= $this->iw_request->get_campaign_for_site();
				$this->show_edit_page($campaign->campaign, array(
					'plan_error'	=> "Sorry, that doesn't look like a valid Plan URL"
				));
				
				break;
				
			case 'create'://process create campaign
				$this->show_create_page();
				break;
			
			case 'edit'	:
				//carry out an update
				$response	= $this->iw_request->update_campaign(array(
					'name'			=> $this->get_post_field('name'),
					'purpose'		=> $this->get_post_field('purpose'),
					'description'	=> $this->get_post_field('description'),
					'image'			=> $this->get_post_field('image'),
					'campaign_id'	=> $this->iw_request->get_campaign_id_for_site(),
				));
				$campaignResponse	= $this->iw_request->get_campaign_for_site();
				if (!empty($response->error)) {
					$this->show_edit_page($campaignResponse->campaign, array(
						'campaign_error'	=> $response->error
					));
				} else {
					$this->show_edit_page($campaignResponse->campaign);
				}
				break;
				
			case 'remove_plan'	:
				$plan_id		= isset($_GET['plan_id']) ? $_GET['plan_id'] : null;
				if ($plan_id) {
					$this->iw_request->remove_plan($plan_id);
				}
				$response		= $this->iw_request->get_campaign_for_site();
				$this->show_edit_page($response->campaign);
				break;
		}
	}
			
	
	private function show_create_page() {
		
		$errors			= null;
		$name			= $this->get_post_field('name');
		$purpose		= $this->get_post_field('purpose');
		$email			= $this->get_post_field('email');
		$image			= $this->get_post_field('image');
		$description	= $this->get_post_field('description');

		if (!empty($_POST)) {
			//process
			if (empty($name) || empty($purpose)) {
				$errors	= "Campaign Name and Purpose are both required.";
			} else if (empty($email)) {
				$errors	= "Please give us your email to create your Instawell account.";
			} else {
				$response	= $this->iw_request->create_campaign(array(
					'name'				=> $name,
					'purpose'			=> $purpose,
					'email'				=> $email,
					'image'				=> $image,
					'description'		=> $description,
					'hosted_site_url'	=> get_site_url()
				));
				if (!empty($response->error)) {
					$errors	= $response->error;
				} else {
					$campaign		= $response->campaign;
					$auth_token		= $response->auth_token;
					$this->iw_request->set_token($auth_token);
					$this->iw_request->set_campaign_for_site($campaign);
					$this->show_edit_page($campaign);
					return;
				}
			}
		}
		
		?> 
			<div class="wrap">
				<h1>Create your Campaign</h1>
				<?php if (!empty($errors)) : ?>
				<p style="background:#a00; color: #fff; padding: 10px;">
					<?=$errors?>
				</p>
				<?php endif; ?>
				<form name="form1" method="post" action="" style="max-width: 600px">
					<input type="hidden" name="mode" value="create">
					<table class="form-table">
						<tbody>
							<tr class="form-field ">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'email' ); ?>"><?php _e( 'Your Email:' ); ?></label> 
								</th>
								<td>
									<input value="<?=$email?>" class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>" name="<?php echo $this->get_field_name( 'email' ); ?>" type="text" />
								</td>
							</tr>
							<tr class="form-field ">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'name' ); ?>"><?php _e( 'Campaign Name:' ); ?></label> 
								</th>
								<td>
									<input value="<?=$name?>" class="widefat" id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" type="text" />
								</td>
							</tr>
							<tr class="form-field ">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'purpose' ); ?>"><?php _e( 'Campaign Purpose:' ); ?></label> 
								</th>
								<td>
									<select id="<?php echo $this->get_field_id( 'purpose' ); ?>" name="<?php echo $this->get_field_name( 'purpose' ); ?>">
										<option <?=$purpose == 'BRING_AWARENESS' ? ' selected="selected" ' : ''?> value="BRING_AWARENESS">Bringing awareness to a topic</option>
										<option <?=$purpose == 'HELP_SOMEONE' ? ' selected="selected" ' : ''?> value="HELP_SOMEONE">Helping a loved one</option>
										<option <?=$purpose == 'PRIVATE_COMMUNITY' ? ' selected="selected" ' : ''?> value="PRIVATE_COMMUNITY">Creating a private community</option>
									</select>	
								</td>
							</tr>
							<tr class="form-field ">
								<th scope="row">
									<label for="create_show_advanced_">Advanced Options</label> 
								</th>
								<td>
									<input class="widefat" id="create_show_advanced_" type="checkbox" value="1" />
								</td>
							</tr>
							<tr class="form-field create_hidden_field_" style="display: none;">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'description' ); ?>"><?php _e( 'Campaign Description:' ); ?></label> 
								</th>
								<td>
									<textarea class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>"><?=$description?></textarea>
								</td>
							</tr>
							<tr class="form-field create_hidden_field_" style="display: none;">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'image' ); ?>"><?php _e( 'Campaign Image URL:' ); ?></label> 
								</th>
								<td>
									<input value="<?=$image?>" class="widefat" id="<?php echo $this->get_field_id( 'image' ); ?>" name="<?php echo $this->get_field_name( 'image' ); ?>" type="text" />
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Create Campaign') ?>" />
					</p>
				</form>
			</div>
		<?php 
	}
	
	
	private function show_edit_page($campaign = null, $errors = array()) {
		$rec_response		= $this->iw_request->get_recommended_plans();
		$recommended_plans	= array();
		$already_added_ids	= array();
		if (!empty($rec_response->plans)) {
			$recommended_plans	= $rec_response->plans;
		}
		
		?> 
			<div class="wrap">
				<div id="responsiveTabsDemo" style="display: none;">
					<ul>
						<li><a href="#add_plan_tab_">Manage Plans</a></li>
						<li><a href="#manage_tab_">Manage Campaign</a></li>
					</ul>
					<div id="add_plan_tab_">
						<h1>Plans Added to your Campaign</h1>
						<?php if (empty($campaign->campaign_plans)) : ?>
							<p>No Plans added yet.</p>
						<?php endif; ?>

							
						<div class="plans-list">	
						<?php foreach ($campaign->campaign_plans as $i => $plan) : ?>
							<?php 
								$already_added_ids[$plan->id]		= $plan->id;
							?>
								<div class='plan-card-wrap'>
									<div class="plan-card" data-plan-id='<?=$plan->id?>'>
										<div style="background-image: url('<?=$plan->image?>')" class="plan-card-bg"></div>
										<div class="plan-card-bg-overlay"></div>
										<div class="plan-card-body">
											<h3><?=$plan->name?></h3>
											<h4><?=$plan->goal->title?></h4>
										</div>
									</div>
									<form name="form1" method="post" class='plan-card-remove-plan'>
										<div class="plan-options-list-cont">
											<h5>Available Options</h5>
											<ul class="plan-options-list">
												<?php foreach ($plan->all_child_plans as $i => $sub_plan) : ?>
													<li><?=$sub_plan->duration?> day - $<?=$sub_plan->price?></li>
												<?php endforeach; ?>
											</ul>
										</div>
										<a class='action-button button-secondary button-danger' href="<?=$this->page_link?>&mode=remove_plan&plan_id=<?=$plan->id?>">Remove</a>
										<div class='clearfix'></div>
									</form>
								</div>
							<?php endforeach; ?>
							<div class='clearfix'></div>
						</div>	

						<h1>Recommended Plans</h1>
						
						<?php if (empty($recommended_plans) || count($recommended_plans) == count($already_added_ids)) : ?>
							<p>No other Plans available right now.</p>
						<?php endif; ?>
							
						<?php if (!empty($errors['plan_error'])) : ?>
						<p style="background:#a00; color: #fff; padding: 10px;">
							<?=$errors['plan_error']?>
						</p>
						<?php endif; ?>
						
						<div class="plans-list">
							<?php foreach ($recommended_plans as $plan) : ?>
								<?php 
									if (!empty($already_added_ids[$plan->id])) {
										continue;;
									}
								?>
							<div class='plan-card-wrap'>
								<div class="plan-card" data-plan-id='<?=$plan->id?>'>
									<div style="background-image: url('<?=$plan->image?>')" class="plan-card-bg"></div>
									<div class="plan-card-bg-overlay"></div>
									<div class="plan-card-body">
										<h3><?=$plan->name?></h3>
										<h4><?=$plan->goal->title?></h4>
									</div>
								</div>
								<form name="form1" method="post" class='plan-card-add-plan'>
									<div class="plan-options-list-cont">
										<h5>Available Options</h5>
										<ul class="plan-options-list">
											<?php foreach ($plan->all_child_plans as $i => $sub_plan) : ?>
												<li><?=$sub_plan->duration?> day - $<?=$sub_plan->price?></li>
											<?php endforeach; ?>
										</ul>
									</div>
									<input type="hidden" name="mode" value="addplan">
									<input type="hidden" name="<?php echo $this->get_field_name( 'plan_id' ); ?>" value="<?=$plan->id?>">
									<input type="submit" name="Submit" class="action-button button-primary" value="<?php esc_attr_e('Add Plan') ?>" />
									<div class='clearfix'></div>
								</form>
							</div>
							<?php endforeach; ?>
							<div class='clearfix'></div>
						</div>
					</div>
					<div id="manage_tab_">
							<h1>Update your Campaign</h1>

							<form name="form1" method="post" action="" style="max-width: 600px">
								<?php if (!empty($errors['campaign_error'])) : ?>
								<p style="background:#a00; color: #fff; padding: 10px;">
									<?=$errors['campaign_error']?>
								</p>
								<?php endif; ?>

								<input type="hidden" name="mode" value="edit">
								<table class="form-table">
									<tbody>
										<tr class="form-field ">
											<th scope="row">
												<label for="<?php echo $this->get_field_name( 'name' ); ?>"><?php _e( 'Campaign Name:' ); ?></label> 
											</th>
											<td>
												<input class="widefat" id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" type="text" value="<?=$campaign->name?>" />
											</td>
										</tr>
										<tr class="form-field ">
											<th scope="row">
												<label for="<?php echo $this->get_field_name( 'description' ); ?>"><?php _e( 'Campaign Description:' ); ?></label> 
											</th>
											<td>
												<textarea class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>"><?=$campaign->description?></textarea>
											</td>
										</tr>
										<tr class="form-field ">
											<th scope="row">
												<label for="<?php echo $this->get_field_name( 'image' ); ?>"><?php _e( 'Campaign Image URL:' ); ?></label> 
											</th>
											<td>
												<input class="widefat" id="<?php echo $this->get_field_id( 'image' ); ?>" name="<?php echo $this->get_field_name( 'image' ); ?>" type="text" value="<?=$campaign->image?>" />
											</td>
										</tr>
										<tr class="form-field ">
											<th scope="row">
												<label for="<?php echo $this->get_field_name( 'purpose' ); ?>"><?php _e( 'Campaign Purpose:' ); ?></label> 
											</th>
											<td>
												<select id="<?php echo $this->get_field_id( 'purpose' ); ?>" name="<?php echo $this->get_field_name( 'purpose' ); ?>">
													<option <?=$campaign->purpose_code == 'BRING_AWARENESS' ? ' selected="selected" ' : ''?> value="BRING_AWARENESS">Bringing awareness to a topic</option>
													<option <?=$campaign->purpose_code == 'HELP_SOMEONE' ? ' selected="selected" ' : ''?> value="HELP_SOMEONE">Helping a loved one</option>
													<option <?=$campaign->purpose_code == 'PRIVATE_COMMUNITY' ? ' selected="selected" ' : ''?> value="PRIVATE_COMMUNITY">Creating a private community</option>
												</select>
											</td>
										</tr>
									</tbody>
								</table>
								<p class="submit">
									<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Update Campaign') ?>" />
								</p>
							</form>
					</div>
				</div>
				
			</div>
		<?php 
		
	}
}
