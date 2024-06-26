<?php
/**
 * Research Software Directory - Item - Project
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
 * Project item class.
 *
 * @since 0.1.0
 */
class Project_Item extends Item {

	/**
	 * The item title.
	 *
	 * @var string
	 */
	public $title = false;

	/**
	 * The item subtitle.
	 *
	 * @var string
	 */
	public $subtitle = false;

	/**
	 * The item start date.
	 *
	 * @var string
	 */
	public $date_start = false;

	/**
	 * The item end date.
	 *
	 * @var string
	 */
	public $date_end = false;

	/**
	 * The item image contain flag, used for image ratio in CSS.
	 *
	 * @var string
	 */
	public $image_contain = false;

	/**
	 * The item featured flag.
	 *
	 * @var boolean
	 */
	public $is_featured = false;

	/**
	 * The item research domains.
	 *
	 * @var string
	 */
	public $research_domains = array();

	/**
	 * The item participating organisations.
	 *
	 * @var string
	 */
	public $participating_organisations = array();

	/**
	 * The item impact count.
	 *
	 * @var string
	 */
	public $impact_cnt = 0;

	/**
	 * The item output count.
	 *
	 * @var string
	 */
	public $output_cnt = 0;

	/**
	 * The item project status.
	 *
	 * @var string
	 */
	public $project_status = false;

	/**
	 * The item constructor.
	 *
	 * @param array $data The item data.
	 */
	public function __construct( $data ) {
		parent::__construct( $data );

		$this->title                       = ( ! empty( $data['title'] ) ? $data['title'] : '' );
		$this->subtitle                    = ( ! empty( $data['subtitle'] ) ? $data['subtitle'] : '' );
		$this->date_start                  = ( ! empty( $data['date_start'] ) ? $data['date_start'] : '' );
		$this->date_end                    = ( ! empty( $data['date_end'] ) ? $data['date_end'] : '' );
		$this->image_contain               = ( ! empty( $data['image_contain'] ) ? $data['image_contain'] : '' );
		$this->is_featured                 = ( ! empty( $data['is_featured'] ) ? $data['is_featured'] : false );
		$this->research_domains            = ( ! empty( $data['research_domains'] ) ? $data['research_domains'] : array() );
		$this->participating_organisations = ( ! empty( $data['participating_organisations'] ) ? $data['participating_organisations'] : array() );
		$this->impact_cnt                  = ( ! empty( $data['impact_cnt'] ) ? $data['impact_cnt'] : 0 );
		$this->output_cnt                  = ( ! empty( $data['output_cnt'] ) ? $data['output_cnt'] : 0 );
		$this->project_status              = ( ! empty( $data['project_status'] ) ? $data['project_status'] : '' );
	}

	/**
	 * Get the item title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the item subtitle.
	 *
	 * @return string
	 */
	public function get_subtitle() {
		return $this->subtitle;
	}

	/**
	 * Get the item start date.
	 *
	 * @param string $format The date format.
	 * @return string
	 */
	public function get_date_start( $format = 'Y-m-d' ) {
		if ( empty( $this->date_start ) ) {
			return '';
		}

		$timezone = get_option( 'timezone_string' );
		$date     = new \DateTimeImmutable( $this->date_start, new \DateTimeZone( $timezone ) );
		return $date->format( $format );
	}

	/**
	 * Get the item end date.
	 *
	 * @param string $format The date format.
	 * @return string
	 */
	public function get_date_end( $format = 'Y-m-d' ) {
		if ( empty( $this->date_end ) ) {
			return '';
		}

		$timezone = get_option( 'timezone_string' );
		$date     = new \DateTimeImmutable( $this->date_end, new \DateTimeZone( $timezone ) );
		return $date->format( $format );
	}

	/**
	 * Get the item image contain.
	 *
	 * @return string
	 */
	public function get_image_contain() {
		return $this->image_contain;
	}

	/**
	 * Get the item research domains.
	 *
	 * @return string
	 */
	public function get_research_domains() {
		return $this->research_domains;
	}

	/**
	 * Get the item participating organisations.
	 *
	 * @return string
	 */
	public function get_participating_organisations() {
		return $this->participating_organisations;
	}

	/**
	 * Get the item impact count.
	 *
	 * @return string
	 */
	public function get_impact_cnt() {
		return $this->impact_cnt;
	}

	/**
	 * Get the item output count.
	 *
	 * @return string
	 */
	public function get_output_cnt() {
		return $this->output_cnt;
	}

	/**
	 * Get the item project status.
	 *
	 * @return string
	 */
	public function get_project_status() {
		return $this->project_status;
	}

	/**
	 * Get the item featured flag.
	 *
	 * @return boolean
	 */
	public function is_featured() {
		return $this->is_featured;
	}

	/**
	 * Get the item progress percentage.
	 *
	 * @return string
	 */
	public function get_progress_percentage() {
		if ( empty( $this->date_start ) || empty( $this->date_end ) ) {
			return 0;
		}

		$date_start = new \DateTimeImmutable( $this->date_start );
		$date_end   = new \DateTimeImmutable( $this->date_end );
		$now        = new \DateTimeImmutable();

		if ( $now > $date_end ) {
			return 100;
		} elseif ( $now < $date_start ) {
			return 0;
		} else {
			$total   = $date_start->diff( $date_end )->days;
			$elapsed = $date_start->diff( $now )->days;
			return round( ( $elapsed / $total ) * 100 );
		}
	}
}
