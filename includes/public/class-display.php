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
	 * Default image URL.
	 *
	 * @var string
	 */
	private static $default_img_url = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

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
	 * Get the default image URL.
	 *
	 * @return string
	 */
	public static function get_default_image_url() {
		return self::$default_img_url;
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
		<div id="rsd-wordpress" class="rsd" data-section="<?php echo esc_attr( Controller::get_section() ); ?>" data-organisation_id="<?php echo esc_attr( Controller::get_organisation_id() ); ?>">
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
					<input type="text" name="q" id="rsd-search" class="rsd-search-input" placeholder="<?php echo esc_html( $btn_placeholder ); ?>">
					<input type="submit" value="<?php esc_html_e( 'Search', 'rsd-wordpress' ); ?>">
				</form>
				<?php
				// phpcs:ignore
				// echo self::display_results_settings();
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
	 * Renders the filter button.
	 */
	public static function display_filter_button() {
		ob_start();
		?>
			<div class="rsd-filter-button">
				<button class="button"><?php esc_html_e( 'Filters', 'rsd-wordpress' ); ?></button>
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
			<div class="rsd-filter-sidebar" style="display: none;">
				<form action="" method="get">
					<div class="rsd-filters rsd-filters-<?php echo esc_attr( Controller::get_section() ); ?> row large-unstack">
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

		$identifier = $filter->get_identifier( 'rsd-filter-' );
		$i = 1;
		?>
			<div class="rsd-filter columns in-viewport">
				<h3><label for="<?php echo esc_attr( $identifier ); ?>"><?php echo esc_html( $filter->get_title() ); ?></label></h3>
				<?php if ( 'multicheckbox' === $filter->get_type() ) : ?>
					<?php foreach ( $filter->get_items() as $item ) : ?>
						<?php  ?>
						<label for="<?php echo esc_attr( $identifier . '-' . $i ); ?>"><input type="checkbox" name="<?php echo esc_attr( $identifier . '[]' ); ?>" id="<?php echo esc_attr( $identifier . '-' . $i ); ?>" value="<?php echo esc_attr( $item['name'] ); ?>"> <?php echo esc_html( $filter->get_label( $item['name'] ) ); ?></label>
						<?php $i++; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<select name="<?php echo esc_attr( $identifier ); ?>" id="<?php echo esc_attr( $identifier ); ?>" data-filter="<?php echo esc_attr( $filter->get_identifier() ); ?>">
					<?php if ( 'select' === $filter->get_type() ) : ?>
						<option value="" class="placeholder"><?php echo esc_html( $filter->get_placeholder_title() ); ?></option>
					<?php endif; ?>
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

		if ( 'projects' === Controller::get_section() ) {
			$sort_fields = array(
				'impact_cnt' => __( 'Impact', 'rsd-wordpress' ),
				'output_cnt' => __( 'Output', 'rsd-wordpress' ),
				'title' => __( 'Title', 'rsd-wordpress' ),
				'date_start' => __( 'Start date', 'rsd-wordpress' ),
				'date_end' => __( 'End date', 'rsd-wordpress' ),
				'updated_at' => __( 'Last updated', 'rsd-wordpress' ),
			);
		} else {
			$sort_fields = array(
				'brand_name' => __( 'Name', 'rsd-wordpress' ),
				'mention_cnt' => __( 'Mentions', 'rsd-wordpress' ),
				'contributor_cnt' => __( 'Contributors', 'rsd-wordpress' ),
				'updated_at' => __( 'Last updated', 'rsd-wordpress' ),
			);
		}

		?>
		<div class="rsd-results">
			<h2 class="show-for-sr"><?php esc_html_e( 'Results', 'rsd-wordpress' ); ?></h2>
			<div class="rsd-results-stats row">
				<div class="rsd-results-header columns shrink in-viewport">
					<h3 class="rsd-results-count">
						<?php
						// translators: Number of result items found.
						printf( esc_html__( '%s items found', 'rsd-wordpress' ), Controller::get_result_total_count() );
						?>
					</h3>
					<button class="rsd-results-clear-filters button"<?php if ( Controller::has_search_or_filters() ) { echo ' style="display: none;"'; } ?>><?php esc_html_e( 'Clear filters', 'rsd-wordpress' ); ?></button>
				</div>
				<div class="rsd-results-controls columns in-viewport">
					<?php
					// phpcs:ignore
					echo self::display_search_bar();
					// phpcs:ignore
					echo self::display_filter_button();
					?>
					<div class="rsd-results-sort">
						<label for="rsd-sortby"><?php esc_html_e( 'Sort by', 'rsd-wordpress' ); ?></label>
						<select name="rsd-sortby" id="rsd-sortby">
						<?php foreach ( $sort_fields as $key => $value ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
						<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<?php
			// phpcs:ignore
			echo self::display_filter_sidebar();
			?>
			<div class="rsd-results-items row small-up-1 medium-up-2 large-up-3">
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
		$labels = $item->get_keywords();
		$title = $item->get_brand_name();
		$item_url = sprintf( 'https://research-software-directory.org/software/%s', $item->get_slug() );
		$aria_label = sprintf( __( "Logo for '%s'", 'rsd-wordpress' ), $title );
		$image_url = $item->get_image_url();

		if ( empty( $image_url ) ) {
			$image_url = self::get_default_image_url();
		}

		ob_start();
		?>
		<div class="rsd-results-item column card" data-id="<?php echo esc_attr( $item->get_id() ); ?>">
			<div class="card-image">
				<a href="<?php echo esc_attr( $item_url ); ?>" target="_blank" rel="external"><img src="<?php echo $image_url; ?>"
				 alt="" title="<?php echo esc_attr( $title ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>"></a>
			</div>
			<div class="card-section">
				<h3><a href="<?php echo esc_attr( $item_url ); ?>" target="_blank" rel="external"><?php echo esc_html( $item->get_brand_name() ); ?></a></h3>
				<p><?php echo esc_html( mb_strimwidth( $item->get_short_statement(), 0, 100, '...' ) ); ?></p>
			</div>
			<div class="card-footer">
				<div class="rsd-results-item-specs">
					<?php if ( ! empty( $labels) && is_array( $labels ) && count( $labels ) > 0 ) : ?>
					<ul class="rsd-results-item-spec-labels">
					<?php foreach ( $labels as $label ) : ?>
						<li class="label"><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
				<ul class="rsd-results-item-props">
					<li class="rsd-results-item-prop-contributors">
						<span aria-hidden="true" class="icon icon-contributors" title="<?php esc_attr_e( 'Contributors', 'rsd-wordpress' ); ?>"></span>
						<span class="value"><?php echo esc_html( $item->get_contributor_cnt() ); ?></span>
						<span class="prop"><?php esc_html_e( 'contributors', 'rsd-wordpress' ); ?></span>
					</li>
					<li class="rsd-results-item-prop-mentions">
						<span aria-hidden="true" class="icon icon-mentions" title="<?php esc_attr_e( 'Mentions', 'rsd-wordpress' ); ?>"></span>
						<span class="value"><?php echo esc_html( $item->get_mention_cnt() ); ?></span>
						<span class="prop"><?php esc_html_e( 'mentions', 'rsd-wordpress' ); ?></span>
					</li>
				</ul>
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
		$labels = $item->get_keywords();
		$title = $item->get_title();
		$item_url = sprintf( 'https://research-software-directory.org/projects/%s', $item->get_slug() );
		$aria_label = sprintf( __( "Logo for '%s'", 'rsd-wordpress' ), $title );
		$image_url = $item->get_image_url();
		$image_contain_attr = ( $item->get_image_contain() ? ' class="contain"' : '' );

		if ( empty( $image_url ) ) {
			$image_url = self::get_default_image_url();
		}

		ob_start();
		?>
		<div class="rsd-results-item column card" data-id="<?php echo esc_attr( $item->get_id() ); ?>">
			<div class="card-image">
				<a href="<?php echo esc_attr( $item_url ); ?>" target="_blank" rel="external"><img src="<?php echo $image_url; ?>"
				 alt="" title="<?php echo esc_attr( $title ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>"<?php echo $image_contain_attr; ?>></a>
			</div>
			<div class="card-section">
				<h3><a href="<?php echo esc_attr( $item_url ); ?>" target="_blank" rel="external"><?php echo esc_html( $item->get_title() ); ?></a></h3>
				<p><?php echo esc_html( mb_strimwidth( $item->get_subtitle(), 0, 100, '...' ) ); ?></p>
			</div>
			<div class="card-footer">
				<div class="rsd-results-item-specs">
					<?php if ( ! empty( $labels ) && is_array( $labels ) && count( $labels ) > 0 ) : ?>
					<ul class="rsd-results-item-spec-labels">
					<?php foreach ( $labels as $label ) : ?>
						<li class="label"><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
				<ul class="rsd-results-item-props">
					<li class="rsd-results-item-prop-progress">
						<span class="prop"><?php esc_html_e( 'progress:', 'rsd-wordpress' ); ?></span> <?php echo esc_html( $item->get_progress() ); ?>
					</li>
					<li class="rsd-results-item-prop-impact">
						<span aria-hidden="true" class="icon icon-impact" title="<?php esc_html_e( 'Impact', 'rsd-wordpress' ); ?>"></span>
						<span class="value"><?php echo esc_html( $item->get_impact_cnt() ); ?></span>
						<span class="prop"><?php esc_html_e( 'impact', 'rsd-wordpress' ); ?></span>
					</li>
					<li class="rsd-results-item-prop-output">
						<span aria-hidden="true" class="icon icon-output" title="<?php esc_html_e( 'Output', 'rsd-wordpress' ); ?>"></span>
						<span class="value"><?php echo esc_html( $item->get_output_cnt() ); ?></span>
						<span class="prop"><?php esc_html_e( 'output', 'rsd-wordpress' ); ?></span>
					</li>
				</ul>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
