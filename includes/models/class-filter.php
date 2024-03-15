<?php
/**
 * Research Software Directory - Filter
 *
 * @package   RSD_WP
 * @category  Model
 * @link      https://research-software-directory.org
 * @since     1.3.2
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Filter class.
 *
 * @since 1.3.2
 */
class Filter {

	/**
	 * Filter title.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Filter identifier.
	 *
	 * @var string
	 */
	public $identifier = '';

	/**
	 * The filter type.
	 *
	 * @var string
	 */
	public $type = 'select';

	/**
	 * The filter items.
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * Currently selected filter item.
	 *
	 * @var mixed
	 */
	public $current_filter = false;

	/**
	 * The item constructor.
	 *
	 * @param string $title The filter title.
	 * @param string $identifier The filter identifier.
	 * @param array $data The filter items.
	 * @param array $args The filter arguments.
	 */
	public function __construct( $title, $identifier, $data = array(), $args = array() ) {
		$this->title      = $title;
		$this->identifier = $identifier;
		$this->type	      =  ( ! empty( $args['type'] ) ? $args['type'] : 'select' );

		if ( ! empty( $data ) ) {
			$this->set_items( $data );
		}
	}

	/**
	 * Set filter title.
	 *
	 * @param string $title The filter title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Get filter title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Set filter identifier.
	 *
	 * @param string $identifier The filter identifier.
	 */
	public function set_identifier( $identifier ) {
		$this->identifier = $identifier;
	}

	/**
	 * Get filter identifier.
	 *
	 * @return string
	 */
	public function get_identifier( $prefix = '' ) {
		return $prefix . $this->identifier;
	}

	/**
	 * Set filter type.
	 *
	 * @param string $type The filter type.
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}

	/**
	 * Get filter type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Set the filter items.
	 *
	 * @param array $data The filter items API data.
	 */
	public function set_items( $data ) {
		$items = array();

		foreach ( $data as $item ) {
			$items[] = array(
				'title' => $item[ $this->get_identifier() ],
				'count' => $item[ $this->get_identifier() . '_cnt' ],
			);
		}

		$this->items = $items;
	}

	/**
	 * Get the filter items.
	 *
	 * @return array
	 */
	public function get_items() {
		return $this->items;
	}

}
