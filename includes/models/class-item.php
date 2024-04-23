<?php
/**
 * Research Software Directory - Item
 *
 * @package   RSD_WP
 * @category  Model
 * @link      https://research-software-directory.org
 * @since     0.1.0
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Item class.
 *
 * @since 0.1.0
 */
abstract class Item {
	/**
	 * The item ID.
	 *
	 * @var string
	 */
	public $id = false;

	/**
	 * The item slug.
	 *
	 * @var string
	 */
	public $slug = false;

	/**
	 * The item image ID.
	 *
	 * @var string
	 */
	public $image_id = false;

	/**
	 * The item updated date.
	 *
	 * @var string
	 */
	public $updated_at = false;

	/**
	 * The item is published.
	 *
	 * @var string
	 */
	public $is_published = false;

	/**
	 * The item keywords.
	 *
	 * @var string
	 */
	public $keywords = array();

	/**
	 * The item constructor.
	 *
	 * @param array $data The item data.
	 */
	protected function __construct( $data ) {
		$this->id           = ( ! empty( $data['id'] ) ? $data['id'] : false );
		$this->slug         = ( ! empty( $data['slug'] ) ? $data['slug'] : false );
		$this->image_id     = ( ! empty( $data['image_id'] ) ? $data['image_id'] : 0 );
		$this->updated_at   = ( ! empty( $data['updated_at'] ) ? $data['updated_at'] : false );
		$this->is_published = ( ! empty( $data['is_published'] ) ? $data['is_published'] : false );
		$this->keywords     = ( ! empty( $data['keywords'] ) ? $data['keywords'] : array() );
	}

	/**
	 * Get the item ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the item slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the item image ID.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_image_id() {
		return $this->image_id;
	}

	/**
	 * Get the item keywords.
	 *
	 * @return string
	 */
	public function get_keywords() {
		return $this->keywords;
	}

	/**
	 * Get the item updated date.
	 *
	 * @return string
	 */
	public function get_updated_at() {
		return $this->updated_at;
	}

	/**
	 * Get the item is published.
	 *
	 * @return string
	 */
	public function get_is_published() {
		return $this->is_published;
	}

	/**
	 * Get the image URL.
	 *
	 * @return string The image URL.
	 */
	function get_image_url() {
		if ( empty( $this->get_image_id() ) ) {
			return false;
		}

		return Controller::get_img_base_url() . Controller::get_img_path() . '?uid=' . urlencode( $this->get_image_id() );
	}
}
