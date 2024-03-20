<?php
/**
 * Research Software Directory
 *
 * @package   RSD_WP
 * @link      https://research-software-directory.org
 * @since     0.0.1
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load plugin components.
require 'controller/class-api.php';
require 'controller/class-controller.php';
require 'models/class-filter.php';
require 'models/class-item.php';
require 'models/class-project-item.php';
require 'models/class-software-item.php';
require 'public/class-display.php';

/**
 * Plugin main class.
 *
 * @package RSD_WP
 * @since   0.0.1
 */
class Plugin {
	/**
	 * The version of the plugin.
	 *
	 * @var string
	 */
	private static $version = '0.3.2';

	/**
	 * The name of the plugin.
	 *
	 * @var string
	 */
	private static $plugin_name = 'rsd-wordpress';

	/**
	 * The single instance of the class.
	 *
	 * @var Plugin|null
	 */
	private static $_instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since 0.3.2
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		if ( defined( 'RSD_WP_VERSION' ) ) {
			self::$version = RSD_WP_VERSION;
		}
	}

	/**
	 * Initializes the plugin.
	 */
	public static function init() {
		if ( ! shortcode_exists( 'research_software_directory' ) ) {
			// Add shortcode to display the table.
			add_shortcode( 'research_software_directory', array( self::get_instance(), 'process_shortcode' ) );
		}
	}

	/**
	 * Plugin activation hook.
	 */
	public static function activate() {
		// Do nothing (yet).
	}

	/**
	 * Plugin deactivation hook.
	 */
	public static function deactivate() {
		// Do nothing (yet).
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public static function get_version() {
		return self::$version;
	}

	/**
	 * Get the plugin name.
	 *
	 * @return string
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Process shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function process_shortcode( $atts ) {
		if ( is_admin() ) {
			return;
		}

		// Merge default attributes with attributes used in shortcode.
		$atts = shortcode_atts(
			array(
				'section'         => Controller::get_section(),
				'organisation-id' => Controller::get_organisation_id(),
				'limit'           => Controller::get_limit(),
			),
			$atts,
			'research_software_directory'
		);

		// Process attributes.
		Controller::set_section( sanitize_text_field( $atts['section'] ) );
		Controller::set_organisation_id( sanitize_text_field( $atts['organisation-id'] ) );
		Controller::set_limit( sanitize_text_field( $atts['limit'] ) );

		// Get items from the API.
		$items = Controller::get_items();

		// Display all components.
		return Display::display_all( $items );
	}
}
