<?php
/**
 * Research Software Directory
 *
 * @package     RSD
 * @author      Netherlands eScience Center
 *
 * @wordpress-plugin
 * Plugin Name: Research Software Directory for WordPress
 * Plugin URI:  https://github.com/research-software-directory/rsd-wordpress
 * Description: Displays projects and software information from the Research Software Directory API.
 * Version:     1.0.0
 * Author:      ctw@ctwhome.com (eScience Center), Vincent Twigt (Mezzo Media)
 * Author URI:  https://www.esciencecenter.nl/
 * Text domain: rsd-wordpress
 * License:     Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 */

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

register_activation_hook( __FILE__, array( 'RSD_WP', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'RSD_WP', 'plugin_deactivation' ) );

require_once RSD_WP__PLUGIN_DIR . 'includes/class-rsd-wp.php';

add_action( 'init', array( 'RSD_WP', 'init' ) );
