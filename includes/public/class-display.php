<?php
/**
 * Research Software Directory - Display
 *
 * Class for rendering the front end HTML output of the plugin.
 *
 * @package     RSD_WP
 * @category    Display
 * @link        https://research-software-directory.org
 * @since       0.1.0
 */

namespace RSD;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display class.
 *
 * @since 0.1.0
 */
class Display {
	/**
	 * The single instance of the class.
	 *
	 * @var Display|null
	 */
	private static $_instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since 0.3.2
	 * @return Display
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
	}

	/**
	 * Renders all components: search bar, results settings, filter sidebar and results.
	 *
	 * @param array $items The items to display.
	 * @return string
	 */
	public static function display_all( $items ) {

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
			if ( ! $items ) {
				esc_html_e( 'No data returned from API', 'rsd-wordpress' );
			} else {
				// phpcs:ignore
				echo self::display_results( $items );
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

		$btn_placeholder = 'software' === Controller::get_section() ? __( 'Search software', 'rsd-wordpress' ) : __( 'Search projects', 'rsd-wordpress' );
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
					<div class="rsd-filters rsd-filters-<?php echo esc_attr( Controller::get_section() ); ?>">
						<h2 class="show-for-sr"><?php esc_html_e( 'Filters', 'rsd-wordpress' ); ?></h2>
						<?php
						foreach ( Controller::get_filters() as $filter ) {
							// phpcs:ignore
							echo self::display_filter( $filter );
						}
						?>
					</div>
				</form>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render filter class to HTML.
	 *
	 * @param Filter $filter The filter object to display.
	 * @return string The filter HTML.
	 */
	public static function display_filter( $filter ) {
		ob_start();

		$identifier = $filter->get_identifier( 'rsd-' );
		$i = 1;
		?>
			<div class="rsd-filter">
				<h3><label for="<?php echo esc_attr( $identifier ); ?>"><?php echo esc_html( $filter->get_title() ); ?></label></h3>
				<?php if ( 'multicheckbox' === $filter->get_type() ) : ?>
					<?php foreach ( $filter->get_items() as $item ) : ?>
						<?php  ?>
						<label for="<?php echo esc_attr( $identifier . '-' . $i ); ?>"><input type="checkbox" name="<?php echo esc_attr( $identifier . '[]' ); ?>" id="<?php echo esc_attr( $identifier . '-' . $i ); ?>" value="<?php echo esc_attr( $item['name'] ); ?>"> <?php echo esc_html( $filter->get_label( $item['name'] ) ); ?></label>
						<?php $i++; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<select name="<?php echo esc_attr( $identifier ); ?>" id="<?php echo esc_attr( $identifier ); ?>">
					<?php foreach ( $filter->get_items() as $item ) : ?>
						<option value="<?php echo esc_attr( $item['name'] ); ?>"><?php echo esc_html( $filter->get_label( $item['name'] ) ); ?></option>
					<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Renders the results in row or card view.
	 *
	 * @param array $items The items to display.
	 * @return string The results HTML.
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
					printf( esc_html__( '%s items found', 'rsd-wordpress' ), Controller::get_result_total_count() );
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
				if ( 'projects' === Controller::get_section() ) {
					// phpcs:ignore
					echo self::display_project_item( $item );
				} else {
					// phpcs:ignore
					echo self::display_software_item( $item );
				}
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

	/**
	 * Display a Software result item.
	 *
	 * @param Software_Item $item The item to display.
	 * @return string The item HTML.
	 */
	public static function display_software_item( $item ) {
		$domain = __( 'Example', 'rsd-wordpress' );
		$labels = $item->get_keywords();

		ob_start();
		?>
		<div class="rsd-results-item card">
			<div class="card-section">
				<h3><a href="<?php printf( 'https://research-software-directory.org/software/%s', esc_attr( $item->get_slug() ) ); ?>" target="_blank" rel="external"><?php echo esc_html( $item->get_brand_name() ); ?></a></h3>
				<p><?php echo esc_html( mb_strimwidth( $item->get_short_statement(), 0, 100, '...' ) ); ?></p>
			</div>
			<div class="card-footer">
				<div class="rsd-results-item-specs">
					<?php if ( ! empty( $domain ) ) : ?>
					<p class="rsd-result-item-domain"><strong class="label"><?php echo esc_html( $domain ); ?></strong></p>
					<?php endif; ?>
					<?php if ( ! empty( $labels) && is_array( $labels ) && count( $labels ) > 0 ) : ?>
					<ul class="rsd-results-item-labels">
					<?php foreach ( $labels as $label ) : ?>
						<li class="label"><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
				<div class="rsd-results-item-props">
					<div class="rsd-results-item-contributors">
						<?php esc_html_e( 'Contributors:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item->get_contributor_cnt() ); ?>
					</div>
					<div class="rsd-results-item-mentions">
						<?php esc_html_e( 'Mentions:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item->get_mention_cnt() ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Display a Project result item.
	 *
	 * @param Project_Item $item The item to display.
	 * @return string The item HTML.
	 */
	public static function display_project_item( $item ) {
		$domain = __( 'Example', 'rsd-wordpress' );
		$labels = $item->get_keywords();

		ob_start();
		?>
		<div class="rsd-results-item card">
			<div class="card-section">
				<h3><a href="<?php printf( 'https://research-software-directory.org/projects/%s', esc_attr( $item->get_slug() ) ); ?>" target="_blank" rel="external"><?php echo esc_html( $item->get_title() ); ?></a></h3>
				<p><?php echo esc_html( mb_strimwidth( $item->get_subtitle(), 0, 100, '...' ) ); ?></p>
			</div>
			<div class="card-footer">
				<div class="rsd-results-item-specs">
					<?php if ( ! empty( $domain ) ) : ?>
					<p class="rsd-result-item-domain"><strong class="label"><?php echo esc_html( $domain ); ?></strong></p>
					<?php endif; ?>
					<?php if ( ! empty( $labels ) && is_array( $labels ) && count( $labels ) > 0 ) : ?>
					<ul class="rsd-results-item-labels">
					<?php foreach ( $labels as $label ) : ?>
						<li class="label"><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
				<div class="rsd-results-item-props">
					<div class="rsd-results-item-progress">
						<?php esc_html_e( 'Progress:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item->get_progress() ); ?>
					</div>
					<div class="rsd-results-item-impact">
						<?php esc_html_e( 'Impact:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item->get_impact_cnt() ); ?>
					</div>
					<div class="rsd-results-item-output">
						<?php esc_html_e( 'Output:', 'rsd-wordpress' ); ?> <?php echo esc_html( $item->get_output_cnt() ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
