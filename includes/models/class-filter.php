<?php
/**
 * Research Software Directory - Filter
 *
 * @package   RSD_WP
 * @category  Model
 * @link      https://research-software-directory.org
 * @since     0.3.2
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Filter class.
 *
 * @since 0.3.2
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
	 * The filter labels.
	 *
	 * @var array
	 */
	public $labels = array();

	/**
	 * The filter arguments.
	 *
	 * @var array
	 */
	public $args = array();

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
		$this->args       = $args;

		if ( ! empty( $args['labels'] ) ) {
			$this->set_labels( $args['labels'] );
		}

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
				'name'  => $item[ $this->get_identifier() ],
				'count' => $item[ $this->get_identifier() . '_cnt' ],
			);
		}

		$this->items = $items;
	}

	/**
	 * Get the filter items.
	 *
	 * @param bool $labeled_only Return only labeled items.
	 * @return array
	 */
	public function get_items( $labeled_only = null ) {
		if ( ( isset( $labeled_only ) && $labeled_only ) || ( isset( $this->args['labeled_only'] ) && $this->args['labeled_only'] ) ) {
			$items = array();
			$labels = self::get_labels();

			foreach ( $this->items as $item ) {
				if ( isset( $labels[ $item['name'] ] ) ) {
					$items[] = $item;
				}
			}

			return $items;
		} else {
			return $this->items;
		}
	}

	/**
	 * Get filter placeholder title.
	 *
	 * @return string
	 */
	public function get_placeholder_title() {
		if ( ! empty( $this->args['placeholder'] ) ) {
			return $this->args['placeholder'];
		} else {
			// translators: %s is the filter placeholder title.
			return sprintf( __( 'Filter by %s', 'rsd-wordpress' ), $this->get_title() );
		}
	}

	/**
	 * Set the filter labels.
	 *
	 * @param array $labels The filter labels.
	 */
	public function set_labels( $labels ) {
		$this->labels = $labels;
	}

	/**
	 * Get the filter labels.
	 *
	 * @return array
	 */
	public function get_labels() {
		if ( empty( $this->labels ) ) {
			$labels = array();

			foreach ( $this->items as $item ) {
				$labels[ $item['title'] ] = $item['title'];
			}

			$this->labels = $labels;
		}

		return $this->labels;
	}

	/**
	 * Get filter value label.
	 *
	 * @param string $name The filter value.
	 */
	public function get_label( $name ) {
		$labels = $this->get_labels();

		if ( ! empty( $labels[ $name ] ) ) {
			return $labels[ $name ];
		}

		return $name;
	}

}
