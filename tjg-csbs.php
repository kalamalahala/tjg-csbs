<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/kalamalahala
 * @since             1.0.0
 * @package           Tjg_Csbs
 *
 * @wordpress-plugin
 * Plugin Name:       Cornerstone Business Solutions - The Johnson Group
 * Plugin URI:        https://thejohnson.group/csb/tk/
 * Description:       Functionality for the Cornerstone Business Solutions path on The Johnson Group website.
 * Version:           1.0.0
 * Author:            Tyler Karle
 * Author URI:        https://github.com/kalamalahala
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tjg-csbs
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get local wpdb prefix
global $wpdb;
$prefix = $wpdb->prefix;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TJG_CSBS_VERSION', '1.0.0' );
define( 'TJG_CSBS_TABLE_NAME', $prefix . 'tjg_csbs_candidates' );
define( 'TJG_CSBS_LOG_TABLE_NAME', $prefix . 'tjg_csbs_log' );
define( 'TJG_CSBS_NOTES_TABLE_NAME', $prefix . 'tjg_csbs_notes' );
define( 'TJG_CSBS_CALL_LOG_TABLE_NAME', $prefix . 'tjg_csbs_call_log' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tjg-csbs-activator.php
 */
function activate_tjg_csbs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tjg-csbs-activator.php';
	Tjg_Csbs_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tjg-csbs-deactivator.php
 */
function deactivate_tjg_csbs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tjg-csbs-deactivator.php';
	Tjg_Csbs_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tjg_csbs' );
register_deactivation_hook( __FILE__, 'deactivate_tjg_csbs' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tjg-csbs.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tjg_csbs() {

	$plugin = new Tjg_Csbs();
	$plugin->run();

}
run_tjg_csbs();
