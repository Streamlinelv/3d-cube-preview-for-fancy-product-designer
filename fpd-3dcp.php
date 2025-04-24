<?php

/**
 * Plugin Name: 3D Cube Preview for Fancy Product Designer
 * Plugin URI: https://www.cartbounty.com
 * Description: Add option to preview 3D cube in Fancy Product Designer
 * Version: 1.0
 * Text Domain: fpd-3dcp
 * Author: Streamline.lv
 * Author URI: http://www.majas-lapu-izstrade.lv/en
 * Requires at least: 4.6
 * Requires PHP: 7.3
 * Requires Plugins: fancy-product-designer
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FPD_3DCP_VERSION', '1.0' );
define( 'FPD_3DCP_PLUGIN_NAME', '3D Preview' );
define( 'FPD_3DCP', 'fpd-3dcp' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fpd-3dcp-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fpd-3dcp-activator.php';
	fpd_3dcp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fpd-3dcp-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fpd-3dcp-deactivator.php';
	fpd_3dcp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fpd-3dcp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new fpd_3dcp();
	$plugin->run();

}
run_plugin_name();
