<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              mailto:joshuaslaven42@gmail.com
 * @since             1.0.0
 * @package           Suitepresssso
 *
 * @wordpress-plugin
 * Plugin Name:       SuitePressSSO
 * Plugin URI:        mailto:joshuaslaven42@gmail.com
 * Description:       WordPress authentication hooks that allow wordpress to use MS credentials or vice versa.
 * Version:           1.0.0
 * Author:            Joshua Slaven
 * Author URI:        mailto:joshuaslaven42@gmail.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       suitepresssso
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$plugin_name    = "SuitePressSSO";
$plugin_version = "2.0.0";

/**
 * The code that runs during plugin activation.
 */
function activate_suitepresssso() {
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['mssso/login$'] ) ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_suitepresssso() {
	// Do nothing
}

register_activation_hook( __FILE__, 'activate_suitepresssso' );
register_deactivation_hook( __FILE__, 'deactivate_suitepresssso' );

require_once plugin_dir_path( __FILE__ ) . 'includes/ms-sdk/MemberSuite.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/ms-sso/ConciergeApiHelper.php';
require_once plugin_dir_path( __FILE__ ) . 'class-userconfig.php';
require_once plugin_dir_path( __FILE__ ) . 'class-suitepresssso-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'class-suitepresssso-public.php';

/**
 * Register all of the hooks related to the admin area functionality
 * of the plugin.
 *
 * @since    1.0.0
 * @access   private
 */
function define_admin_hooks() {
	global $plugin_name;
	global $plugin_version;

	$plugin_admin = new Suitepresssso_Admin( $plugin_name, $plugin_version );

	add_action( 'admin_menu', array( $plugin_admin, 'admin_menu' ) );
	add_action( 'admin_init', array( $plugin_admin, 'suitepress_sso_page_init' ) );

}

/**
 * Register all of the hooks related to the public-facing functionality
 * of the plugin.
 *
 * @since    1.0.0
 * @access   private
 */
function define_public_hooks() {
	global $plugin_name;
	global $plugin_version;

	$plugin_public = new Suitepresssso_Public( $plugin_name, $plugin_version );

	add_filter( 'authenticate', array( $plugin_public, 'authenticate' ), 10, 3 );
	add_filter( 'login_redirect', array( $plugin_public, 'login_redirect' ), 10, 3 );

}

define_admin_hooks();
define_public_hooks();
