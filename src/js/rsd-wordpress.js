/**
 * RSD WordPress plugin JavaScript
 *
 * @module rsd-wordpress
 * @since 0.4.0
 * @license Apache-2.0
 */

'use strict';

jQuery( function ( $ ) {
	/*
	Variables
	*/

	// Control variables
	let items = [];
	let itemsTotal = 0;
	let currentOffset = 0;
	let scrollObserver = null;
	let currentFilters = {};
	// API
	const apiEndpoint = 'https://research-software-directory.org/api';
	const apiVersion = 'v1';
	// Default parameters
	const defaultLimit = 48;
	const defaultFilterLabels = rsdWordPressVars.defaultFilterLabels || {
		project_status: {
			upcoming: 'Upcoming',
			in_progress: 'In progress',
			finished: 'Finished',
			unknown: 'Unknown',
		},
	};

	// Get container element and section.
	const $container = $( '#rsd-wordpress' );
	const section = $container.data( 'section' );
	const organisationId = $container.data( 'organisation_id' );

	// Add a class to the body when the page is loaded.
	$( 'body' ).addClass( 'rsd-wordpress-loaded' );
	// Hide search button, since we're using the input event to trigger a search.
	hideSearchButton();
	// Check if any filters are set and show the 'Clear filters' button.
	if ( getSearchTerm() || Object.keys( getFilterValues() ).length !== 0 ) {
		currentFilters = getFilterValues();
		// (Re)load filters and items.
		loadFilters();
		loadItems();
		showClearFiltersButton();
	} else {
		// Hide filters sidebar by default.
		hideFiltersSidebar();
		// Load items from DOM.
		items = getItemsFromDOM();
		currentOffset = items.length;
		itemsTotal = getItemsTotalFromDOM();
	}
	// Attach filters sidebar event handlers.
	enhanceFiltersSidebar();

	/*
	Classes
	*/

	// Filter class
	class Filter {
		constructor( title, identifier, data = [], args = {} ) {
			const defaultArgs = {
				placeholder: '',
				showCount: true,
				labeled_only: false,
				labels: [],
			};
			this.title = title;
			this.identifier = identifier;
			this.type = args.type || 'select';
			this.args = { ...defaultArgs, ...args };

			this.labels = {};
			if ( args.labels && Object.keys( args.labels ).length !== 0 ) {
				this.setLabels( args.labels );
			}

			this.setItems( data );
		}

		getTitle() {
			return this.title;
		}

		getIdentifier( prefix = '' ) {
			return prefix + this.identifier;
		}

		getItems( labeledOnly = false ) {
			if ( labeledOnly || this.args.labeled_only ) {
				const filterItems = [];
				const labels = this.getLabels();
				$.each( this.items, function ( index, item ) {
					if ( labels[ item.name ] ) {
						filterItems.push( item );
					}
				} );
				return filterItems;
			}

			return this.items;
		}

		setItems( data ) {
			this.items = [];

			// Check if data is an array and not empty.
			if ( data && Array.isArray( data ) && data.length !== 0 ) {
				// Convert data to items.
				this.items = data.map( ( item ) => {
					const label = item[ this.getIdentifier() ];
					const num = item[ this.getIdentifier() + '_cnt' ] || 0;
					return { name: label, count: num };
				} );
			}
		}

		getPlaceholder() {
			const defaultPlaceholder =
				'Filter by ' + this.getTitle().toLowerCase(); // TODO: i18n
			return this.args.placeholder || defaultPlaceholder;
		}

		setLabels( labels ) {
			this.labels = labels;
		}

		getLabels() {
			return this.labels;
		}

		getLabel( name ) {
			let label = name;

			if ( this.args.labels && this.args.labels[ name ] ) {
				label = this.args.labels[ name ];
			}

			if ( this.args.showCount ) {
				label += ' (' + this.getItemCount( name ) + ')';
			}

			return label;
		}

		getItemCount( name ) {
			let count = 0;
			// Find the item by name and return its count.
			$.each( this.items, function ( index, item ) {
				if ( name === item.name ) {
					count = item.count;
					return false; // exit the loop
				}
			} );
			return count;
		}

		getValues() {
			return this.items.map( ( item ) => item.name );
		}
	}

	/*
	API functions
	*/

	// Get the API URL.
	function apiGetUrl( path, params = {} ) {
		if (
			params &&
			typeof params === 'object' &&
			Object.keys( params ).length !== 0
		) {
			path = path + '?' + $.param( params );
		}
		return (
			apiEndpoint + '/' + apiVersion + '/' + path.replace( /^\/+/, '' )
		);
	}

	// Get the API order string.
	function apiGetOrder( orderBy, order ) {
		const nullsLast = [
			'mention_cnt',
			'contributor_cnt',
			'impact_cnt',
			'output_cnt',
			'date_start',
			'date_end',
		];
		if ( nullsLast.includes( orderBy ) ) {
			return `${ orderBy.toLowerCase() }.${ order.toLowerCase() }.nullslast`;
		}

		return `${ orderBy.toLowerCase() }.${ order.toLowerCase() }`;
	}

	/*
	Controller functions
	*/

	// Get the result items from the API.
	async function fetchItems(
		searchTerm = false,
		filters = false,
		orderBy = false,
		order = false,
		offset = 0
	) {
		// Get the search term and filter values.
		searchTerm = searchTerm
			? searchTerm.toLowerCase().trim()
			: getSearchTerm();
		filters = filters ? filters : getFilterValues();
		orderBy = orderBy ? orderBy : getOrderBy();
		order = order ? order : getOrder( orderBy );
		offset = offset ? offset : 0;

		// Hide the 'Clear filters' button if no search term or filters are set.
		if (
			searchTerm ||
			( filters && Object.keys( filters ).length !== 0 )
		) {
			showClearFiltersButton();
		}

		// Build the API URL based on section.
		let path = '';
		const params = {
			organisation_id: organisationId,
			status: 'eq.approved',
			is_published: 'eq.true',
			limit: defaultLimit,
			// eslint-disable-next-line object-shorthand
			offset: offset,
		};

		if ( orderBy ) {
			params.order = apiGetOrder( orderBy, order );
		}

		if ( section === 'projects' ) {
			if ( searchTerm !== '' ) {
				path = '/rpc/projects_by_organisation_search';
				params.search = searchTerm;
			} else {
				path = '/rpc/projects_by_organisation';
			}
		} else if ( section === 'software' ) {
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

		console.log( '🎹 path with params: ', apiGetUrl( path, params ) );

		// Get the data from the API.
		return new Promise( ( resolve, reject ) => {
			const req = $.ajax( {
				type: 'GET',
				url: apiGetUrl( path, params ),
				headers: { Prefer: 'count=exact' },
				// eslint-disable-next-line object-shorthand
				success: function ( response ) {
					const resultItems = response;
					console.log( '🎹 result items: ', resultItems );

					// Get the total count of results from `content-range` response header.
					let totalResults = false;
					const contentRange =
						req.getResponseHeader( 'content-range' );
					if ( contentRange ) {
						const total = contentRange.split( '/' );
						totalResults = total[ 1 ];
						itemsTotal = parseInt( totalResults );
					}

					resolve( resultItems );
				},
				// eslint-disable-next-line object-shorthand
				error: function ( jqXHR, textStatus, errorThrown ) {
					reject( errorThrown );
				},
			} );
		} );
	}

	// Get the filters from the API.
	async function fetchFilters() {
		const filters = {};
		const defaultArgs = {};
		const defaultParams = {
			organisation_id: organisationId,
		};

		if ( getSearchTerm() !== '' ) {
			defaultParams.search_filter = getSearchTerm();
		}

		const filtersDefault = {
			projects: {
				project_status: {
					title: 'Project status',
					identifier: 'project_status',
					args: {
						...defaultArgs,
						labels: { ...defaultFilterLabels.project_status },
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
						labeled_only: true,
						labels: { ...defaultFilterLabels.domain },
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
		const filterReqs = filtersDefault[ section ] || {};
		const filterValues = getFilterValues();
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
					url: apiGetUrl( data.path ),
					data: JSON.stringify( data.params ),
					dataType: 'json',
					contentType: 'application/json',
					// eslint-disable-next-line object-shorthand
					success: function ( response ) {
						filters[ filter ] = new Filter(
							data.title,
							data.identifier,
							response,
							data.args
						);
						resolve();
					},
					// eslint-disable-next-line object-shorthand
					error: function ( jqXHR, textStatus, errorThrown ) {
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

	/*
	Wrappers
	*/

	// Load items.
	async function loadItems() {
		try {
			// Fetch the items from the API.
			const offset = 0;
			items = await fetchItems(
				getSearchTerm(),
				getFilterValues(),
				getOrderBy(),
				getOrder(),
				offset
			);
			currentOffset = offset + items.length;

			// Display the results.
			displayResults( items, itemsTotal );
			// Re-attach infinite scroll event.
			enhanceResultsInfiniteScroll();
		} catch ( error ) {
			console.error( '🎹 Error fetching items: ', error );
		}
	}

	// Load more items.
	async function loadMoreItems() {
		if ( ! hasMoreItems() ) {
			return;
		}

		try {
			// Fetch more items from the API.
			const offset = currentOffset;
			const newItems = await fetchItems(
				getSearchTerm(),
				getFilterValues(),
				getOrderBy(),
				getOrder(),
				offset
			);
			items = items.concat( newItems );
			currentOffset = offset + newItems.length;

			// Append the results.
			const appendItems = true;
			displayResults( newItems, itemsTotal, appendItems );
		} catch ( error ) {
			console.error( '🎹 Error fetching more items: ', error );
		}
	}

	// Check if there are more items.
	function hasMoreItems() {
		return itemsTotal === 0 || items.length < itemsTotal;
	}

	// Load filters
	async function loadFilters() {
		fetchFilters()
			.then( ( filters ) => {
				displayUpdateFilterValues( filters );
				return filters;
			} )
			.catch( ( error ) => {
				console.error( '🎹 Error fetching filters: ', error );
			} );
	}

	/*
	Item functions
	*/

	function getItemId( item ) {
		return item.id;
	}

	function getItemSlugFromURL( url ) {
		return url.split( '/' ).pop();
	}

	function getItemUrl( item ) {
		return `https://research-software-directory.org/${ section }/${ item.slug }`;
	}

	function getItemImgUrl( item ) {
		if ( item.image_id ) {
			return `https://research-software-directory.org/image/rpc/get_image?uid=${ item.image_id }`;
		}

		return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; // Transparent 1x1 GIF
	}

	function getItemImageContain( item ) {
		if ( item.image_contain ) {
			return item.image_contain;
		}
		return false;
	}

	function getItemContributorsCount( item ) {
		return item.contributor_cnt || 0;
	}

	function getItemMentionsCount( item ) {
		return item.mention_cnt || 0;
	}

	function getItemImpactCount( item ) {
		return item.impact_cnt || 0;
	}

	function getItemOutputCount( item ) {
		return item.output_cnt || 0;
	}

	function getItemLabels( item ) {
		let html = '<ul class="rsd-results-item-labels">';
		$.each( item.keywords, function ( index, label ) {
			html += `<li class="label">${ label }</li>`;
		} );
		html += '</ul>';

		return html;
	}

	function getItemProps( item, props ) {
		let html = '<ul class="rsd-results-item-props">';
		$.each( props, function ( prop, value ) {
			html += `
				<li class="rsd-results-item-prop-${ prop.toLowerCase() }">
					<span aria-hidden="true" class="icon icon-${ prop.toLowerCase() }" title="${ prop }"></span>
					<span class="value">${ value }</span>
					<span class="prop">${ prop.toLowerCase() }</span>
				</li>
			`;
		} );
		html += '</ul>';

		return html;
	}

	function getItemsFromDOM() {
		const domItems = [];
		const validProps = [
			'contributor_cnt',
			'mention_cnt',
			'impact_cnt',
			'output_cnt',
		];

		$container.find( '.rsd-results-item' ).each( function () {
			const $item = $( this );
			const item = {
				id: $item.data( 'id' ),
				title: $item.find( 'h3' ).text(),
				description: $item.find( '.card-section p' ).text(),
				slug: getItemSlugFromURL( $item.find( 'h3 a' ).attr( 'href' ) ),
				labels: [],
			};
			$item.find( '.rsd-results-item-props li' ).each( function () {
				const prop =
					$( this )
						.attr( 'class' )
						.replace( 'rsd-results-item-prop-', '' )
						.trim() + '_cnt';
				const value = parseInt( $( this ).find( '.value' ).text() );
				if ( validProps.includes( prop ) ) {
					item[ prop ] = value;
				}
			} );

			domItems.push( item );
		} );
		return domItems;
	}

	function getItemsTotalFromDOM() {
		const count = $container
			.find( '.rsd-results-count' )
			.data( 'items-total' );
		return parseInt( count );
	}

	/*
	Filter functions
	*/

	function setCurrentFilters( filter, value ) {
		if ( ! Array.isArray( value ) ) {
			currentFilters[ filter ] = [ value ];
		} else {
			currentFilters[ filter ] = value;
		}
	}

	function clearCurrentFilters() {
		currentFilters = {};
	}

	/*
	Form functions
	*/

	function getSearchTerm() {
		return $container
			.find( '.rsd-search-input' )
			.val()
			.toLowerCase()
			.trim();
	}

	function getOrderBy() {
		const orderBy = $container
			.find( '.rsd-sortby-input' )
			.val()
			.toLowerCase()
			.trim();
		return orderBy || false;
	}

	function getOrder( orderBy = false ) {
		const sortDesc = [
			'mention_cnt',
			'contributor_cnt',
			'impact_cnt',
			'output_cnt',
			'updated_at',
			'date_end',
		];
		if ( orderBy && sortDesc.includes( orderBy ) ) {
			return 'desc';
		}

		return 'asc';
	}

	function getFilterValues() {
		const filters = {};
		$container.find( '.rsd-filters select' ).each( function () {
			const $filter = $( this );
			const identifier = $filter.data( 'filter' );
			const value = $filter.val();
			if ( value && value !== '' ) {
				filters[ identifier ] = [ value ];
			}
		} );
		return filters;
	}

	/*
	UI functions
	*/

	function hideSearchButton() {
		$container.find( '.rsd-search-bar input[type="submit"]' ).hide();
	}

	function showClearFiltersButton() {
		$container.find( '.rsd-results-clear-filters' ).show();
	}

	function hideClearFiltersButton() {
		$container.find( '.rsd-results-clear-filters' ).hide();
	}

	function hideFiltersSidebar() {
		$container.find( '.rsd-filter-sidebar' ).hide();
	}

	function toggleFiltersSidebar() {
		const $sidebar = $container.find( '.rsd-filter-sidebar' );
		const $button = $container.find( '.rsd-filter-button button' );
		$sidebar.toggle();

		if ( $sidebar.is( ':visible' ) ) {
			$button.addClass( 'active' );
		} else {
			$button.removeClass( 'active' );
		}
	}

	function hideShowMoreButton() {
		$container.find( '.rsd-results-show-more .button' ).hide();
	}

	/*
	Event handlers
	*/

	// Search field - attach search event and get new results from API.
	// (executing with a slight delay after entry changes, so that the search term is not sent with every character)
	let delayTimer;
	$container.find( '.rsd-search-input' ).on( 'input', function () {
		clearTimeout( delayTimer );
		const searchTerm = $( this ).val().toLowerCase();
		delayTimer = setTimeout( function () {
			console.log( '🎹 searchTerm: ', searchTerm );
			loadFilters();
			loadItems( searchTerm );
			if ( searchTerm.trim() === '' ) {
				hideClearFiltersButton();
			} else {
				showClearFiltersButton();
			}
		}, 500 );
	} );

	// Attach set filters event and get new results from API.
	$container.find( '.rsd-filters' ).on( 'change', 'select', function () {
		setCurrentFilters( $( this ).data( 'filter' ), $( this ).val() );
		loadFilters();
		loadItems();
		if ( Object.keys( getFilterValues() ).length === 0 ) {
			hideClearFiltersButton();
		}
	} );

	// Attach click event to 'Clear filters' button and get new results from API.
	$container.find( '.rsd-results-clear-filters' ).on( 'click', clearFilters );

	function clearFilters() {
		$container.find( '.rsd-search-input' ).val( '' );
		$container.find( '.rsd-filters select' ).val( '' );
		clearCurrentFilters();
		loadFilters();
		loadItems();
		hideClearFiltersButton();
	}

	// Attach click event to filters toggle button.
	$container
		.find( '.rsd-filter-button button' )
		.on( 'click', toggleFiltersSidebar );

	// Enhance filters sidebar.
	function enhanceFiltersSidebar() {
		const $sidebar = $container.find( '.rsd-filter-sidebar' );

		// Add close button to filters sidebar.
		$sidebar.prepend( `
			<button class="close-button" aria-label="Close alert" type="button"
				<span aria-hidden="true">&times;</span>
			</button>
		` );

		$sidebar.find( '.close-button' ).on( 'click', toggleFiltersSidebar );
	}

	// Attach change event to sort by select.
	$container.find( '.rsd-sortby-input' ).on( 'change', function () {
		loadItems();
	} );

	// Attach infinite scroll event that automatically loads more results (if any).
	function enhanceResultsInfiniteScroll() {
		if ( IntersectionObserver === undefined ) {
			return false;
		}

		const targetElement = $( '.rsd-results-show-more' )[ 0 ];

		if ( scrollObserver ) {
			// Continue observing the target element.
			if ( hasMoreItems() ) {
				scrollObserver.observe( targetElement );
			}
		} else {
			// Start observing the target element.
			scrollObserver = new IntersectionObserver(
				async ( entries, observer ) => {
					if ( entries[ 0 ].isIntersecting ) {
						if ( hasMoreItems() ) {
							loadMoreItems();
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

	if ( enhanceResultsInfiniteScroll() ) {
		hideShowMoreButton();
	}

	// Attach click event to 'Show more' button.
	$container
		.find( '.rsd-results-show-more .button' )
		.on( 'click', loadMoreItems );

	// Attach back to top button scroll handler and execute on page load.
	$( window ).on( 'scroll', enhanceBackToTopButton );
	enhanceBackToTopButton();

	function enhanceBackToTopButton() {
		const offset = $container.offset().top || 100;
		const $button = $container.find( '.rsd-back-to-top' );

		if ( $( window ).scrollTop() > offset ) {
			$button.addClass( 'visible' );
		} else {
			$button.removeClass( 'visible' );
		}
	}

	$container.find( '.rsd-back-to-top a' ).on( 'click', function () {
		$( 'html, body' ).animate( { scrollTop: 0 }, 400 );
		return false;
	} );

	/*
	Display functions
	*/

	// Update the result count.
	function displaySetResultsTotalCount( count ) {
		$container
			.find( '.rsd-results-count' )
			.text( `${ count } items found` );
	}

	// Update filters.
	function displayUpdateFilterValues( filters ) {
		$.each( filters, function ( identifier, filter ) {
			const $filter = $container.find(
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
					currentFilters[ identifier ] &&
					currentFilters[ identifier ].includes( value )
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
	function displayResults(
		displayItems = [],
		totalCount = null,
		appendItems = false
	) {
		// Get the results container.
		const $itemsContainer = $container.find( '.rsd-results-items' );

		// Empty results container if no items are provided.
		if (
			! displayItems ||
			! Array.isArray( displayItems ) ||
			displayItems.length === 0
		) {
			$itemsContainer.empty();
			displaySetResultsTotalCount( 0 );
			return false;
		}

		// Update result count.
		displaySetResultsTotalCount( totalCount || '-' );

		// Clear the results container.
		if ( ! appendItems ) {
			$itemsContainer.empty();
		}

		$.each( displayItems, function ( index, item ) {
			let title, description, props;
			if ( 'projects' === section ) {
				title = item.title;
				description = item.subtitle || '';
				props = {
					Impact: getItemImpactCount( item ),
					Output: getItemOutputCount( item ),
				};
			} else {
				title = item.brand_name;
				description = item.short_statement || '';
				props = {
					Contributors: getItemContributorsCount( item ),
					Mentions: getItemMentionsCount( item ),
				};
			}

			let imageContainAttr = '';
			if ( getItemImageContain( item ) ) {
				imageContainAttr = ' class="contain"';
			}

			// prettier-ignore
			$itemsContainer.append( `
				<div class="rsd-results-item column card in-viewport" data-id="${ getItemId( item ) }">
					<div class="card-image">
						<a href="${ getItemUrl( item ) }" target="_blank" rel="external"><img src="${ getItemImgUrl( item ) }"
							 alt="" title="${ title }" aria-label="${ title }"${ imageContainAttr }></a>
					</div>
					<div class="card-section">
						<h3><a href="${ getItemUrl( item ) }" target="_blank" rel="external">${ title }</a></h3>
						<p>${ description }</p>
					</div>
					<div class="card-footer">
						<div class="rsd-results-item-specs">
							${ getItemLabels( item ) }
						</div>
						<div class="rsd-results-item-props">
							${ getItemProps( item, props ) }
						</div>
					</div>
				</div>
			` );
		} );

		return true;
	}
} );
