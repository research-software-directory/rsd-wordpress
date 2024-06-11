/**
 * API class to handle all the API calls
 */

let instance = null;

class API {
	constructor() {
		if ( ! instance ) {
			this.endpoint = 'https://research-software-directory.org/api';
			this.version = 'v1';

			instance = this;
		}

		return instance;
	}

	// Get the API URL.
	getUrl( path, params = {} ) {
		if (
			params &&
			typeof params === 'object' &&
			Object.keys( params ).length !== 0
		) {
			path = path + '?' + $.param( params );
		}
		return (
			this.endpoint +
			'/' +
			this.version +
			'/' +
			path.replace( /^\/+/, '' )
		);
	}

	// Get the API order string.
	getOrder( orderBy, order ) {
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
}

export default new API();
