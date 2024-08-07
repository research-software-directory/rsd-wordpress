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
	 * The link base URL.
	 *
	 * @var string
	 */
	public static $link_base_url = 'https://research-software-directory.org';

	/**
	 * The image base URL.
	 *
	 * @var string
	 */
	public static $img_base_url = 'https://research-software-directory.org';

	/**
	 * Image path.
	 *
	 * @var string
	 */
	public static $img_path = '/image/rpc/get_image';

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
	public static $orderby = '';

	/**
	 * The order.
	 *
	 * @var string
	 */
	public static $order = '';

	/**
	 * The limit.
	 *
	 * @var int
	 */
	public static $limit = 48;

	/**
	 * The offset.
	 *
	 * @var int
	 */
	public static $offset = 0;

	/**
	 * The filters.
	 *
	 * @var array
	 */
	public static $filters = array();

	/**
	 * The result total items count.
	 *
	 * @var int The result total items count. False if result is not cached yet.
	 * @since 0.3.1
	 */
	private static $result_total_count = false;

	/**
	 * The result items.
	 *
	 * @var bool|array The result items. False if result is not cached yet.
	 * @since 0.3.1
	 */
	private static $result_items = false;

	/**
	 * The instance of the Controller class.
	 *
	 * @var Controller|null
	 */
	private static $instance = null;

	/**
	 * Get the instance of the Controller class.
	 *
	 * @since 0.3.2
	 * @return Controller
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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
	 * Set the link base URL.
	 *
	 * @param string $url The link base URL.
	 */
	public static function set_link_base_url( $url ) {
		self::$link_base_url = $url;
	}

	/**
	 * Get the link base URL.
	 *
	 * @return string
	 */
	public static function get_link_base_url() {
		return self::$link_base_url;
	}

	/**
	 * Set the image base URL.
	 *
	 * @param string $url The image base URL.
	 */
	public static function set_img_base_url( $url ) {
		self::$img_base_url = $url;
	}

	/**
	 * Get the image base URL.
	 *
	 * @return string
	 */
	public static function get_img_base_url() {
		return self::$img_base_url;
	}

	/**
	 * Set the image path.
	 *
	 * @param string $path The image path.
	 */
	public static function set_img_path( $path ) {
		self::$img_path = $path;
	}

	/**
	 * Get the image path.
	 *
	 * @return string
	 */
	public static function get_img_path() {
		return self::$img_path;
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
	 * Set the filters.
	 *
	 * @param array $filters The filters.
	 */
	public static function set_filters( $filters ) {
		self::$filters = $filters;
	}

	/**
	 * Get the filters.
	 *
	 * @param string $identifier The filter identifier.
	 * @return array
	 */
	public static function get_filters( $identifier = false ) {
		if ( ! empty( $identifier ) ) {
			return array( self::$filters[ $identifier ] );
		} else {
			return self::$filters;
		}
	}

	/**
	 * Return if search query or filters are set.
	 *
	 * @return bool
	 */
	public static function has_search_or_filters() {
		return ( ! empty( self::get_search_query() ) || ! empty( self::get_filters() ) );
	}

	/**
	 * Fetch all filters from API.
	 *
	 * @param string $section The section to fetch filters for (defaults to current section).
	 * @return array
	 *
	 * @since 0.3.2
	 */
	public static function fetch_filters( $section = false ) {
		$section = ( $section ? $section : self::get_section() );

		// Set the default arguments.
		$default_args = array(
			'multiple' => true,
		);

		// Set the default API path parameters.
		$default_params = array(
			'organisation_id' => self::get_organisation_id(),
		);
		// Add search query to parameters, if set.
		if ( ! empty( self::get_search_query() ) ) {
			$default_params['search_filter'] = self::get_search_query();
		}

		// Set the default filters.
		// phpcs:disable
		$filters_default = array(
			'projects' => array(
				'project_status' => array(
					'title'      => __( 'Project Status', 'rsd-wordpress' ),
					'identifier' => 'project_status',
					'args'       => wp_parse_args(
						array(
							'labels' => array(
								'upcoming'    => __( 'Upcoming', 'rsd-wordpress' ),
								'in_progress' => __( 'In progress', 'rsd-wordpress' ),
								'finished'    => __( 'Finished', 'rsd-wordpress' ),
								'unknown'     => __( 'Unknown', 'rsd-wordpress' ),
							),
							'placeholder' => __( 'Filter by project status', 'rsd-wordpress' ),
						),
						$default_args
					),
					'path'       => '/rpc/org_project_status_filter',
					'params'     => wp_parse_args(
						array(
							'order' => 'project_status',
						),
						$default_params
					),
				),
				'keywords' => array(
					'title'      => __( 'Keywords', 'rsd-wordpress' ),
					'identifier' => 'keyword',
					'args'       => wp_parse_args(
						array(
							'placeholder' => __( 'Filter by keyword', 'rsd-wordpress' ),
						),
						$default_args
					),
					'path'       => '/rpc/org_project_keywords_filter',
					'params'     => wp_parse_args(
						array(
							'order' => 'keyword',
						),
						$default_params
					),
				),
				'research_domains' => array(
					'title'      => __( 'Research Domains', 'rsd-wordpress' ),
					'identifier' => 'domain',
					'args'       => wp_parse_args(
						array(
							'labeled_only' => true,
							'placeholder'  => __( 'Filter by research domain', 'rsd-wordpress' ),
						),
						$default_args
					),
					'path'       => '/rpc/org_project_domains_filter',
					'params'     => wp_parse_args(
						array(
							'order' => 'domain',
						),
						$default_params
					),
				),
				'partners' => array(
					'title'      => __( 'Partners', 'rsd-wordpress' ),
					'identifier' => 'organisation',
					'args'       => wp_parse_args(
						array(
							'placeholder'  => __( 'Filter by partner', 'rsd-wordpress' ),
						),
						$default_args
					),
					'path'       => '/rpc/org_project_participating_organisations_filter',
					'params'     => wp_parse_args(
						array(
							'order' => 'organisation',
						),
						$default_params
					),
				),
			),
			'software' => array(
				'keywords'  => array(
					'title'      => __( 'Keywords', 'rsd-wordpress' ),
					'identifier' => 'keyword',
					'args'       => wp_parse_args(
						array(
							'placeholder' => __( 'Filter by keyword', 'rsd-wordpress' ),
						),
						$default_args
					),
					'path'       => '/rpc/org_software_keywords_filter',
					'params'     => wp_parse_args(
						array(
							'order' => 'keyword',
						),
						$default_params
					),
				),
				'programming_languages' => array(
					'title'      => __( 'Programming Languages', 'rsd-wordpress' ),
					'identifier' => 'prog_language',
					'args'       => wp_parse_args(
						array(
							'placeholder' => __( 'Filter by programming language', 'rsd-wordpress' ),
						),
						$default_args
					),
					'path'       => '/rpc/org_software_languages_filter',
					'params'     => wp_parse_args(
						array(
							'order'  => 'prog_language',
						),
						$default_params
					),
				),
				'license' => array(
					'title'      => __( 'Licenses', 'rsd-wordpress' ),
					'identifier' => 'license',
					'args'       => wp_parse_args(
						array(
							'placeholder' => __( 'Filter by license', 'rsd-wordpress' ),
						),
						$default_args
					),
					'path'       => '/rpc/org_software_licenses_filter',
					'params'     => wp_parse_args(
						array(
							'order'  => 'license',
						),
						$default_params
					),
				),
			),
		);
		// phpcs:enable

		// Load the configured filters, and prepare the filters to be requested from the API.
		$filters_config = Settings::get_settings( 'filters' );
		$filters_req    = array();
		foreach ( $filters_default[ $section ] as $filter ) {
			if ( isset( $filters_config[ $filter['identifier'] ] ) ) {
				$filters_req[ $filter['identifier'] ] = $filter;
			}
		}

		// Prepare the filters var.
		$filters = array();

		// Set the API paths and parameters for configured filters.
		foreach ( $filters_req as $filter ) {
			// Build the API path.
			$path = Api::build_path( $filter['path'], $filter['params'] );

			// Get the API response.
			$response = Api::get_response( $path );

			// Process data.
			$args           = ( ! empty( $filter['args'] ) ? $filter['args'] : array() );
			$id             = $filter['identifier'];
			$filters[ $id ] = new Filter( $filter['title'], $filter['identifier'], $response['data'], $args );

			// Additionally retrieve and set labels for specific filter(s).
			if ( 'domain' === $id ) {
				$path     = Api::build_path(
					'research_domain',
					array(
						'select' => 'key,name',
						'parent' => 'is.null',
					)
				);
				$response = Api::get_response( $path );

				$labels = array();
				foreach ( $response['data'] as $item ) {
					$labels[ $item['key'] ] = $item['name'];
				}
				$filters[ $id ]->set_labels( $labels );
			} elseif ( 'project_status' === $id ) {
				// Remove 'unknown' status from items.
				$filters[ $id ]->remove_item( 'unknown' );
			}
		}

		// Set the filters.
		self::set_filters( $filters );

		return $filters;
	}

	/**
	 * Get the filter labels.
	 *
	 * @param string $identifier The filter identifier.
	 * @return array
	 */
	public static function get_filter_labels( $identifier = false ) {
		$labels = array();

		$filters = self::get_filters( $identifier );
		foreach ( $filters as $filter ) {
			$labels[ $filter->get_identifier() ] = $filter->get_labels();
		}

		return $labels;
	}

	/**
	 * Set the total count of result items.
	 *
	 * @param int $total_count The total count of items.
	 */
	public static function set_result_total_count( $total_count ) {
		self::$result_total_count = (int) $total_count;
	}

	/**
	 * Get the total count of result items.
	 *
	 * @return int
	 */
	public static function get_result_total_count() {
		return self::$result_total_count;
	}

	/**
	 * Store the result items.
	 *
	 * @param array $items The result items.
	 */
	private static function set_result_items( $items ) {
		self::$result_items = $items;
	}

	/**
	 * Get the stored result items.
	 *
	 * @return array
	 */
	public static function get_result_items() {
		return self::$result_items;
	}

	/**
	 * Get items from the API.
	 *
	 * @param string $section The section to get items for.
	 * @return array|bool
	 */
	public static function get_items( $section = false ) {
		// Set the default section.
		$section = ( $section ? $section : self::get_section() );

		// Set the default API path parameters.
		$defaults = array(
			'organisation_id' => self::get_organisation_id(),
			'status'          => 'eq.approved',
			'is_published'    => 'eq.true',
			'limit'           => self::get_limit(),
			'offset'          => self::get_offset(),
		);
		$params   = wp_parse_args( array(), $defaults );

		// Add search query to parameters.
		if ( ! empty( self::get_search_query() ) ) {
			$params['search'] = self::get_search_query();
		}

		// Set order by and order parameters, or use defaults.
		$orderby = 'updated_at';
		$order   = 'desc';
		if ( ! empty( self::get_orderby() ) ) {
			$orderby = self::get_orderby();
		}
		if ( ! empty( self::get_order() ) ) {
			$order = self::get_order();
		}
		self::set_orderby( $orderby );
		self::set_order( $order );
		$params['order'] = Api::build_order_param( $orderby, $order );

		// Set the API path and parameters.
		if ( 'projects' === $section ) {
			$path_start = '/rpc/projects_by_organisation';
		} else {
			$path_start = '/rpc/software_by_organisation';
		}
		// Append _search to API path if search query is set.
		if ( ! empty( $params['search'] ) ) {
			$path_start .= '_search';
		}
		$path = Api::build_path( $path_start, $params );

		// Add `Prefer` header to also request total count of result items (may result in slower query).
		$args = array(
			'headers' => array(
				'Prefer' => 'count=exact',
			),
		);

		// Get the API response.
		$response = Api::get_response( $path, $args, true );
		$headers  = $response['headers'];
		$data     = $response['data'];

		// Process data.
		$items = array();

		// If headers contain `content-range`, then save the total count of items.
		if ( ! empty( $headers['content-range'] ) ) {
			$content_range = explode( '/', $headers['content-range'] );
			self::set_result_total_count( $content_range[1] );
		}

		// If data is not empty, then process the items.
		if ( ! empty( $data ) ) {
			if ( 'projects' === $section ) {
				foreach ( $data as $item ) {
					$items[] = new Project_Item( $item );
				}
			} elseif ( 'software' === $section ) {
				foreach ( $data as $item ) {
					$items[] = new Software_Item( $item );
				}
			}

			// Store the result items.
			self::set_result_items( $items );

			return $items;
		} else {
			return false;
		}
	}
}
