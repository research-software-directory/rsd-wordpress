/**
 * RSD WordPress plugin JavaScript
 *
 * @package RSD_WP
 * @since 0.4.0
 * @license Apache-2.0
 * @link https://research-software-directory.org
 */

'use strict';

jQuery(function($) {

	/*
	Variables
	*/

	// Control variables
	let items = [];
	let itemsTotal = 0;
	let currentOffset = 0;
	// API
	const apiEndpoint = 'https://research-software-directory.org/api';
	const apiVersion = 'v1';
	// Default parameters
	const defaultLimit = 48;

	// Get container element and section.
	const $container = $('#rsd-wordpress');
	const section = $container.data('section');
	const organisation_id = $container.data('organisation_id');

	// Add a class to the body when the page is loaded.
	$('body').addClass('rsd-wordpress');
	// Hide search button, since we're using the input event to trigger a search.
	hideSearchButton();
	// Hide filters sidebar by default.
	hideFiltersSidebar();
	enhanceFiltersSidebar();
	// Hide the 'Show more' button (for now)
	hideShowMoreButton();


	/*
	API functions
	*/

	// Get the API URL.
	function apiGetUrl(path, params = {}) {
		if (params && typeof params === 'object' && Object.keys(params).length !== 0) {
			path = path + '?' + $.param(params);
		}
		return apiEndpoint + '/' + apiVersion + '/' + path.replace(/^\/+/, '');
	}

	// Get the API order string.
	function apiGetOrder(orderBy, order) {
		let nullsLast = ['mention_cnt', 'contributor_cnt', 'impact_cnt', 'output_cnt', 'date_start', 'date_end'];
		if (nullsLast.includes(orderBy)) {
			return `${orderBy.toLowerCase()}.${order.toLowerCase()}.nullslast`;
		} else {
			return `${orderBy.toLowerCase()}.${order.toLowerCase()}`;
		}
	}


	/*
	Controller functions
	*/

	// Get the result items from the API.
	async function fetchItems(searchTerm = false, filters = false, orderBy = false, order = false, offset = 0) {
		// Get the search term and filter values.
		searchTerm = searchTerm ? searchTerm.toLowerCase().trim() : getSearchTerm();
		filters = filters ? filters : getFilterValues();
		orderBy = orderBy ? orderBy : getOrderBy();
		order = order ? order : getOrder(orderBy);
		offset = offset ? offset : 0;

		// Hide the 'Clear filters' button if no search term or filters are set.
		if (searchTerm || (filters && Object.keys(filters).length !== 0)) {
			showClearFiltersButton();
		}

		// Build the API URL based on section.
		let path = '';
		let params = {
			organisation_id: organisation_id,
			status: 'eq.approved',
			is_published: 'eq.true',
			limit: defaultLimit,
			offset: offset,
		};

		if (orderBy) {
			params.order = apiGetOrder(orderBy, order);
		}

		if (section === 'projects') {
			if (searchTerm != '') {
				path = '/rpc/projects_by_organisation_search';
				params.search = searchTerm;
			} else {
				path = '/rpc/projects_by_organisation';
			}
		} else {
			if (searchTerm != '') {
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

		Object.keys(filterParamMap).forEach(filterKey => {
			const paramKey = filterParamMap[filterKey];
			const filterValue = filters[filterKey];
			if (paramKey === 'project_status') {
				if (filterValue && filterValue.length > 0) {
					params[paramKey] = `eq.${filterValue.flat().map(value => value.toLowerCase())}`;
				}
			} else {
				if (filterValue && filterValue.length > 0) {
					params[paramKey] = 'cs.{' + filterValue.map(value => `"${value}"`).join(',') + '}';
				}
			}
		});

		console.log('🎹 path with params: ', apiGetUrl(path, params));

		// Get the data from the API.
		return new Promise((resolve, reject) => {
			let url = apiGetUrl(path, params);
			let req = $.ajax({
				type: 'GET',
				url: url,
				headers: { 'Prefer': 'count=exact' },
				success: function(response) {
					let resultItems = response;
					console.log('🎹 result items: ', resultItems);

					// Get the total count of results from `content-range` response header.
					let totalResults = false;
					let contentRange = req.getResponseHeader('content-range');
					if (contentRange) {
						let total = contentRange.split('/');
						totalResults = total[1];
						itemsTotal = parseInt(totalResults);
					}

					resolve(resultItems);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					reject(errorThrown);
				},
			});
		});
	}

	function fetchFilters() {
		let filters = {};

		let defaultParams = {
			'organisation_id': organisation_id,
		};

		let filtersDefault = {
			'projects': {
				'project_status': {
					title: 'Project status',
					identifier: 'project_status',
					path: '/rpc/org_project_status_filter?order=project_status',
					params: { ...defaultParams },
					labels: {
						'upcoming'    : 'Upcoming',
						'in_progress' : 'In progress',
						'finished'    : 'Finished',
						'unknown'     : 'Unknown'
					}
				},
				'keyword': {
					title: 'Keywords',
					identifier: 'keyword',
					path: '/rpc/org_project_keywords_filter?order=keyword',
					params: { ...defaultParams }
				},
				'domain': {
					title: 'Research domains',
					identifier: 'domain',
					path: '/rpc/org_project_domains_filter?order=domain',
					params: { ...defaultParams }
				},
				'partner': {
					title: 'Partners',
					identifier: 'organisation',
					path: '/rpc/org_project_participating_organisations_filter?order=organisation',
					params: { ...defaultParams }
				}
			},
			'software': {
				'keyword': {
					title: 'Keywords',
					identifier: 'keyword',
					path: '/rpc/org_software_keywords_filter?order=keyword',
					params: { ...defaultParams }
				},
				'prog_language': {
					title: 'Programming Languages',
					identifier: 'prog_language',
					path: '/rpc/org_software_languages_filter?order=prog_language',
					params: { ...defaultParams }
				},
				'license': {
					title: 'Licenses',
					identifier: 'license',
					path: '/rpc/org_software_licenses_filter?order=license',
					params: { ...defaultParams }
				}
			}
		}

		// Build filters object for the current section, narrowed down by filter values.
		let filterReqs = filtersDefault[section] || {};
		let filterValues = getFilterValues();

		$.each(filterValues, function(filter, data) {
			// Keywords filter
			if (filter === 'keyword' && filterValues.keyword && filterValues.keyword.length > 0) {
				filterReqs.keyword.params.keyword_filter = data;
			}
			// Program languages filter
			if (filter === 'prog_language' && filterValues.prog_language && filterValues.prog_language.length > 0) {
				filterReqs.prog_language.params.prog_lang_filter = data;
			}
			// Licenses filter
			if (filter === 'license' && filterValues.license && filterValues.license.length > 0) {
				filterReqs.license.params.license_filter = data;
			}
		});

		// Get filter data from the API for each filter.
		$.each(filterReqs, function(filter, data) {
			$.ajax({
				type: 'POST',
				url: apiGetUrl(data.path),
				data: JSON.stringify(data.params),
				dataType: 'json',
				contentType: 'application/json',
				success: function(response) {
					filters[filter] = {
						title: data.title,
						identifier: data.identifier,
						labels: data.labels || {},
						values: response,
					};
				},
			});
		});

		return filters;
	}


	/*
	Wrappers
	*/

	// Load items.
	async function loadItems() {
		try {
			// Fetch the items from the API.
			let offset = 0;
			items = await fetchItems(getSearchTerm(), getFilterValues(), getOrderBy(), getOrder(), offset);
			currentOffset = offset;

			// Display the results.
			displayResults(items, itemsTotal);
			// Re-attach infinite scroll event.
			enhanceResultsInfiniteScroll();
		} catch (error) {
			console.error('🎹 Error fetching items: ', error);
		}
	}

	// Load more items.
	async function loadMoreItems() {
		if (!hasMoreItems()) {
			return;
		}

		try {
			// Fetch more items from the API.
			let offset = currentOffset + defaultLimit;
			let newItems = await fetchItems(getSearchTerm(), getFilterValues(), getOrderBy(), getOrder(), offset);
			items = items.concat(newItems);
			currentOffset = offset;

			// Append the results.
			let appendItems = true;
			displayResults(newItems, itemsTotal, appendItems);
		} catch (error) {
			console.error('🎹 Error fetching more items: ', error);
		}
	}

	// Check if there are more items.
	function hasMoreItems() {
		return itemsTotal === 0 || items.length < itemsTotal;
	}

	// Load filters
	async function loadFilters() {
		let filters = await fetchFilters();
		return filters;
	}


	/*
	Item functions
	*/

	function getItemId(item) {
		return item.id;
	}

	function getItemUrl(item) {
		return `https://research-software-directory.org/${section}/${item.slug}`;
	}

	function getItemImgUrl(item) {
		if (item.image_id) {
			return `https://research-software-directory.org/image/rpc/get_image?uid=${item.image_id}`;
		} else {
			return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; // Transparent 1x1 GIF
		}
	}

	function getItemImageContain(item) {
		if (item.image_contain) {
			return item.image_contain;
		}
		return false;
	}

	function getItemContributorsCount(item) {
		return item.contributor_cnt || 0;
	}

	function getItemMentionsCount(item) {
		return item.mention_cnt || 0;
	}

	function getItemImpactCount(item) {
		return item.impact_cnt || 0;
	}

	function getItemOutputCount(item) {
		return item.output_cnt || 0;
	}

	function getItemLabels(item) {
		let html = '<ul class="rsd-results-item-labels">';
		$.each(item.keywords, function(index, label) {
			html += `<li class="label">${label}</li>`;
		});
		html += '</ul>';

		return html;
	}

	function getItemProps(item, props) {
		let html = '<ul class="rsd-results-item-props">';
		$.each(props, function(prop, value) {
			html += `
				<li class="rsd-results-item-prop-${prop.toLowerCase()}">
					<span aria-hidden="true" class="icon icon-${prop.toLowerCase()}" title="${prop}"></span>
					<span class="value">${value}</span>
					<span class="prop">${prop.toLowerCase()}</span>
				</li>
			`;
		});
		html += '</ul>';

		return html;
	}


	/*
	Form functions
	*/

	function getSearchTerm() {
		return $('#rsd-search').val().toLowerCase().trim();
	}

	function getOrderBy() {
		let orderBy = $container.find('#rsd-sortby').val().toLowerCase().trim();
		return orderBy || false;
	}

	function getOrder(orderBy = false) {
		let sortDesc = ['mention_cnt', 'contributor_cnt', 'impact_cnt', 'output_cnt', 'updated_at', 'date_end'];
		if (orderBy && sortDesc.includes(orderBy)) {
			return 'desc';
		} else {
			return 'asc';
		}
	}

	function getFilterValues() {
		let filters = {};
		$container.find('.rsd-filters select').each(function() {
			let $filter = $(this);
			let filterIdentifier = $filter.data('filter');
			let filterValue = $filter.val();
			if (filterValue != '') {
				filters[filterIdentifier] = [filterValue];
			}
		});
		return filters;
	}


	/*
	UI functions
	*/

	function hideSearchButton() {
		$('#rsd-wordpress .rsd-search-bar input[type="submit"]').hide();
	}

	function showClearFiltersButton() {
		$('#rsd-wordpress .rsd-results-clear-filters').show();
	}

	function hideClearFiltersButton() {
		$('#rsd-wordpress .rsd-results-clear-filters').hide();
	}

	function hideFiltersSidebar() {
		$container.find('.rsd-filter-sidebar').hide();
	}

	function toggleFiltersSidebar() {
		let $sidebar = $container.find('.rsd-filter-sidebar');
		let $button = $container.find('.rsd-filter-button button');
		$sidebar.toggle();

		if ($sidebar.is(':visible')) {
			$button.addClass('active');
		} else {
			$button.removeClass('active');
		}
	}

	function showShowMoreButton() {
		$container.find('.rsd-results-show-more .button').show();
	}

	function hideShowMoreButton() {
		$container.find('.rsd-results-show-more .button').hide();
	}


	/*
	Event handlers
	*/

	// Search field - attach search event and get new results from API.
	// (executing with a slight delay after entry changes, so that the search term is not sent with every character)
	var delayTimer;
	$container.find('#rsd-search').on('input', function() {
		clearTimeout(delayTimer);
		var searchTerm = $(this).val().toLowerCase();
		delayTimer = setTimeout(function() {
			console.log('🎹 searchTerm: ', searchTerm);
			fetchFilters();
			fetchResults(searchTerm);
			showClearFiltersButton();
		}, 500);
	});

	// Attach set filters event and get new results from API.
	$container.find('.rsd-filters').on('change', 'select', function() {
		fetchFilters();
		fetchResults();
	});

	// Attach click event to 'Clear filters' button and get new results from API.
	$container.find('.rsd-results-clear-filters').on('click', clearFilters);

	function clearFilters() {
		$container.find('#rsd-search').val('');
		$container.find('.rsd-filters select').val('');
		fetchFilters();
		fetchResults();
		hideClearFiltersButton();
	}

	// Attach click event to filters toggle button.
	$container.find('.rsd-filter-button').on('click', toggleFiltersSidebar);

	// Enhance filters sidebar.
	function enhanceFiltersSidebar(filters) {
		let $sidebar = $container.find('.rsd-filter-sidebar');

		// Add close button to filters sidebar.
		$sidebar.prepend(
			`
			<button class="close-button" aria-label="Close alert" type="button"
				<span aria-hidden="true">&times;</span>
			</button>
			`
		);

		$sidebar.find('.close-button').on('click', toggleFiltersSidebar);
	}

	// Attach change event to sort by select.
	$container.find('#rsd-sortby').on('change', function() {
		fetchResults();
	});


	/*
	Display functions
	*/

	// Update the result count.
	function setResultsTotalCount(count) {
		$container.find('.rsd-results-count').text(`${count} items found`);
	}

	// Display the results.
	function displayResults(items = [], totalCount = null, appendItems = false) {
		// Get the results container.
		let $itemsContainer = $container.find('.rsd-results-items');

		// Empty results container if no items are provided.
		if (!items || !Array.isArray(items) || items.length === 0) {
			$container.find('.rsd-results-items').empty();
			setResultsTotalCount('-');
			return false;
		}

		// Update result count.
		setResultsTotalCount(totalCount || '-');

		// Clear the results container.
		if (!appendItems) {
			$itemsContainer.empty();
		}

		$.each(items, function(index, item) {
			let title, description, props;
			if ('projects' === section) {
				title = item.title;
				description = item.subtitle;
				props = {
					'Impact': getItemImpactCount(item),
					'Output': getItemOutputCount(item),
				}
			} else {
				title = item.brand_name;
				description = item.short_statement;
				props = {
					'Contributors': getItemContributorsCount(item),
					'Mentions': getItemMentionsCount(item),
				}
			}

			let imageContainAttr = '';
			if (getItemImageContain(item)) {
				imageContainAttr = ` class="contain"`;
			}

			$itemsContainer.append(
				`
				<div class="rsd-results-item column card in-viewport" data-id="${getItemId(item)}">
					<div class="card-image">
						<a href="${getItemUrl(item)}" target="_blank" rel="external"><img src="${getItemImgUrl(item)}"
							 alt="" title="${title}" aria-label="${title}"${imageContainAttr}></a>
					</div>
					<div class="card-section">
						<h3><a href="${getItemUrl(item)}" target="_blank" rel="external">${title}</a></h3>
						<p>${description}</p>
					</div>
					<div class="card-footer">
						<div class="rsd-results-item-specs">
							${getItemLabels(item)}
						</div>
						<div class="rsd-results-item-props">
							${getItemProps(item, props)}
						</div>
					</div>
				</div>
				`
			);
		});

		return true;
	}

});
