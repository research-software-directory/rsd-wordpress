<?php
/**
 * Research Software Directory - Item - Software
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
 * Software item class.
 *
 * @since 0.1.0
 */
class Software_Item extends Item {

	/**
	 * The item name.
	 *
	 * @var string
	 */
	public $brand_name = false;

	/**
	 * The item concept DOI.
	 */
	public $concept_doi = false;

	/**
	 * The item description.
	 *
	 * @var string
	 */
	public $description = false;

	/**
	 * The item short statement.
	 *
	 * @var string
	 */
	public $short_statement = false;

	/**
	 * The item URL.
	 *
	 * @var string
	 */
	public $url = false;

	/**
	 * The item image ID.
	 *
	 * @var string
	 */
	public $image_id = false;

	/**
	 * The item contributor count.
	 *
	 * @var string
	 */
	public $contributor_cnt = 0;

	/**
	 * The item mention count.
	 *
	 * @var string
	 */
	public $mention_cnt = 0;

	/**
	 * The item programming language.
	 *
	 * @var string
	 */
	public $prog_lang = array();

	/**
	 * The item licenses.
	 *
	 * @var string
	 */
	public $licenses = array();

	/**
	 * The item closed source flag.
	 *
	 * @var boolean
	 */
	public $closed_source = false;

	/**
	 * The item constructor.
	 *
	 * @param array $data The item data.
	 */
	public function __construct( $data ) {
		parent::__construct( $data );

		$this->brand_name      = ( ! empty( $data['brand_name'] ) ? $data['brand_name'] : '' );
		$this->description     = ( ! empty( $data['description'] ) ? $data['description'] : '' );
		$this->short_statement = ( ! empty( $data['short_statement'] ) ? $data['short_statement'] : '' );
		$this->url             = ( ! empty( $data['url'] ) ? $data['url'] : '' );
		$this->image_id        = ( ! empty( $data['image_id'] ) ? $data['image_id'] : 0 );
		$this->contributor_cnt = ( ! empty( $data['contributor_cnt'] ) ? $data['contributor_cnt'] : 0 );
		$this->mention_cnt     = ( ! empty( $data['mention_cnt'] ) ? $data['mention_cnt'] : 0 );
		$this->prog_lang       = ( ! empty( $data['prog_lang'] ) ? $data['prog_lang'] : array() );
		$this->licenses        = ( ! empty( $data['licenses'] ) ? $data['licenses'] : array() );
		$this->closed_source   = ( ! empty( $data['closed_source'] ) ? $data['closed_source'] : false );
	}

	/**
	 * Get the item name.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_brand_name() {
		return $this->brand_name;
	}

	/**
	 * Get the item description.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the item short statement.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_short_statement() {
		return $this->short_statement;
	}

	/**
	 * Get the item URL.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_url() {
		return $this->url;
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
	 * Get the item contributor count.
	 *
	 * @since 0.1.0
	 * @return int
	 */
	public function get_contributor_cnt() {
		return $this->contributor_cnt;
	}

	/**
	 * Get the item mention count.
	 *
	 * @since 0.1.0
	 * @return int
	 */
	public function get_mention_cnt() {
		return $this->mention_cnt;
	}

	/**
	 * Get the item programming language(s).
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_prog_lang() {
		return $this->prog_lang;
	}

	/**
	 * Get the item licenses.
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public function get_licenses() {
		return $this->licenses;
	}

	/**
	 * Get the item closed source flag.
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function get_closed_source() {
		return $this->closed_source;
	}

	/**
	 * Get the item programming language(s) as a string.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_prog_lang_string() {
		if ( is_array( $this->prog_lang ) && count( $this->prog_lang ) > 0 ) {
			return implode( ', ', $this->prog_lang );
		} else {
			return array();
		}
	}

	/**
	 * Get the item licenses as a string.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_licenses_string() {
		if ( is_array( $this->licenses ) && count( $this->licenses ) > 0 ) {
			return implode( ', ', $this->licenses );
		} else {
			return array();
		}
	}

	/**
	 * Get the item image URL.
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_image_url() {
		return wp_get_attachment_image_url( $this->image_id, 'thumbnail' );
	}
}
