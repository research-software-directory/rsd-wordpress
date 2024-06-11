<?php
/**
 * Research Software Directory
 *
 * @package     RSD_WP
 * @author      Netherlands eScience Center
 *
 * @wordpress-plugin
 * Plugin Name: Research Software Directory for WordPress
 * Plugin URI:  https://github.com/research-software-directory/rsd-wordpress
 * Description: Displays projects and software information from the Research Software Directory API.
 * Version:     0.11.1
 * Author:      Vincent Twigt (Mezzo Media), ctw@ctwhome.com (eScience Center)
 * Author URI:  https://www.esciencecenter.nl/
 * Text domain: rsd-wordpress
 * License:     Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 */

namespace RSD;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If the plugin was already loaded, do not load again.
if ( defined( 'RSD_WP_LOADED' ) ) {
	return;
}

define( 'RSD_WP_LOADED', true );
define( 'RSD_WP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RSD_WP__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'deactivate' ) );

require_once RSD_WP__PLUGIN_DIR . 'includes/class-plugin.php';

add_action( 'init', array( __NAMESPACE__ . '\Plugin', 'init' ) );
