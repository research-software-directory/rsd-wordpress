/**
 * UI class
 */

import DOM from '../helpers/dom';
import Controller from '../services/controller';

let instance = null;

class UI {
	constructor() {
		if ( ! instance ) {
			instance = this;
		}

		return instance;
	}

	hideSearchButton() {
		DOM.$container.find( '.rsd-search-bar input[type="submit"]' ).hide();
	}

	showClearFiltersButton() {
		DOM.$container.find( '.rsd-results-clear-filters' ).show();
	}

	hideClearFiltersButton() {
		DOM.$container.find( '.rsd-results-clear-filters' ).hide();
	}

	toggleClearFiltersButton() {
		if (
			DOM.getSearchTerm() ||
			Object.keys( DOM.getFilterValues() ).length !== 0
		) {
			this.showClearFiltersButton();
		} else {
			this.hideClearFiltersButton();
		}
	}

	hideFiltersSidebar() {
		DOM.$container.find( '.rsd-filter-sidebar' ).hide();
	}

	toggleFiltersSidebar() {
		const $sidebar = DOM.$container.find( '.rsd-filter-sidebar' );
		const $button = DOM.$container.find( '.rsd-filter-button button' );
		$sidebar.toggle();

		if ( $sidebar.is( ':visible' ) ) {
			$button.addClass( 'active' );
		} else {
			$button.removeClass( 'active' );
		}
	}

	hideShowMoreButton() {
		DOM.$container.find( '.rsd-results-show-more .button' ).hide();
	}

	setResultsTotalCount( count ) {
		DOM.$container
			.find( '.rsd-results-count' )
			.text( `${ count } items found` );
	}

	updateFilterValues( filters ) {
		$.each( filters, function ( identifier, filter ) {
			const $filter = DOM.$container.find(
				`.rsd-filters select[data-filter="${ identifier }"]`
			);
			// Get first placeholder item.
			const $placeholder = $filter.find( '.placeholder' );
			// Clear the filter.
			$filter.empty();
			// Add the placeholder item back and add the new filter values.
			$filter.append( $placeholder );
			// Add the new filter values.
			filter.getItems().forEach( function ( item ) {
				const value = item.name;
				const label = filter.getLabel( value );
				let selected = '';
				if (
					Controller.currentFilters[ identifier ] &&
					Controller.currentFilters[ identifier ].includes( value )
				) {
					selected = ' selected';
				}
				$filter.append(
					`<option value="${ value }"${ selected }>${ label }</option>`
				);
			} );
		} );
	}

	// Display the results.
	displayResults( items = [], totalCount = null, appendItems = false ) {
		// Get the results container.
		const $itemsContainer = DOM.$container.find( '.rsd-results-items' );

		// Empty results container if no items are provided.
		if ( ! items || ! Array.isArray( items ) || items.length === 0 ) {
			$itemsContainer.empty();
			this.setResultsTotalCount( 0 );
			return false;
		}

		// Update result count.
		this.setResultsTotalCount( totalCount || '-' );

		// Clear the results container.
		if ( ! appendItems ) {
			$itemsContainer.empty();
		}

		$.each( items, function ( index, item ) {
			let title, description, props;
			if ( 'projects' === Controller.section ) {
				title = item.title;
				description = item.subtitle || '';
				props = {
					Impact: item.getImpactCount(),
					Output: item.getOutputCount(),
				};
			} else {
				title = item.brand_name;
				description = item.short_statement || '';
				props = {
					Contributors: item.getContributorsCount(),
					Mentions: item.getMentionsCount(),
				};
			}

			let imageContainAttr = '';
			if ( item.getImageContain() ) {
				imageContainAttr = ' class="contain"';
			}

			// prettier-ignore
			$itemsContainer.append( `
				<div class="rsd-results-item column card in-viewport" data-id="${ item.getId() }" data-last-updated="${ item.getLastUpdated() }">
					<div class="card-image">
						<a href="${ item.getUrl() }" target="_blank" rel="external"><img src="${ item.getImgUrl() }"
							 alt="" title="${ title }" aria-label="${ title }"${ imageContainAttr }></a>
					</div>
					<div class="card-section">
						<h3><a href="${ item.getUrl() }" target="_blank" rel="external">${ title }</a></h3>
						<p>${ description }</p>
					</div>
					<div class="card-footer">
						<div class="rsd-results-item-specs">
							${ item.getLabels() }
						</div>
						<div class="rsd-results-item-props">
							${ item.getProps( props ) }
						</div>
					</div>
				</div>
			` );
		} );

		return true;
	}
}

export default new UI();
