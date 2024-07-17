/**
 * RSD WordPress plugin JavaScript
 *
 * @module rsd-wordpress
 * @license Apache-2.0
 */

'use strict';

import Controller from './services/controller';
import DOM from './helpers/dom';
import UI from './display/ui';
import {
	handleSearch,
	handleSearchEnterKey,
	clearFilters,
	handleSortBySelect,
	handleFiltersSelect,
	enhanceFiltersSidebar,
	enhanceResultsInfiniteScroll,
	enhanceBackToTopButton,
} from './helpers/eventhandlers';

// Main function, executed when the page is loaded.
( function ( $ ) {
	// Get container element and section.
	DOM.setContainer( $( '#rsd-wordpress' ) );
	Controller.section = DOM.$container.data( 'section' );
	Controller.organisationId = DOM.$container.data( 'organisation_id' );

	// Add a class to the body when the page is loaded.
	$( 'body' ).addClass( 'rsd-wordpress-loaded' );
	// Hide search button, since we're using the input event to trigger a search.
	UI.hideSearchButton();
	// Enhance filter select elements with Choices.js.
	UI.enhanceFilterSelects();

	// When search query is not set from PHP, check if search query or any filters are set and show the 'Clear filters' button.
	if (
		! rsdWordPressVars.search &&
		( DOM.getSearchTerm() || DOM.hasFilterValues() )
	) {
		Controller.currentFilters = DOM.getFilterValues();
		// (Re)load filters and items.
		Controller.loadFilters();
		Controller.loadItems();
		UI.showClearFiltersButton();
	} else {
		if ( rsdWordPressVars.search ) {
			// Clear any active filters, except the search term.
			const reloadResults = false;
			const clearSearch = false;
			clearFilters( reloadResults, clearSearch );
			// Search term is set, do show the 'Clear filters' button.
			UI.showClearFiltersButton();
		}
		// Hide filters sidebar by default.
		UI.hideFiltersSidebar();
		// Load items from DOM.
		Controller.items = DOM.getItems();
		Controller.currentOffset = Controller.items.length;
		Controller.itemsTotal = DOM.getItemsTotal();
	}

	/*
	Attach event handlers.
	*/

	// Enhance filters sidebar.
	enhanceFiltersSidebar();

	// Attach event handlers to search input field.
	DOM.$container
		.find( '.rsd-search-input' )
		.on( 'input', handleSearch )
		.on( 'keydown', handleSearchEnterKey );

	// Attach click event to filters toggle button.
	DOM.$container
		.find( '.rsd-filter-button button' )
		.on( 'click', UI.toggleFiltersSidebar );

	// Attach set filters event and get new results from API.
	DOM.$container
		.find( '.rsd-filters' )
		.on( 'change', 'select', handleFiltersSelect );

	// Attach change event to sort by select.
	DOM.$container
		.find( '.rsd-sortby-input' )
		.on( 'change', handleSortBySelect );

	// Attach click event to 'Clear filters' button and get new results from API.
	DOM.$container
		.find( '.rsd-results-clear-filters' )
		.on( 'click', clearFilters );

	// Hide 'Show more' button if infinite scroll is enabled.
	if ( enhanceResultsInfiniteScroll() ) {
		UI.hideShowMoreButton();
	}

	// Attach click event to 'Show more' button.
	DOM.$container
		.find( '.rsd-results-show-more .button' )
		.on( 'click', Controller.loadMoreItems );

	// Attach back to top button scroll handler and execute on page load.
	DOM.$container.find( '.rsd-back-to-top a' ).on( 'click', function () {
		$( 'html, body' ).animate( { scrollTop: 0 }, 400 );
		return false;
	} );

	$( window ).on( 'scroll', enhanceBackToTopButton );
	enhanceBackToTopButton();
} )( jQuery.noConflict() );
