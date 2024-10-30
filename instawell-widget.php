<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://instawell.com
 * @since             1.0.0
 * @package           Instawell_Widget
 *
 * @wordpress-plugin
 * Plugin Name:       Instawell Widget
 * Plugin URI:        https://instawell.com/apps
 * Description:       This plugin creates an Instawell widget that you can place in your sidebar.
 * Version:           1.0.2
 * Author:            Instawell
 * Author URI:        https://instawell.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       instawell-widget
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-instawell-widget-activator.php
 */
function activate_instawell_widget() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-instawell-widget-activator.php';
	Instawell_Widget_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-instawell-widget-deactivator.php
 */
function deactivate_instawell_widget() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-instawell-widget-deactivator.php';
	Instawell_Widget_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_instawell_widget' );
register_deactivation_hook( __FILE__, 'deactivate_instawell_widget' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-instawell-widget.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_instawell_widget() {

	$plugin = new Instawell_Widget();
	$plugin->run();

}
run_instawell_widget();
