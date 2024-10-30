<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://instawell.com
 * @since      1.0.0
 *
 * @package    Instawell_Widget
 * @subpackage Instawell_Widget/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Instawell_Widget
 * @subpackage Instawell_Widget/admin
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
		 * defined in Instawell_Widget_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instawell_Widget_Loader will then create the relationship
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
		 * defined in Instawell_Widget_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instawell_Widget_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/instawell-widget-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_admin_menu() {
		add_submenu_page($this->parent_page, 'Instawell Team', 'Instawell', 'manage_options', $this->page_name, array($this, 'manage_page'), 'dashicons-tickets', 6  );
	}
	
	protected function get_field_name($field) {
		return esc_attr($this->plugin_name . '-' .$field);
	}
	
	protected function get_post_field($field, $def = null, $max_length = 400) {
		$field_name		= $this->plugin_name . '-' . $field;
		$field_value	= isset($_POST[$field_name]) ? stripslashes(sanitize_text_field($_POST[$field_name])) : $def;
		$field_value	= mb_substr($field_value, 0, $max_length);
		return $field_value;
	}

	protected function get_field_id($field) {
		return $this->plugin_name . '-' .$field;
	}

	public function manage_page() {
		$mode				= isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : null;
		$mode				= isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : $mode;

		$campaign_id		= $this->iw_request->get_campaign_id_for_site();
		$token				= $this->iw_request->get_token();
		
		switch ($mode) {
			case null	:
				if (empty($token)) {
					$this->show_create_page();
				} else {
					$this->show_embed_page();
				}
				break;
				
			case 'welcome_show'			:
				$this->iw_request->set_option('welcome_hide', false);
				if (empty($token)) {
					$this->show_create_page();
				} else {
					$this->show_embed_page();
				}
				break;

			case 'welcome_dismiss'		:
				$this->iw_request->set_option('welcome_hide', true);
				if (empty($token)) {
					$this->show_create_page();
				} else {
					$this->show_embed_page();
				}
				break;
				
			case 'confirmaccount'	:
				$this->show_create_confirm_page();
				break;
			
			case 'login'	:
				$this->show_login_page();
				break;
			
			case 'setwidget':
				$type			= 'plan';
				$description	= $this->get_post_field('description');
				$image			= $this->get_post_field('image');
				$logo			= $this->get_post_field('logo');
				$name			= $this->get_post_field('name');
				
				//check if image and logo are image URLs
				if (!empty($image) && filter_var($image, FILTER_VALIDATE_URL) == false) {
					$this->show_embed_page(array(
						'error'	=> "Background image should be a valid URL or empty."
					));
					return;
				}
				
				if (!empty($logo) && filter_var($logo, FILTER_VALIDATE_URL) == false) {
					$this->show_embed_page(array(
						'error'	=> "Logo should be a valid URL or empty."
					));
					return;
				}
				
				if (empty($type)) {
					$this->show_embed_page(array(
						'error'	=> "Please select a widget type."
					));
					return;
				}
				
				$response	= $this->iw_request->update_campaign(array(
					'team_id'		=> $campaign_id,
					'description'	=> $description,
					'name'			=> $name,
					'image'			=> $image,
					'logo'			=> $logo
				));
				$this->iw_request->set_option('widget_type', $type);
				
				$this->show_embed_page(array(
					'success'	=> 'Team settings updated'
				));
				break;
				
			case 'create'://process create campaign
				$this->show_create_page();
				break;;
		}
	}
		
	
	private function show_create_page() {
		
		$errors			= null;
		$email			= $this->get_post_field('email');

		if (!empty($_POST)) {
			//process
			if (empty($email)) {
				$errors	= "Please give us your email to create your Instawell account.";
			} else {
				$this->show_create_confirm_page();
				return;
			}
		}
		
		?> 
			<div class="wrap">
				<?php $this->show_brand_header();?>
				<h2>Create your Account</h2>
				<?php if (!empty($errors)) : ?>
				<p style="background:#a00; color: #fff; padding: 10px;">
					<?=esc_html($errors)?>
				</p>
				<?php endif; ?>
				<form name="form1" method="post" action="" style="max-width: 600px;padding: 15px 15px 0;background: #fff;">
					<input type="hidden" name="mode" value="create">
					<p>
						Enter your email to complete installation of the Instawell plugin.
						<br>
						If you already have an account with Instawell, we'll ask you to login in the next screen.
					</p>
					<table class="form-table">
						<tbody>
							<tr class="form-field ">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'email' ); ?>"><?php _e( 'Your Email:' ); ?></label> 
								</th>
								<td>
									<input value="<?=esc_attr($email)?>" class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>" name="<?php echo $this->get_field_name( 'email' ); ?>" type="text" />
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Create Account') ?>" />
					</p>
				</form>
			</div>
		<?php 
	}
	
	private function show_create_confirm_page() {
		$errors			= null;
		$email			= $this->get_post_field('email');
		$mode			= empty($_POST['mode']) ? '' : sanitize_text_field($_POST['mode']);
		
		if (!empty($_POST) && $mode == 'confirmaccount') {
			//process
			if (empty($email)) {
				$errors	= "Please give us your email to create your Instawell account.";
			} else {
				$response	= $this->iw_request->create_campaign(array(
					'name'				=> get_bloginfo('name') ? get_bloginfo('name') : 'My Blog' ,
					'email'				=> $email,
					'hosted_site_url'	=> get_site_url()
				));
				$errors	= "Oops, something went wrong. Please try again.";

				if (!empty($response->error) || empty($response->team)) {
					//if this is a login error, then show the login view and move on
					if ($response->code == 'API_ERROR_USER_EMAIL_TAKEN') {
						$this->show_login_page();
						return;
					}
					
					$errors	= $response->error;
				} else {
					$campaign		= $response->team;
					$auth_token		= $response->auth_token;
					$this->iw_request->set_token($auth_token);
					$this->iw_request->set_campaign_for_site($campaign);
					$this->show_embed_page();
					return;
				}
			}
		}
		
		?> 
			<div class="wrap">
				<?php $this->show_brand_header();?>
				<h2>Confirm your Email</h2>
				<?php if (!empty($errors)) : ?>
				<p style="background:#a00; color: #fff; padding: 10px;">
					<?=esc_html($errors)?>
				</p>
				<?php endif; ?>
				<form name="form1" method="post" action="" style="max-width: 600px;padding: 15px 15px 0;background: #fff;">
					<input type="hidden" name="mode" value="create">
					<p>
						Please make sure this is the email address you want to use. You cannot change this later.
					</p>
					<table class="form-table">
						<tbody>
							<tr class="form-field ">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'email' ); ?>"><?php _e( 'Your Email:' ); ?></label> 
								</th>
								<td>
									<input value="<?=esc_attr($email)?>" class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>" name="<?php echo $this->get_field_name( 'email' ); ?>" type="text" />
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="hidden" name="mode" value="confirmaccount">
						<a class="button-secondary" href="<?=esc_attr($this->page_link)?>"><?php esc_attr_e('Back') ?></a>
						<input style="float:right;" type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Confirm')?>" />
					</p>
				</form>
			</div>
		<?php 
	}
	
	
	private function show_login_page() {
		$errors			= null;
		$email			= $this->get_post_field('email');
		$password		= $this->get_post_field('password');
		$mode			= empty($_POST['mode']) ? '' : sanitize_text_field($_POST['mode']);
		
		if (!empty($_POST) && $mode == 'login') {
			//process
			if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
				$errors	= "Please give us your email to create your Instawell account.";
			} else {
				$login_response	= $this->iw_request->login(array(
					'email'		=> $email,
					'password'	=> $password
				));
				if (!empty($login_response->error)) {
					$errors	= $login_response->error;
				} else {
					$this->iw_request->set_token($login_response->token);
					$response	= $this->iw_request->create_campaign(array(
						'name'				=> 'My Team',
						'hosted_site_url'	=> get_site_url()
					));
					$errors	= "Oops, something went wrong. Please try again.";
					if (!empty($response->error) || empty($response->team)) {
						$errors	= $response->error;
					} else {
						$campaign		= $response->team;
						$auth_token		= $response->auth_token;
						$this->iw_request->set_token($auth_token);
						$this->iw_request->set_campaign_for_site($campaign);
						$this->show_embed_page();
						return;
					}
				}
			}
		}
		
		?> 
			<div class="wrap">
				<?php $this->show_brand_header();?>
				<h2>Login to your Account</h2>
				<?php if (!empty($errors)) : ?>
					<p style="background:#a00; color: #fff; padding: 10px;">
						<?=esc_html($errors);?>
					</p>
				<?php else : ?>
					<p style="background:#0073aa; color: #fff; padding: 10px;">
						Looks like that email address already exists. Login to your account to continune.
					</p>
				<?php endif; ?>
				<form name="form1" method="post" action="" style="max-width: 600px;padding: 15px 15px 0;background: #fff;">
					<input type="hidden" name="mode" value="create">
					<table class="form-table">
						<tbody>
							<tr class="form-field ">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'email' ); ?>"><?php _e( 'Your Email:' ); ?></label> 
								</th>
								<td>
									<input value="<?=esc_attr($email)?>" class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>" name="<?php echo $this->get_field_name( 'email' ); ?>" type="text" />
								</td>
							</tr>
							<tr class="form-field ">
								<th scope="row">
									<label for="<?php echo $this->get_field_name( 'password' ); ?>"><?php _e( 'Your Password:' ); ?></label> 
								</th>
								<td>
									<input value="" class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>" name="<?php echo $this->get_field_name( 'password' ); ?>" type="password" />
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="hidden" name="mode" value="login">
						<a class="button-secondary" href="<?=$this->page_link?>"><?php esc_attr_e('Back') ?></a>
						<input style="float:right;" type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Confirm') ?>" />
					</p>
				</form>
			</div>
		<?php 
	}
	
	
	
	private function show_embed_page($opts = array()) {
		$widget_type	= 'plan';
		$plan_url		= $this->iw_request->get_option('widget_plan_url');
		$profile_url	= $this->iw_request->get_option('widget_profile_url');
		$response		= $this->iw_request->get_campaign_for_site();
		if (empty($response->team)) {
			$this->show_create_page();
			return;
		}
		$campaign		= $response->team;
	?>

		<div class="wrap">
			<?php $this->show_brand_header();?>
			<h2>Customize Widget</h2>
			<?php if (!empty($opts['error'])) : ?>
			<p style="background:#a00; color: #fff; padding: 10px;">
				<?=esc_html($opts['error'])?>
			</p>
			<?php endif; ?>
			
			<?php if (!empty($opts['success'])) : ?>
			<p style="background:#0a0; color: #fff; padding: 10px;">
				<?=esc_html($opts['success'])?>
			</p>
			<?php endif; ?>
			
			<form name="form1" method="post" action="" style="padding: 15px 15px 0; max-width: 600px; background: #fff;">
				<p style="margin:0;">What kind of widget should be displayed?</p>
				<input type="hidden" name="mode" value="setwidget">
				<table class="form-table">
					<tbody>
						<tr class="form-field ">
							<td>
								<label for="<?php echo $this->get_field_name( 'name' ); ?>"><?php _e( 'Team Name:' ); ?></label> 
								<input type='text' class="widefat" value="<?=esc_attr($campaign->name)?>" id="<?=$this->get_field_id( 'name' ); ?>" name="<?=$this->get_field_name( 'name' ); ?>">
								<br><small>Give your Team a name.</small>
							</td>
						</tr>
						<tr class="form-field ">
							<td>
								<label for="<?php echo $this->get_field_name( 'description' ); ?>"><?php _e( 'Welcome Message:' ); ?></label> 
								<textarea class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" maxlength="400"><?=esc_textarea($campaign->description)?></textarea>
								<br><small>A message to show visitors.</small>
							</td>
						</tr>
						<tr class="form-field ">
							<td>
								<label for="<?php echo $this->get_field_name( 'logo' ); ?>"><?php _e( 'Team Logo (PNG, 50x50px minimum) :' ); ?></label> 
								<input type='text' class="widefat" value='<?=esc_attr($campaign->logo)?>' id="<?=$this->get_field_id( 'logo' ); ?>" name="<?=$this->get_field_name( 'logo' ); ?>">
								<br><small>A logo image for your Team.</small>
							</td>
						</tr> 
						<tr class="form-field ">
							<td>
								<label for="<?php echo $this->get_field_name( 'image' ); ?>"><?php _e( 'Background Image (JPG, 400x400px minimum):' ); ?></label> 
								<input type='text' class="widefat" value='<?=esc_attr($campaign->image)?>' id="<?=$this->get_field_id( 'image' ); ?>" name="<?=$this->get_field_name( 'image' ); ?>">
								<br><small>A background image for your Team.</small>
							</td>
						</tr> 
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Update Widget') ?>" />
				</p>
			</form>
			
			<?php if (!empty($campaign) && !empty($widget_type)) : ?>
			<div style="max-width: 600px;; padding: 15px; color: #31708f; background-color: #d9edf7; border-color: #bce8f1; ">
				Your widget is now ready and visible on your blog. <a href="<?=get_site_url()?>">View your widget.</a>
			</div>
			<?php endif; ?>
		</div>

	<?php
	}
	
	
	protected function show_brand_header() {
		$welcome_hide	= $this->iw_request->get_option('welcome_hide');
		?>
			<h1>
				<a href='<?=esc_attr($this->page_link)?>&mode=welcome_show' style='color:inherit;text-decoration: none;'>
					<span class="iw-logo"></span>
					Instawell
				</a>
			</h1>

<div class="welcome-panel" style='<?=$welcome_hide ? 'display:none' :'';?>'>
	<a class="welcome-panel-close" href="<?=esc_attr($this->page_link)?>&mode=welcome_dismiss" aria-label="Dismiss the welcome panel">Dismiss</a>
	<div class="welcome-panel-content">
		<h2>Thanks for installing the Instawell plugin!</h2>
		<p class="about-description">Here's how you get started:</p>
		
		<div class='welcome-panel-column-container'>
			<div class="welcome-panel-column">
				<h3>Create a Team</h3>
				<ul>
					<li><span class="welcome-icon welcome-add-page">Create an account</span></li>
					<li><span class="welcome-icon welcome-add-page">Customize your Team</span></li>
					<li><span class="welcome-icon welcome-add-page">Spread the word</span></li>
				</ul>
			</div>
			<div class="welcome-panel-column">
				<h3>Discover</h3>
				<ul>
					<li><a target="_blank" href='https://instawell.com' class="welcome-icon welcome-view-site">Features</a></li>
					<li><a target="_blank" href='https://instawell.com/plans/list' class="welcome-icon welcome-view-site">Browse Plans</a></li>
					<li><a target="_blank" href='https://instawell.com/support/pricing' class="welcome-icon welcome-view-site">Pricing</a></li>
				</ul>
			</div>
			<div class="welcome-panel-column">
				<h3>About Instawell</h3>
				<ul>
					<li><a target="_blank" href='https://instawell.com/support' class="welcome-icon welcome-learn-more">FAQ</a></li>
					<li><a target="_blank" href='https://instawell.com/integrations' class="welcome-icon welcome-learn-more">More Apps</a></li>
					<li><a target="_blank" href='https://instawell.com/support/contact.us' class="welcome-icon welcome-learn-more">Need help? Contact us</a></li>
				</ul>
			</div>
			
		</div>
	</div>
</div>
		<?php
	}
}
