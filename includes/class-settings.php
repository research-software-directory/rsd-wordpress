<?php
/**
 * Research Software Directory - Settings
 *
 * @package RSD_WP
 * @link https://research-software-directory.org
 */

namespace RSD;

/**
 * Plugin settings class.
 *
 * @since 0.11.1
 */
class Settings {
	/**
	 * The default settings.
	 *
	 * @var array
	 */
	private static $defaults = array();

	/**
	 * The single instance of the class.
	 *
	 * @var Settings|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Do nothing (yet).
	}

	/**
	 * Get the default settings.
	 *
	 * @param string|null $key The settings key.
	 * @param string|null $subkey The settings subkey.
	 * @return array
	 */
	public static function get_defaults( $key = null, $subkey = null ) {
		self::$defaults = array(
			'api'     => array(
				'endpoint' => 'https://research-software-directory.org/api',
				'version'  => 'v1',
			),
			'filters' => array(
				'software' => array(
					'keyword'       => __( 'Keywords', 'rsd-wordpress' ),
					'prog_language' => __( 'Programming Languages', 'rsd-wordpress' ),
					'license'       => __( 'Licenses', 'rsd-wordpress' ),
				),
				'projects' => array(
					'project_status' => __( 'Project Status', 'rsd-wordpress' ),
					'keyword'        => __( 'Keywords', 'rsd-wordpress' ),
					'domain'         => __( 'Research Domains', 'rsd-wordpress' ),
					'organisation'   => __( 'Partners', 'rsd-wordpress' ),
				),
			),
			'img_url' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', // 1x1 transparent GIF.
		);

		if ( null === $key ) {
			return self::$defaults;
		}

		if ( null === $subkey ) {
			return isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : array();
		}

		return isset( self::$defaults[ $key ][ $subkey ] ) ? self::$defaults[ $key ][ $subkey ] : array();
	}

	/**
	 * Get the settings.
	 *
	 * @param string|null $key The settings key.
	 * @return array
	 */
	public static function get_settings( $key = null ) {
		$settings = get_option( 'rsd_wordpress_settings', array() );

		if ( null === $key ) {
			// Return all settings.
			return $settings;
		} elseif ( 'filters' === $key ) {
			// Return configured filters.
			$section         = Controller::get_section();
			$default_filters = self::get_defaults( 'filters', $section );
			$config_filters  = isset( $settings[ $section . '_default_filters' ] ) ? $settings[ $section . '_default_filters' ] : array_keys( $default_filters );

			$values = array();
			foreach ( $default_filters as $filter => $label ) {
				if ( in_array( $filter, $config_filters, true ) ) {
					$values[ $filter ] = $label;
				}
			}
			return $values;
		} elseif ( 'img_url' === $key ) {
			// Return the configured default image URL.
			if ( isset( $settings['img_url'] ) && ! empty( $settings['img_url'] ) ) {
				return $settings['img_url'];
			} else {
				return self::get_defaults( 'img_url' );
			}
		}

		// Return any other requested settings.
		return isset( $settings[ $key ] ) ? $settings[ $key ] : array();
	}

	/**
	 * Get the default image URL from settings.
	 *
	 * @return string
	 */
	public static function get_default_image_url() {
		return self::get_settings( 'img_url' );
	}
}
