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

require 'class-api.php';

/**
 * Plugin main class.
 *
 * @package RSD_WP
 * @since   0.0.1
 */
class Plugin {

	/**
	 * The section to display.
	 *
	 * @var string
	 */
	public static $section = 'software';
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
	public static $orderby = 'impact';
	/**
	 * The order.
	 *
	 * @var string
	 */
	public static $order = 'desc';
	/**
	 * The limit.
	 *
	 * @var int
	 */
	public static $limit = 10;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Do nothing (yet).
	}

	/**
	 * Initializes the plugin.
	 */
	public static function init() {
		if ( ! shortcode_exists( 'research_software_directory_table' ) ) {
			// Add shortcode to display the table.
			add_shortcode( 'research_software_directory_table', array( 'RSD\\Plugin', 'display_all' ) );
		}
	}

	/**
	 * Plugin activation hook.
	 */
	public static function plugin_activation() {
		// Do nothing (yet).
	}

	/**
	 * Plugin deactivation hook.
	 */
	public static function plugin_deactivation() {
		// Do nothing (yet).
	}

	/**
	 * Renders all components: search bar, results settings, filter sidebar and results.
	 *
	 * @param array $atts The shortcode attributes.
	 * @return string
	 */
	public static function display_all( $atts ) {
		if ( is_admin() ) {
			return;
		}

		// Set default attributes.
		$atts = shortcode_atts(
			array(
				'organization-id' => '35c17f17-6b5f-4385-aa8b-6b1d33a10157',
				'limit'           => 10,
			),
			$atts,
			'research_software_directory_table'
		);

		// Process attributes.
		$organization_id = $atts['organization-id'];
		$limit           = (int) $atts['limit'];

		// Call the API.
		$data = Api::get_response(
			sprintf( 'software_for_organisation?select=*,software!left(*)&organisation=eq.%s&limit=%s', $organization_id, $limit )
		);

		// Render the RSD components.
		ob_start();
		?>
		<div class="rsd">
			<?php
			// phpcs:ignore
			echo self::display_search_bar();
			?>
			<?php
			// phpcs:ignore
			echo self::display_filter_sidebar();
			?>
			<?php
			if ( ! $data ) {
				echo 'No data returned from API';
			} else {
				// phpcs:ignore
				echo self::display_results( $data );
			}
			?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the search bar.
	 */
	public static function display_search_bar() {
		ob_start();

		$btn_placeholder = 'software' === self::$section ? __( 'Search software', 'rsd-wordpress' ) : __( 'Search projects', 'rsd-wordpress' );
		?>
			<div class="rsd-search-bar">
				<form action="" method="get">
					<input type="text" name="q" id="rsd-search" placeholder="<?php echo esc_html( $btn_placeholder ); ?>">
					<input type="submit" value="<?php esc_html_e( 'Search', 'rsd-wordpress' ); ?>">
				</form>
				<?php
				// phpcs:ignore
				echo self::display_results_settings();
				?>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the results settings.
	 */
	public static function display_results_settings() {
		ob_start();
		?>
			<div class="rsd-results-settings">
				<form action="" method="get">
					<label for="rsd-view"><?php esc_html_e( 'View:', 'rsd-wordpress' ); ?></label>
					<select name="rsd-view" id="rsd-view">
						<option value="card"><?php esc_html_e( 'Card', 'rsd-wordpress' ); ?></option>
						<option value="row"><?php esc_html_e( 'Row', 'rsd-wordpress' ); ?></option>
					</select>
					<label for="rsd-limit"><?php esc_html_e( 'Limit:', 'rsd-wordpress' ); ?></label>
					<select name="rsd-limit" id="rsd-limit">
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="50">50</option>
						<option value="100">100</option>
					</select>
				</form>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the filter sidebar.
	 */
	public static function display_filter_sidebar() {
		ob_start();
		?>
			<div class="rsd-filter-sidebar">
				<form action="" method="get">
					<?php
					if ( 'software' === self::$section ) {
						// phpcs:ignore
						echo self::display_software_filter();
					} else {
						// phpcs:ignore
						echo self::display_project_filter();
					}
					?>
				</form>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the software filter.
	 */
	public static function display_software_filter() {
		// phpcs:ignore
		// TODO: get the keywords and licenses from the API.
		ob_start();
		?>
			<div class="software-filter">
				<h2 class="show-for-sr"><?php esc_html_e( 'Filters', 'rsd-wordpress' ); ?></h2>
				<h3><label for="rsd-keywords"><?php esc_html_e( 'Keywords', 'rsd-wordpress' ); ?></label></h3>
				<select name="rsd-keywords" id="rsd-keywords">
					<option value="1">Keyword 1</option>
					<option value="2">Keyword 2</option>
					<option value="3">Keyword 3</option>
				</select>
				<h3><label for="rsd-license"><?php esc_html_e( 'License', 'rsd-wordpress' ); ?></label></h3>
				<select name="rsd-license" id="rsd-license">
					<option value="1">License 1</option>
					<option value="2">License 2</option>
					<option value="3">License 3</option>
				</select>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the project filter.
	 */
	public static function display_project_filter() {
		// phpcs:ignore
		// TODO: get the keywords, research domains and partners from the API.
		ob_start();
		?>
			<div class="project-filter">
				<h2 class="show-for-sr"><?php esc_html_e( 'Filters', 'rsd-wordpress' ); ?></h2>
				<div class="project-filter-status">
					<h3><?php esc_html_e( 'Project status', 'rsd-wordpress' ); ?></h3>
					<label for="rsd-status-1"><input type="checkbox" name="rsd-status[]" id="rsd-status-1" value="1"> <?php esc_html_e( 'Finished', 'rsd-wordpress' ); ?></label>
					<label for="rsd-status-2"><input type="checkbox" name="rsd-status[]" id="rsd-status-2" value="2"> <?php esc_html_e( 'In progress', 'rsd-wordpress' ); ?></label>
					<label for="rsd-status-3"><input type="checkbox" name="rsd-status[]" id="rsd-status-3" value="3"> <?php esc_html_e( 'Upcoming', 'rsd-wordpress' ); ?></label>
				</div>
				<div class="project-filter-keywords">
					<h3><label for="rsd-keywords"><?php esc_html_e( 'Keywords', 'rsd-wordpress' ); ?></label></h3>
					<select name="rsd-keywords" id="rsd-keywords">
						<option value="" class="placeholder"><?php esc_html_e( 'Filter by keywords', 'rsd-wordpress' ); ?></option>
						<option value="1">Keyword 1</option>
						<option value="2">Keyword 2</option>
						<option value="3">Keyword 3</option>
					</select>
				</div>
				<div class="project-filter-researchdomain">
					<h3><label for="rsd-researchdomain"><?php esc_html_e( 'Research domain', 'rsd-wordpress' ); ?></label></h3>
					<div class="project-filter-researchdomain">
						<h3><label for="rsd-researchdomain"><?php esc_html_e( 'Research domain', 'rsd-wordpress' ); ?></label></h3>
						<label for="rsd-researchdomain-1"><input type="checkbox" name="rsd-researchdomain[]" id="rsd-researchdomain-1" value="1"> <?php esc_html_e( 'Environment and Sustainability', 'rsd-wordpress' ); ?></label>
						<label for="rsd-researchdomain-2"><input type="checkbox" name="rsd-researchdomain[]" id="rsd-researchdomain-2" value="2"> <?php esc_html_e( 'Life Sciences', 'rsd-wordpress' ); ?></label>
						<label for="rsd-researchdomain-3"><input type="checkbox" name="rsd-researchdomain[]" id="rsd-researchdomain-3" value="3"> <?php esc_html_e( 'Natural Sciences & Engineering', 'rsd-wordpress' ); ?></label>
						<label for="rsd-researchdomain-4"><input type="checkbox" name="rsd-researchdomain[]" id="rsd-researchdomain-4" value="4"> <?php esc_html_e( 'Social Sciences & Humanities', 'rsd-wordpress' ); ?></label>
					</div>
				</div>
				<div class="project-filter-partners">
					<h3><label for="rsd-partners"><?php esc_html_e( 'Partners', 'rsd-wordpress' ); ?></label></h3>
					<select name="rsd-partners" id="rsd-partners">
						<option value="" class="placeholder"><?php esc_html_e( 'Filter by participating organisations', 'rsd-wordpress' ); ?></option>
						<option value="1">Partner 1</option>
						<option value="2">Partner 2</option>
						<option value="3">Partner 3</option>
					</select>
				</div>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the results in row or card view.
	 *
	 * @param array $items The items to display.
	 */
	public static function display_results( $items ) {
		ob_start();

		?>
		<div class="rsd-results">
			<h2 class="show-for-sr"><?php esc_html_e( 'Results', 'rsd-wordpress' ); ?></h2>
			<div class="rsd-results-stats">
				<h3 class="rsd-results-count">
					<?php
					// translators: Number of result items found.
					printf( esc_html__( '%s items found', 'rsd-wordpress' ), count( $items ) );
					?>
				</h3>
				<button class="button rsd-results-clear-filters"><?php esc_html_e( 'Clear filters', 'rsd-wordpress' ); ?></button>
				<div class="rsd-results-sort">
					<label for="rsd-sortby"><?php esc_html_e( 'Sort by', 'rsd-wordpress' ); ?></label>
					<select name="rsd-sortby" id="rsd-sortby">
						<option value="impact"><?php esc_html_e( 'Impact', 'rsd-wordpress' ); ?></option>
						<option value="name"><?php esc_html_e( 'Name', 'rsd-wordpress' ); ?></option>
						<option value="date_added"><?php esc_html_e( 'Date added', 'rsd-wordpress' ); ?></option>
						<option value="contributors"><?php esc_html_e( 'Number of contributors', 'rsd-wordpress' ); ?></option>
						<option value="mentions"><?php esc_html_e( 'Number of mentions', 'rsd-wordpress' ); ?></option>
					</select>
				</div>
			</div>
			<div class="rsd-results-items">
			<?php
			// Loop through the data and add each item as a card div.
			foreach ( $items as $item ) {
				?>
				<div class="rsd-results-item card">
					<div class="card-section">
						<h3><a href="<?php printf( 'https://research-software-directory.org/software/%s', esc_attr( $item['software']['slug'] ) ); ?>" target="_blank" rel="external"><?php echo esc_html( $item['software']['brand_name'] ); ?></a></h3>
						<p><?php echo esc_html( mb_strimwidth( $item['software']['description'], 0, 100, '...' ) ); ?></p>
					</div>
					<div class="card-footer">
						<div class="rsd-results-item-specs">
							<ul class="rsd-results-item-labels">
							<?php foreach ( $item['software']['labels'] as $label ) : ?>
								<li class="rsd-results-item-label"><?php echo esc_html( $label ); ?></li>
							<?php endforeach; ?>
							</ul>
						</div>
						<div class="rsd-results-item-props">
							<?php if ( 'software' === self::$section ) : ?>
								<div class="rsd-results-item-contributors">
									<?php esc_html_e( 'Contributors:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item['software']['contributors'] ); ?>
								</div>
								<div class="rsd-results-item-mentions">
									<?php esc_html_e( 'Mentions:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item['software']['mentions'] ); ?>
								</div>
							<?php elseif ( 'projects' === self::$section ) : ?>
								<div class="rsd-results-item-progress">
									<?php esc_html_e( 'Progress:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item['project']['progress'] ); ?>
								</div>
								<div class="rsd-results-item-mentions">
									<?php esc_html_e( 'Mentions:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item['project']['mentions'] ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			}
			?>
			</div>
			<div class="rsd-results-show-more">
				<?php
				// phpcs:ignore
				// TODO: Add the following features:
				// - (optional) show more button (using infinite scroll and AJAX loading of more results).
				?>
				<a role="" class="button rsd-results-show-more-button" href="#more"><?php esc_html_e( 'Show more', 'rsd-wordpress' ); ?></a>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
