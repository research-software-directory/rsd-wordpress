/**
 * Controller class
 */

import Filter from '../components/filter';
import API from './api';
import DOM from '../helpers/dom';
import UI from '../display/ui';
import { enhanceResultsInfiniteScroll } from '../helpers/eventhandlers';
import Item from '../components/item';

let instance = null;

class Controller {
	constructor() {
		if ( ! instance ) {
			this.section = 'software';
			this.organisationId = '';

			this.items = [];
			this.itemsTotal = 0;
			this.currentOffset = 0;
			this.currentFilters = {};

			this.defaultLimit = 48;
			this.defaultFilterLabels = rsdWordPressVars.defaultFilterLabels || {
				project_status: {
					upcoming: 'Upcoming',
					in_progress: 'In progress',
					finished: 'Finished',
					unknown: 'Unknown',
				},
			};

			instance = this;
		}

		return instance;
	}

	// Set current filter values.
	setCurrentFilters( filter, value ) {
		if ( ! Array.isArray( value ) ) {
			this.currentFilters[ filter ] = [ value ];
		} else {
			this.currentFilters[ filter ] = value;
		}
	}

	// Clear internal variable for current filters.
	clearCurrentFilters() {
		this.currentFilters = {};
	}

	// Fetch result items from the API.
	async fetchItems(
		searchTerm = false,
		filters = false,
		orderBy = false,
		order = false,
		offset = 0
	) {
		// Get the search term and filter values.
		searchTerm = searchTerm
			? searchTerm.toLowerCase().trim()
			: DOM.getSearchTerm();
		filters = filters ? filters : DOM.getFilterValues();
		orderBy = orderBy ? orderBy : DOM.getOrderBy();
		order = order ? order : DOM.getOrder( orderBy );
		offset = offset ? offset : 0;

		// Build the API URL based on section.
		let path = '';
		const params = {
			organisation_id: this.organisationId,
			status: 'eq.approved',
			is_published: 'eq.true',
			limit: this.defaultLimit,
			// eslint-disable-next-line object-shorthand
			offset: offset,
		};

		if ( orderBy ) {
			params.order = API.getOrder( orderBy, order );
		}

		if ( this.section === 'projects' ) {
			if ( searchTerm !== '' ) {
				path = '/rpc/projects_by_organisation_search';
				params.search = searchTerm;
			} else {
				path = '/rpc/projects_by_organisation';
			}
		} else if ( this.section === 'software' ) {
			if ( searchTerm !== '' ) {
				path = '/rpc/software_by_organisation_search';
				params.search = searchTerm;
			} else {
				path = '/rpc/software_by_organisation';
			}
		}

		// Map filter field to its API parameter, then add its values to API URL params.
		const filterParamMap = {
			keyword: 'keywords',
			prog_language: 'prog_lang',
			license: 'licenses',
			project_status: 'project_status',
			domain: 'research_domain',
			organisation: 'participating_organisations',
		};

		Object.keys( filterParamMap ).forEach( ( filterKey ) => {
			const paramKey = filterParamMap[ filterKey ];
			const filterValue = filters[ filterKey ];
			if ( filterValue && filterValue.length > 0 ) {
				if ( paramKey === 'project_status' ) {
					params[ paramKey ] = `eq.${ filterValue
						.flat()
						.map( ( value ) => value.toLowerCase() ) }`;
				} else {
					params[ paramKey ] =
						'cs.{' +
						filterValue
							.map( ( value ) => `"${ value }"` )
							.join( ',' ) +
						'}';
				}
			}
		} );

		// Get the data from the API.
		return new Promise( ( resolve, reject ) => {
			const req = $.ajax( {
				type: 'GET',
				url: API.getUrl( path, params ),
				headers: { Prefer: 'count=exact' },
				// eslint-disable-next-line object-shorthand
				success: ( response ) => {
					// Create an array of Item objects from the response.
					const resultItems = [];
					if ( response && Array.isArray( response ) ) {
						response.forEach( ( data ) => {
							resultItems.push( new Item( data ) );
						} );
					}

					// Get the total count of results from `content-range` response header.
					let totalResults = false;
					const contentRange =
						req.getResponseHeader( 'content-range' );
					if ( contentRange ) {
						const total = contentRange.split( '/' );
						totalResults = total[ 1 ];
						this.itemsTotal = parseInt( totalResults );
					}

					resolve( resultItems );
				},
				// eslint-disable-next-line object-shorthand
				error: ( jqXHR, textStatus, errorThrown ) => {
					reject( errorThrown );
				},
			} );
		} );
	}

