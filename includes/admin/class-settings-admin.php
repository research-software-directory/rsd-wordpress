<?php
/**
 * Research Software Directory - Plugin settings class (for admin section).
 *
 * @package RSD_WP
 * @category Admin
 * @link https://research-software-directory.org
 */

namespace RSD\Admin;

/**
 * Plugin settings class.
 *
 * @since 0.11.1
 */
class Settings_Admin extends \RSD\Settings {
	/**
	 * The single instance of the class.
	 *
	 * @var Settings_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Settings_Admin
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
		add_action( 'admin_init', array( __CLASS__, 'init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_page' ) );
	}

	/**
	 * Add the admin page.
	 *
	 * @return void
	 */
	public static function add_admin_page() {
		add_options_page(
			__( 'Research Software Directory Settings', 'rsd-wordpress' ),
			__( 'Research Software Directory', 'rsd-wordpress' ),
			'manage_options',
			'rsd-wordpress',
			array( __CLASS__, 'admin_page_html' )
		);
	}

	/**
	 * Admin page HTML.
	 *
	 * @return void
	 */
	public static function admin_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'rsd-wordpress' );
				do_settings_sections( 'rsd-wordpress' );
				submit_button( __( 'Save Settings', 'rsd-wordpress' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Initialize the settings.
	 *
	 * @return void
	 */
	public static function init() {
		register_setting( 'rsd-wordpress', 'rsd_wordpress_settings' );

		add_settings_section(
			'rsd_filters_section',
			__( 'Filters', 'rsd-wordpress' ),
			array( __CLASS__, 'filters_section_html' ),
			'rsd-wordpress'
		);

		add_settings_field(
			'software_default_filters',
			__( 'Software filters', 'rsd-wordpress' ),
			array( __CLASS__, 'software_default_filters_html' ),
			'rsd-wordpress',
			'rsd_filters_section'
		);

		add_settings_field(
			'projects_default_filters',
			__( 'Projects filters', 'rsd-wordpress' ),
			array( __CLASS__, 'projects_default_filters_html' ),
			'rsd-wordpress',
			'rsd_filters_section'
		);

		add_settings_section(
			'rsd_images_section',
			__( 'Images', 'rsd-wordpress' ),
			array( __CLASS__, 'images_section_html' ),
			'rsd-wordpress'
		);

		add_settings_field(
			'img_url',
			__( 'Default image URL', 'rsd-wordpress' ),
			array( __CLASS__, 'default_img_url_html' ),
			'rsd-wordpress',
			'rsd_images_section'
		);
	}

	/**
	 * Section HTML callback.
	 *
	 * @return void
	 */
	public static function filters_section_html() {
		echo '<p>' . esc_html__( 'Configure the filters to be shown for each section using the checkboxes below.', 'rsd-wordpress' ) . '</p>' . "\n";
	}

	/**
	 * Images section HTML callback.
	 *
	 * @return void
	 */
	public static function images_section_html() {
		// Do nothing (yet).
	}

	/**
	 * Default filters HTML callback.
	 *
	 * @param string $section The section to display the filters for.
	 */
	public static function default_filters_html( $section = 'software' ) {
		$settings = get_option( 'rsd_wordpress_settings', array() );

		// Get the filters for the requested section.
		$defaults        = self::get_defaults( 'filters' );
		$default_filters = $defaults[ $section ];
		$filters         = isset( $settings[ $section . '_default_filters' ] ) ? $settings[ $section . '_default_filters' ] : array_keys( $default_filters );
		$name_attr       = 'rsd_wordpress_settings[' . $section . '_default_filters][]';

		// Display the checkboxes.
		foreach ( $default_filters as $id => $filter ) {
			$checked = false;
			if ( ! empty( $filters ) && is_array( $filters ) ) {
				$checked = in_array( $id, $filters, true ) ? ' checked' : '';
			}
			?>
			<label><input type="checkbox" name="<?php echo esc_attr( $name_attr ); ?>"
			value="<?php echo esc_attr( $id ); ?>"<?php echo esc_attr( $checked ); ?>> <?php echo esc_html( $filter ); ?></label><br>
			<?php
		}
	}

	/**
	 * Software default filters HTML callback.
	 *
	 * @return void
	 */
	public static function software_default_filters_html() {
		self::default_filters_html( 'software' );
	}

	/**
	 * Projects default filters HTML callback.
	 *
	 * @return void
	 */
	public static function projects_default_filters_html() {
		self::default_filters_html( 'projects' );
	}

	/**
	 * Default image URL HTML callback.
	 *
	 * @return void
	 */
	public static function default_img_url_html() {
		$settings = get_option( 'rsd_wordpress_settings', array() );
		$img_url  = isset( $settings['img_url'] ) ? $settings['img_url'] : '';
		?>
		<div id="rsd-default-img-preview" class="rsd-image-preview">
			<img src="<?php echo esc_url( $img_url ); ?>" alt="" class="<?php echo esc_attr( empty( $img_url ) ? 'hidden' : '' ); ?>">
		</div>
		<input type="text" id="rsd-default-img-url" name="rsd_wordpress_settings[img_url]" value="<?php echo esc_attr( $img_url ); ?>" class="regular-text">
		<button type="button" class="button" id="rsd-select-default-img"><?php esc_html_e( 'Select image', 'rsd-wordpress' ); ?></button>
		<button id="rsd-remove-default-img" type="button" class="button button-secondary reset<?php echo esc_attr( empty( $img_url ) ? ' hidden' : '' ); ?>">
			<?php esc_html_e( 'Remove image', 'rsd-wordpress' ); ?>
		</button>
		<p class="description"><?php esc_html_e( 'The default image URL to be used for items without an image.', 'rsd-wordpress' ); ?></p>
		<?php
	}
}
