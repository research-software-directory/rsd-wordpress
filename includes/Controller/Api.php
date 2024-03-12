<?php
/**
 * Research Software Directory
 *
 * @package     RSD_WP
 * @subpackage  Controller
 * @category    API
 * @link        https://research-software-directory.org
 * @since       0.0.1
 */

namespace RSD;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API class.
 *
 * @since 0.0.1
 */
class Api {
	/**
	 * The API endpoint.
	 *
	 * @since 0.0.1
	 * @access private
	 * @var string
	 */
	static private $endpoint = 'https://research-software-directory.org/api';

	/**
	 * The API version.
	 *
	 * @since 0.0.1
	 * @access private
	 * @var string
	 */
	static private $version = 'v1';

	/**
	 * Constructor.
	 *
	 * @since 0.0.1
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Get the API endpoint.
	 *
	 * @since 0.0.1
	 * @access public
	 * @return string
	 */
	public function get_endpoint() {
		return self::$endpoint;
	}

	/**
	 * Get the API version.
	 *
	 * @since 0.0.1
	 * @access public
	 * @return string
	 */
	static public function get_version() {

		return self::$version;
	}

	/**
	 * Get the API URL.
	 *
	 * @since 0.0.1
	 * @access public
	 * @param string $path The path.
	 * @return string
	 */
	static public function get_url( $path ) {
		return self::$endpoint . '/' . self::$version . '/' . $path;
	}

	/**
	 * Get the API response.
	 *
	 * @since 0.0.1
	 * @access public
	 * @param string $path The path.
	 * @param array  $args The arguments.
	 * @return array
	 */
	static public function get_response( $path, $args = array() ) {
		// Call the API
		$url = self::get_url( $path );
		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return 'Error: ' . $response->get_error_message();
		}

		// Decode the API response.
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		// Return the data.
		return $data;
	}

}
