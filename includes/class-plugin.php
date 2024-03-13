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
require 'class-api.php';
require 'public/class-display.php';

/**
 * Plugin main class.
 *
 * @package RSD_WP
 * @since   1.0.0
 */
class Plugin {

	/**
	 * The section to display.
	 *
	 * @var string
	 */
	public static $section = 'software';
	/**
	 * The organization ID.
	 *
	 * @var string
	 */
	public static $organization_id = '35c17f17-6b5f-4385-aa8b-6b1d33a10157';
	/**
	 * The search term.
	 *
	 * @var string
	 */
	public static $search = '';
	/**
	 * The filter.
	 *
	 * @var string
	 */
	public static $orderby = 'impact';
	/**
	 * The order.
	 *
	 * @var string
	 */
	public static $order = 'desc';
	/**
	 * The limit.
	 *
	 * @var int
	 */
	public static $limit = 10;

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
		if ( ! shortcode_exists( 'research_software_directory_table' ) ) {
			// Add shortcode to display the table.
			add_shortcode( 'research_software_directory_table', array( __NAMESPACE__ . '\Plugin', 'process_shortcode' ) );
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
	 * Set the section to display.
	 *
	 * @param string $section The section to display.
	 */
	public static function set_section( $section ) {
		self::$section = $section;
	}

	/**
	 * Get the section to display.
	 *
	 * @return string
	 */
	public static function get_section() {
		return self::$section;
	}

	/**
	 * Set the organization ID.
	 *
	 * @param string $organization_id The organization ID.
	 */
	public static function set_organization_id( $organization_id ) {
		self::$organization_id = $organization_id;
	}

	/**
	 * Get the organization ID.
	 *
	 * @return string
	 */
	public static function get_organization_id() {
		return self::$organization_id;
	}

	/**
	 * Set the limit.
	 *
	 * @param int $limit The limit.
	 */
	public static function set_limit( $limit ) {
		self::$limit = (int) $limit;
	}

	/**
	 * Get the limit.
	 *
	 * @return int
	 */
	public static function get_limit() {
		return self::$limit;
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
				'section'         => self::get_section(),
				'organization-id' => self::get_organization_id(),
				'limit'           => self::get_limit(),
			),
			$atts,
			'research_software_directory_table'
		);

		// Process attributes.
		self::set_section( sanitize_text_field( $atts['section'] ) );
		self::set_organization_id( sanitize_text_field( $atts['organization-id'] ) );
		self::set_limit( sanitize_text_field( $atts['limit'] ) );

		// Call the API.
		$path = sprintf( 'software_for_organisation?select=*,%s!left(*)&organisation=eq.%s&limit=%s', self::get_section(), self::get_organization_id(), self::get_limit() );
		$data = Api::get_response( $path );

		// Display all components.
		// phpcs:ignore
		return Display::display_all( $data );
	}
}
