<?php
/**
 * Research Software Directory
 *
 * @package   RSD_WP
 * @link      https://research-software-directory.org
 * @since     1.0.0
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load plugin components.
require 'controller/class-api.php';
require 'controller/class-controller.php';
require 'models/class-item.php';
require 'models/class-project-item.php';
require 'models/class-software-item.php';
require 'public/class-display.php';

/**
 * Plugin main class.
 *
 * @package RSD_WP
 * @since   1.0.0
 */
class Plugin {

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Do nothing (yet).
	}

	/**
	 * Initializes the plugin.
	 */
	public static function init() {
		if ( ! shortcode_exists( 'research_software_directory' ) ) {
			// Add shortcode to display the table.
			add_shortcode( 'research_software_directory', array( __NAMESPACE__ . '\Plugin', 'process_shortcode' ) );
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
