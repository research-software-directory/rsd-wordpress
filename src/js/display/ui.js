/**
 * UI class
 */

import DOM from '../helpers/dom';
import Controller from '../services/controller';
import TomSelect from 'tom-select/dist/js/tom-select.base.js';
import TomSelectRemoveButton from 'tom-select/dist/js/plugins/remove_button.js';

TomSelect.define( 'remove_button', TomSelectRemoveButton );

let instance = null;

class UI {
	constructor() {
		if ( ! instance ) {
			instance = this;
		}

		this.selectInstances = {};

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
		if ( DOM.getSearchTerm() || DOM.hasFilterValues() ) {
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

	enhanceFilterSelects() {
		const self = this;

		$( '.rsd-filters select[data-filter]' ).each( function () {
			const identifier = $( this ).data( 'filter' );
			const $placeholder = $( this ).find( 'option.placeholder' ).first();
			const placeholderStr = $placeholder
				? $placeholder.text().trim()
				: '';
			$( this ).attr( 'placeholder', placeholderStr );
			if ( $placeholder ) {
				$placeholder.remove();
			}

			self.selectInstances[ identifier ] = new TomSelect( this, {
				create: false,
				searchField: [ 'value' ],
				placeholder: placeholderStr,
				plugins: [ 'remove_button' ],
			} );
		} );
	}

	updateFilterValues( filters ) {
		Object.entries( filters ).forEach( ( [ identifier, filter ] ) => {
			const selectInst = this.selectInstances[ identifier ];
			if ( selectInst ) {
				// Clear the filter.
				selectInst.clearOptions();

				// Prepare the new filter values.
				const items = filter.getItems().map( ( item ) => ( {
					value: item.name,
					text: filter.getLabel( item.name ),
				} ) );

				// Add the new filter values.
				selectInst.addOptions( items );
				selectInst.refreshOptions( false );
			}
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
					progress: {
						label: 'Progress',
						value: item.getProgressPercentage(),
					},
					impact: {
						label: 'Impact references',
						value: item.getImpactCount(),
					},
					output: {
						label: 'Research outputs',
						value: item.getOutputCount(),
					},
				};
			} else {
				title = item.brand_name;
				description = item.short_statement || '';
				props = {
					contributors: {
						label: 'Contributors',
						value: item.getContributorsCount(),
					},
					mentions: {
						label: 'Mentions',
						value: item.getMentionsCount(),
					},
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
