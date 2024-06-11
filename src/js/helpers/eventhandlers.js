/**
 * Event handlers functions
 */

import DOM from './dom';
import Controller from '../services/controller';
import UI from '../display/ui';

// Init variables.
let delayTimer;
let scrollObserver = null;

// Search field - attach search event and get new results from API.
// (executing with a slight delay after entry changes, so that the search term is not sent with every character)
// (also, if the user presses Enter, the search is executed immediately)

function handleSearch() {
	clearTimeout( delayTimer );
	const searchTerm = $( this ).val().toLowerCase();
	delayTimer = setTimeout( function () {
		Controller.loadFilters();
		Controller.loadItems( searchTerm );
		if ( searchTerm.trim() === '' ) {
			UI.hideClearFiltersButton();
		} else {
			UI.showClearFiltersButton();
		}
	}, 500 );
}

function handleSearchEnterKey( e ) {
	if ( e.keyCode === 13 ) {
		e.preventDefault();
		clearTimeout( delayTimer );
		handleSearch.call( this );
	}
}

async function clearFilters( reloadResults = true, clearSearch = true ) {
	if ( clearSearch ) {
		DOM.$container.find( '.rsd-search-input' ).val( '' );
	}
	DOM.$container.find( '.rsd-filters select' ).val( '' );

	Controller.clearCurrentFilters();

	if ( reloadResults ) {
		await Promise.all( [
			Controller.loadFilters(),
			Controller.loadItems(),
		] );
	}

	if (
		! DOM.getSearchTerm() &&
		Object.keys( DOM.getFilterValues() ).length === 0
	) {
		UI.hideClearFiltersButton();
	}
}

function handleSortBySelect() {
	Controller.loadItems();
}

function enhanceFiltersSidebar() {
	const $sidebar = DOM.$container.find( '.rsd-filter-sidebar' );

	// Add close button to filters sidebar.
	$sidebar.prepend( `
		<button class="close-button" aria-label="Close alert" type="button"
			<span aria-hidden="true">&times;</span>
		</button>
	` );

	$sidebar.find( '.close-button' ).on( 'click', UI.toggleFiltersSidebar );
}

// Attach event to select filters and get new results from API.
// Note: should be attached to jQuery object, not to the DOM object, since `$( this )` is used.
function handleFiltersSelect() {
	Controller.setCurrentFilters( $( this ).data( 'filter' ), $( this ).val() );
	Controller.loadFilters();
	Controller.loadItems();
}

// Attach infinite scroll event that automatically loads more results (if any).
function enhanceResultsInfiniteScroll() {
	if ( IntersectionObserver === undefined ) {
		return false;
	}

	const targetElement = $( '.rsd-results-show-more' )[ 0 ];

	if ( scrollObserver ) {
		// Continue observing the target element.
		if ( Controller.hasMoreItems() ) {
			scrollObserver.observe( targetElement );
		}
	} else {
		// Start observing the target element.
		scrollObserver = new IntersectionObserver(
			async ( entries, observer ) => {
				if ( entries[ 0 ].isIntersecting ) {
					if ( Controller.hasMoreItems() ) {
						Controller.loadMoreItems();
					} else {
						observer.unobserve( entries[ 0 ].target );
					}
				}
			}
		);

		scrollObserver.observe( targetElement );
	}

	return true;
}

function enhanceBackToTopButton() {
	const offset = DOM.$container.offset().top || 100;
	const $button = DOM.$container.find( '.rsd-back-to-top' );

	if ( $( window ).scrollTop() > offset ) {
		$button.addClass( 'visible' );
	} else {
		$button.removeClass( 'visible' );
	}
}

export {
	handleSearch,
	handleSearchEnterKey,
	clearFilters,
	handleSortBySelect,
	handleFiltersSelect,
	enhanceFiltersSidebar,
	enhanceResultsInfiniteScroll,
	enhanceBackToTopButton,
};
