<?php
/**
 * Research Software Directory - Controller
 *
 * @package   RSD_WP
 * @category  Controller
 * @link      https://research-software-directory.org
 * @since     0.1.0
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Controller class.
 *
 * @since   0.1.0
 */
class Controller {

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
	public static $organisation_id = '35c17f17-6b5f-4385-aa8b-6b1d33a10157';
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
	 *
	 * @since 0.1.0
	 * @access private
	 */
	private function __construct() {
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
	 * Set the organisation ID.
	 *
	 * @param string $organisation_id The organisation ID.
	 */
	public static function set_organisation_id( $organisation_id ) {
		self::$organisation_id = $organisation_id;
	}

	/**
	 * Get the organization ID.
	 *
	 * @return string
	 */
	public static function get_organisation_id() {
		return self::$organisation_id;
	}

	/**
	 * Set the search query.
	 *
	 * @param string $search_query The search query.
	 */
	public static function set_search_query( $search_query ) {
		self::$search = $search_query;
	}

	/**
	 * Get the search query.
	 *
	 * @return string
	 */
	public static function get_search_query() {
		return self::$search;
	}

	/**
	 * Set order by parameter.
	 *
	 * @param string $orderby The order by parameter.
	 */
	public static function set_orderby( $orderby ) {
		self::$orderby = $orderby;
	}

	/**
	 * Get order by parameter.
	 *
	 * @return string
	 */
	public static function get_orderby() {
		return self::$orderby;
	}

	/**
	 * Set order parameter.
	 *
	 * @param string $order The order parameter.
	 */
	public static function set_order( $order ) {
		self::$order = $order;
	}

	/**
	 * Get order parameter.
	 *
	 * @return string
	 */
	public static function get_order() {
		return self::$order;
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
	 * Get items from the API.
	 *
	 * @param array $args The arguments.
	 */
	public static function get_items( $args = array() ) {
		// Set the default arguments.
		$defaults = array(
			'section'         => self::get_section(),
			'search'          => self::get_search_query(),
			'orderby'         => self::get_orderby(),
			'order'           => self::get_order(),
			'limit'           => self::get_limit(),
			'organisation_id' => Controller::get_organisation_id(),
		);
		$args = wp_parse_args( $args, $defaults );

		// TODO: add support for dynamic filters.

		// Call the API.
		$params = array(
			'select'       => sprintf( '*,%s!left(*)', $args['section'] ),
			'organisation' => 'eq.' . $args['organization_id'],
			'limit'        => self::get_limit(),
		);

		if ( 'projects' === $args['section'] ) {
			$path_start = 'projects_for_organisation';
		} else {
			$path_start = 'software_for_organisation';
		}

		$path = Api::build_path( $path_start, $params );
		$data = Api::get_response( $path );

		// Process data.
		$items = array();
		if ( ! empty( $data ) ) {
			$items = $data;
		}

		return $items;
	}
}