	// Get the filters from the API.
	async fetchFilters() {
		const filters = {};
		const defaultArgs = {};
		const defaultParams = {
			organisation_id: this.organisationId,
		};

		if ( DOM.getSearchTerm() !== '' ) {
			defaultParams.search_filter = DOM.getSearchTerm();
		}

		const filtersDefault = {
			projects: {
				project_status: {
					title: 'Project status',
					identifier: 'project_status',
					args: {
						...defaultArgs,
						labels: { ...this.defaultFilterLabels.project_status },
					},
					filter_as_param: 'status_filter',
					path: '/rpc/org_project_status_filter?order=project_status',
					params: { ...defaultParams },
				},
				keyword: {
					title: 'Keywords',
					identifier: 'keyword',
					args: { ...defaultArgs },
					filter_as_param: 'keyword_filter',
					path: '/rpc/org_project_keywords_filter?order=keyword',
					params: { ...defaultParams },
				},
				domain: {
					title: 'Research domains',
					identifier: 'domain',
					args: {
						...defaultArgs,
						labeledOnly: true,
						labels: { ...this.defaultFilterLabels.domain },
					},
					filter_as_param: 'research_domain_filter',
					path: '/rpc/org_project_domains_filter?order=domain',
					params: { ...defaultParams },
				},
				organisation: {
					title: 'Partners',
					identifier: 'organisation',
					args: { ...defaultArgs },
					filter_as_param: 'organisation_filter',
					path: '/rpc/org_project_participating_organisations_filter?order=organisation',
					params: { ...defaultParams },
				},
			},
			software: {
				keyword: {
					title: 'Keywords',
					identifier: 'keyword',
					args: { ...defaultArgs },
					filter_as_param: 'keyword_filter',
					path: '/rpc/org_software_keywords_filter?order=keyword',
					params: { ...defaultParams },
				},
				prog_language: {
					title: 'Programming Languages',
					identifier: 'prog_language',
					args: { ...defaultArgs },
					filter_as_param: 'prog_lang_filter',
					path: '/rpc/org_software_languages_filter?order=prog_language',
					params: { ...defaultParams },
				},
				license: {
					title: 'Licenses',
					identifier: 'license',
					args: { ...defaultArgs },
					filter_as_param: 'license_filter',
					path: '/rpc/org_software_licenses_filter?order=license',
					params: { ...defaultParams },
				},
			},
		};

		// Build filters object for the current section, narrowed down by filter values.
		const filterReqs = filtersDefault[ this.section ] || {};
		const filterValues = DOM.getFilterValues();
		const requests = [];

		// Add any filter values to the filter requests.
		Object.keys( filterReqs ).forEach( ( filter ) => {
			Object.keys( filterValues ).forEach( ( valueId ) => {
				if (
					valueId === 'project_status' &&
					filter === 'project_status'
				) {
					// Skip the project_status filter if it's set to project_status.
					return;
				}

				const param = filterReqs[ valueId ].filter_as_param || false;
				if ( param ) {
					if ( valueId === 'project_status' ) {
						filterReqs[ filter ].params[ param ] =
							filterValues[ valueId ][ 0 ] || '';
					} else {
						filterReqs[ filter ].params[ param ] =
							filterValues[ valueId ];
					}
				}
			} );
		} );

		// Get filter data from the API for each filter.
		Object.entries( filterReqs ).forEach( ( [ filter, data ] ) => {
			const promise = new Promise( ( resolve, reject ) => {
				$.ajax( {
					type: 'POST',
					url: API.getUrl( data.path ),
					data: JSON.stringify( data.params ),
					dataType: 'json',
					contentType: 'application/json',
					// eslint-disable-next-line object-shorthand
					success: ( response ) => {
						filters[ filter ] = new Filter(
							data.title,
							data.identifier,
							response,
							data.args
						);
						resolve();
					},
					// eslint-disable-next-line object-shorthand
					error: ( jqXHR, textStatus, errorThrown ) => {
						reject( errorThrown );
					},
				} );
			} );

			requests.push( promise );
		} );

		return Promise.all( requests ).then( () => {
			return filters;
		} );
	}

	// Load items wrapper.
	async loadItems() {
		try {
			// Fetch the items from the API.
			const offset = 0;
			this.items = await this.fetchItems(
				DOM.getSearchTerm(),
				DOM.getFilterValues(),
				DOM.getOrderBy(),
				DOM.getOrder( DOM.getOrderBy() ),
				offset
			);
			this.currentOffset = offset + this.items.length;

			// Display the results.
			UI.displayResults( this.items, this.itemsTotal );
			// Re-attach infinite scroll event.
			enhanceResultsInfiniteScroll();
		} catch ( error ) {
			console.error( '🎹 Error fetching items: ', error );
		}
	}

	// Load more items wrapper.
	async loadMoreItems() {
		if ( ! this.hasMoreItems() ) {
			return;
		}

		try {
			// Fetch more items from the API.
			const offset = this.currentOffset;
			const newItems = await this.fetchItems(
				DOM.getSearchTerm(),
				DOM.getFilterValues(),
				DOM.getOrderBy(),
				DOM.getOrder( DOM.getOrderBy() ),
				offset
			);
			this.items = this.items.concat( newItems );
			this.currentOffset = offset + newItems.length;

			// Append the results.
			const appendItems = true;
			UI.displayResults( newItems, this.itemsTotal, appendItems );
		} catch ( error ) {
			console.error( '🎹 Error fetching more items: ', error );
		}
	}

	// Check if there are more items.
	hasMoreItems() {
		return this.itemsTotal === 0 || this.items.length < this.itemsTotal;
	}

	// Load filters wrapper.
	async loadFilters() {
		return this.fetchFilters()
			.then( ( filters ) => {
				UI.updateFilterValues( filters );
				return filters;
			} )
			.catch( ( error ) => {
				console.error( '🎹 Error fetching filters: ', error );
			} );
	}
}

export default new Controller();
