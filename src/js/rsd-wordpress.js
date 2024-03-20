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

	// Variables
	let data = [];
	let filteredData = [];
	// API
	let apiEndpoint = 'https://research-software-directory.org/api';
	let apiVersion = 'v1';
	// Default parameters
	let defaultLimit = 48;
	let defaultView = 'card';
	// TODO: set organisation_id server-side
	let organisation_id = '35c17f17-6b5f-4385-aa8b-6b1d33a10157';

	// Add a class to the body when the page is loaded.
	$('body').addClass('rsd-wordpress');
	// Hide search button, since we're using the input event to trigger a search.
	hideSearchButton();

	// Get container element and section.
	let $container = $('#rsd-wordpress');
	let section = $container.data('section');

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

		// Use filters, if any were set.
		if (filters && typeof filters === 'object' && Object.keys(filters).length !== 0) {
			// Keywords filter
			if (filters.keywords && filters.keywords.length > 0) {
				params.keywords = 'cs.{' + filters.keywords.map(value => `"${value}"`).join(',') + '}';
			}
			// Program languages filter
			if (filters.languages && filters.languages.length > 0) {
				params.languages = 'cs.{' + filters.languages.map(value => `"${value}"`).join(',') + '}';
			}
			// Licenses filter
			if (filters.licenses && filters.licenses.length > 0) {
				params.licenses = 'cs.{' + filters.licenses.map(value => `"${value}"`).join(',') + '}';
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
				'keywords': {
					title: 'Keywords',
					identifier: 'keyword',
					path: '/rpc/org_project_keywords_filter',
					params: $.extend({
						'order': 'keyword',
					}, defaultParams )
				},
				'research_domains': {
					title: 'Research domains',
					identifier: 'domain',
					path: '/rpc/org_research_domains_filter',
					params: $.extend({
						'order': 'domain',
					}, defaultParams )
				},
				'partners': {
					title: 'Partners',
					identifier: 'partner',
					path: '/rpc/org_project_participating_organisations_filter',
					params: $.extend({
						'order': 'organisation',
					}, defaultParams )
				}
			},
			'software': {
				'keywords': {
					title: 'Keywords',
					identifier: 'keyword',
					path: '/rpc/org_software_keywords_filter',
					params: $.extend({
						'order': 'keyword',
					}, defaultParams )
				},
				'programming_languages': {
					title: 'Programming Languages',
					identifier: 'prog_language',
					path: '/rpc/org_software_languages_filter',
					params: $.extend({
						'order': 'prog_language',
					}, defaultParams )
				},
				'licenses': {
					title: 'Licenses',
					identifier: 'license',
					path: '/rpc/org_software_licenses_filter',
					params: $.extend({
						'order': 'license',
					}, defaultParams )
				}
			}
		}

		// Get filter data from the API for each filter.
		$.each(filtersDefault[section], function(filter, data) {
			$.ajax({
				type: 'GET',
				url: apiGetUrl(data.path, data.params),
				success: function(response) {
					filters[filter].data = response;
					console.log('🎹 filters[filter].data: ', filters[filter].data);
				},
			});
		});
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

	// Attach search event to search field (executing with a slight delay after its entry was changed) and get new results from API.
	var delayTimer;
	$('#rsd-search').on('input', function() {
		clearTimeout(delayTimer);
		var searchTerm = $(this).val().toLowerCase();
		delayTimer = setTimeout(function() {
			console.log('🎹 searchTerm: ', searchTerm);
			fetchResults(searchTerm);
			showClearFiltersButton();
		}, 500);
	});

	// Attach click event to 'Clear filters' button and get new results from API.
	$('#rsd-wordpress .rsd-results-clear-filters').on('click', function() {
		$('#rsd-search').val('');
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
