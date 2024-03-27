/**
 * RSD WordPress plugin JavaScript
 *
 * @package RSD_WP
 * @since 2.0.0
 * @license Apache-2.0
 * @link https://research-software-directory.org
 */

'use strict';

jQuery(function($) {

	/*
	Variables
	*/

	let data = [];
	let filteredData = [];
	// API
	let apiEndpoint = 'https://research-software-directory.org/api';
	let apiVersion = 'v1';
	// Default parameters
	let defaultLimit = 48;

	// Add a class to the body when the page is loaded.
	$('body').addClass('rsd-wordpress');
	// Hide search button, since we're using the input event to trigger a search.
	hideSearchButton();

	// Get container element and section.
	let $container = $('#rsd-wordpress');
	let section = $container.data('section');
	let organisation_id = $container.data('organisation_id');


	/*
	API functions
	*/

	// Get the API URL.
	function apiGetUrl(path, params = {}) {
		if (params && typeof params === 'object' && Object.keys(params).length !== 0) {
			path = path + '?' + $.param(params);
		}
		return apiEndpoint + '/' + apiVersion + '/' + path.trimStart('/');
	}


	/*
	Controller functions
	*/

	// Get the results from the API.
	function fetchResults(searchTerm = '', filters = {}) {
		searchTerm = searchTerm.toLowerCase().trim() || '';

		// Build the API URL based on section.
		let path = '';
		let params = {
			organisation_id: organisation_id,
			status: 'eq.approved',
			is_published: 'eq.true',
			limit: defaultLimit,
			order: 'slug.asc'
		};

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

		// Add filter values to API URL params.
		if (filters && typeof filters === 'object' && Object.keys(filters).length !== 0) {
			if (filters.keyword && filters.keyword.length > 0) {
				params.keywords = 'cs.{' + filters.keyword.map(value => `"${value}"`).join(',') + '}';
			}
			if (filters.prog_language && filters.prog_language.length > 0) {
				params.languages = 'cs.{' + filters.prog_language.map(value => `"${value}"`).join(',') + '}';
			}
			if (filters.license && filters.license.length > 0) {
				params.licenses = 'cs.{' + filters.license.map(value => `"${value}"`).join(',') + '}';
			}
		}

		console.log(apiGetUrl(path, params));
		let url = apiGetUrl(path, params);

		// Get the data from the API.
		let req = $.ajax({
			type: 'GET',
			url: url,
			headers: { 'Prefer': 'count=exact' },
			success: function(response) {
				data = response;
				filteredData = response;
				console.log('🎹 data: ', data);

				// Get the total count of results from `content-range` response header.
				let totalResults = false;
				let contentRange = req.getResponseHeader('content-range');
				if (contentRange) {
					let total = contentRange.split('/');
					totalResults = total[1];
				}

				// Display the results.
				displayResults(filteredData, totalResults);
			},
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
					path: '/rpc/org_project_status_filter',
					params: $.extend({
						'order': 'project_status',
					}, defaultParams ),
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
					path: '/rpc/org_project_keywords_filter',
					params: $.extend({
						'order': 'keyword',
					}, defaultParams )
				},
				'domain': {
					title: 'Research domains',
					identifier: 'domain',
					path: '/rpc/org_research_domains_filter',
					params: $.extend({
						'order': 'domain',
					}, defaultParams )
				},
				'partner': {
					title: 'Partners',
					identifier: 'partner',
					path: '/rpc/org_project_participating_organisations_filter',
					params: $.extend({
						'order': 'organisation',
					}, defaultParams )
				}
			},
			'software': {
				'keyword': {
					title: 'Keywords',
					identifier: 'keyword',
					path: '/rpc/org_software_keywords_filter',
					params: $.extend({
						'order': 'keyword',
					}, defaultParams )
				},
				'prog_language': {
					title: 'Programming Languages',
					identifier: 'prog_language',
					path: '/rpc/org_software_languages_filter',
					params: $.extend({
						'order': 'prog_language',
					}, defaultParams )
				},
				'license': {
					title: 'Licenses',
					identifier: 'license',
					path: '/rpc/org_software_licenses_filter',
					params: $.extend({
						'order': 'license',
					}, defaultParams )
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
				type: 'GET',
				url: apiGetUrl(data.path, data.params),
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

		console.log('🎹 filters: ', filters);

		return filters;
	}


	/*
	Item functions
	*/

	function getItemUrl(item) {
		return `https://research-software-directory.org/${section}/${item.slug}`;
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

	function getItemOutputsCount(item) {
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
			html += `	<li class="rsd-results-item-prop prop-${prop.toLowerCase()}"><span class="prop">${prop}:</span> ${value}</li>`;
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

	function showSearchButton() {
		$('#rsd-wordpress .rsd-search-bar input[type="submit"]').show();
	}

	function hideSearchButton() {
		$('#rsd-wordpress .rsd-search-bar input[type="submit"]').hide();
	}

	function showClearFiltersButton() {
		$('#rsd-wordpress .rsd-results-clear-filters').show();
	}

	function hideClearFiltersButton() {
		$('#rsd-wordpress .rsd-results-clear-filters').hide();
	}


	/*
	Event handlers
	*/

	// Search field - attach search event and get new results from API.
	// (executing with a slight delay after entry changes, so that the search term is not sent with every character)
	var delayTimer;
	$('#rsd-search').on('input', function() {
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
		let $filter = $(this);
		let filterIdentifier = $filter.data('filter');
		let filterValue = $filter.val();
		// Update results.
		fetchFilters();
		fetchResults();
	});

	// Attach click event to 'Clear filters' button and get new results from API.
	$('#rsd-wordpress .rsd-results-clear-filters').on('click', function() {
		$('#rsd-search').val('');
		fetchFilters();
		fetchResults();
		hideClearFiltersButton();
	});


	/*
	Display functions
	*/

	// Update the result count.
	function setResultsTotalCount(count) {
		$('#rsd-wordpress .rsd-results-count').text(`${count} items found`);
	}

	// Display the results.
	function displayResults(items = [], totalCount = null) {
		// Update result count.
		setResultsTotalCount(totalCount || '-');
		// Display the results.
		let $itemsContainer = $container.find('.rsd-results-items');
		$itemsContainer.empty();
		$.each(items, function(index, item) {
			let title, props;
			if ('projects' === section) {
				title = item.name;
				props = {
					'Contributors': getItemContributorsCount(item),
					'Mentions': getItemMentionsCount(item),
				}
			} else {
				title = item.brand_name;
				props = {
					'Contributors': getItemContributorsCount(item),
					'Mentions': getItemMentionsCount(item),
				}
			}

			$itemsContainer.append(
				`
				<div class="rsd-results-item card">
					<div class="card-section">
						<h3><a href="${getItemUrl(item)}" target="_blank" rel="external">${title}</a></h3>
						<p>${item.short_statement}</p>
					</div>
					<div class="card-footer">
						<div class="rsd-results-item-specs">
							<p class="rsd-result-item-domain"><strong class="label">Example</strong></p>
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
	}
});