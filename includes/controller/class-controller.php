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
	 * The organisation ID.
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
	public static $limit = 20;

	/**
	 * The offset.
	 *
	 * @var int
	 */
	public static $offset = 0;

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
	 * Get the organisation ID.
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
	 * Set the offset.
	 *
	 * @param int $offset The offset.
	 */
	public static function set_offset( $offset ) {
		self::$offset = (int) $offset;
	}

	/**
	 * Get the offset.
	 *
	 * @return int
	 */
	public static function get_offset() {
		return self::$offset;
	}

	/**
	 * Get items from the API.
	 *
	 * @param array $args The arguments.
	 */
	public static function get_items() {
		// Set the default API path parameters.
		$defaults = array(
			// 'section'         => Controller::get_section(),
			'organisation_id' => Controller::get_organisation_id(),
			'status'          => 'eq.approved',
			'is_published'    => 'eq.true',
			// 'search'          => Controller::get_search_query(),
			// 'orderby'         => Controller::get_orderby(),
			// 'order'           => Controller::get_order(),
			'limit'           => Controller::get_limit(),
			'offset'          => Controller::get_offset(),
		);
		$params = wp_parse_args( array(), $defaults );

		// TODO: add support for dynamic filters.

		// Call the API.
		if ( 'projects' === self::get_section() ) {
			$path_start = '/rpc/projects_by_organisation';
		} else {
			$path_start = '/rpc/software_by_organisation';
		}

		$path = Api::build_path( $path_start, $params );
		$data = Api::get_response( $path );

		// Process data.
		$items = array();

		if ( ! empty( $data ) ) {
			if ( 'projects' === self::get_section() ) {
				foreach ( $data as $item ) {
					$items[] = new Project_Item( $item );
				}
			} elseif ( 'software' === self::get_section() ){
				foreach ( $data as $item ) {
					$items[] = new Software_Item( $item );
				}
			}

			return $items;
		} else {
			return false;
		}
	}
}
