<?php
/**
 * Research Software Directory
 *
 * @package   RSD_WP
 * @link      https://research-software-directory.org
 * @since     0.0.1
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin main class.
 *
 * @package RSD_WP
 * @since   0.0.1
 */
class Plugin {
	/**
	 * The version of the plugin.
	 *
	 * @var string
	 */
	private static $version = '0.12.1';

	/**
	 * The name of the plugin.
	 *
	 * @var string
	 */
	private static $plugin_name = 'rsd-wordpress';

	/**
	 * The single instance of the class.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since 0.3.2
	 * @return Plugin
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
		$this->load_dependencies();
		$this->add_admin_hooks();
		$this->add_public_hooks();
	}

	/**
	 * Initializes the plugin.
	 */
	public static function init() {
		if ( ! shortcode_exists( 'research_software_directory' ) ) {
			// Add shortcode to display the table.
			add_shortcode( 'research_software_directory', array( self::get_instance(), 'process_shortcode' ) );
		}
	}

	/**
	 * Plugin activation hook.
	 */
	public static function activate() {
		// Do nothing (yet).
	}

	/**
	 * Plugin deactivation hook.
	 */
	public static function deactivate() {
		// Do nothing (yet).
	}

	/**
	 * Plugin uninstall hook.
	 */
	public static function uninstall() {
		// Do nothing (yet).
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public static function get_version() {
		return self::$version;
	}

	/**
	 * Get the plugin name.
	 *
	 * @return string
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Load required dependencies for the plugin.
	 */
	private function load_dependencies() {
		require RSD_WP__PLUGIN_DIR . 'includes/controller/class-api.php';
		require RSD_WP__PLUGIN_DIR . 'includes/controller/class-controller.php';
		require RSD_WP__PLUGIN_DIR . 'includes/models/class-filter.php';
		require RSD_WP__PLUGIN_DIR . 'includes/models/class-item.php';
		require RSD_WP__PLUGIN_DIR . 'includes/models/class-project-item.php';
		require RSD_WP__PLUGIN_DIR . 'includes/models/class-software-item.php';
		require RSD_WP__PLUGIN_DIR . 'includes/public/class-display.php';
		require RSD_WP__PLUGIN_DIR . 'includes/class-settings.php';

		if ( is_admin() ) {
			require RSD_WP__PLUGIN_DIR . 'includes/admin/class-settings-admin.php';
		}
	}

	/**
	 * Add the hooks used in the admin area.
	 */
	private function add_admin_hooks() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( __NAMESPACE__ . '\Plugin', 'enqueue_admin_scripts' ) );

			Admin\Settings_Admin::get_instance();
		}
	}

	/**
	 * Add the hooks used in the front end area.
	 */
	private function add_public_hooks() {
		add_action( 'wp_enqueue_scripts', array( __NAMESPACE__ . '\Plugin', 'enqueue_public_scripts' ) );
	}

	/**
	 * Enqueue plugin front end scripts and styles.
	 */
	public static function enqueue_public_scripts() {
		global $post;

		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( get_the_content(), 'research_software_directory' ) ) {
			return;
		}

		// Enqueue compiled stylesheet and scripts, using minified versions in production and staging environments.
		$suffix = ( wp_get_environment_type() === 'production' || wp_get_environment_type() === 'staging' ? '.min' : '' );
		wp_enqueue_style( self::get_plugin_name() . '-public', RSD_WP__PLUGIN_URL . 'dist/rsd-wordpress' . $suffix . '.css', array(), self::get_version() );
		wp_enqueue_script( self::get_plugin_name() . '-public', RSD_WP__PLUGIN_URL . 'dist/rsd-wordpress' . $suffix . '.js', array( 'jquery' ), self::get_version(), true );
	}

	/**
	 * Enqueue plugin admin scripts and styles.
	 */
	public static function enqueue_admin_scripts() {
		// Only enqueue the script on the specific admin page where it's needed.
		$current_screen = get_current_screen();
		if ( false !== strpos( $current_screen->id, 'rsd-wordpress' ) ) :
			// Enqueue media scripts for the media uploader.
			wp_enqueue_media();

			// Enqueue compiled stylesheet and scripts, using minified versions in production and staging environments.
			$handle = self::get_plugin_name() . '-admin';
			$suffix = ( wp_get_environment_type() === 'production' || wp_get_environment_type() === 'staging' ? '.min' : '' );
			wp_enqueue_style(
				$handle,
				RSD_WP__PLUGIN_URL . 'dist/rsd-wordpress-admin' . $suffix . '.css',
				array(),
				self::get_version()
			);
			wp_enqueue_script(
				$handle,
				RSD_WP__PLUGIN_URL . 'dist/rsd-wordpress-admin' . $suffix . '.js',
				array( 'jquery' ),
				self::get_version(),
				true
			);
		endif;
	}

	/**
	 * Process shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function process_shortcode( $atts ) {
		if ( is_admin() ) {
			return;
		}

		// Merge default attributes with attributes used in shortcode.
		$atts = shortcode_atts(
			array(
				'section'         => Controller::get_section(),
				'organisation-id' => Controller::get_organisation_id(),
				'limit'           => Controller::get_limit(),
			),
			$atts,
			'research_software_directory'
		);

		// If a search query is provided, set it in the controller.
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! empty( $_GET['q'] ) ) {
			Controller::set_search_query( sanitize_text_field( $_GET['q'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification

		// Process attributes.
		Controller::set_section( sanitize_text_field( $atts['section'] ) );
		Controller::set_organisation_id( sanitize_text_field( $atts['organisation-id'] ) );
		Controller::set_limit( sanitize_text_field( $atts['limit'] ) );

		// Fetch the filters from API.
		Controller::fetch_filters();

		// Make filter labels available in JS.
		$labels       = Controller::get_filter_labels();
		$localize_arr = array(
			'defaultFilterLabels' => $labels,
		);
		// If a search query is provided, also make it available in JS.
		if ( ! empty( Controller::get_search_query() ) ) {
			$localize_arr['search'] = Controller::get_search_query();
		}
		wp_localize_script( self::get_plugin_name() . '-public', 'rsdWordPressVars', $localize_arr );

		// Get items from the API.
		$items = Controller::get_items();

		// Display all components.
		return Display::display_all( $items );
	}
}
