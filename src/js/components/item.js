/**
 * Item component
 */

import { getSlugFromURL } from '../helpers/utils';
import Controller from '../services/controller';

export default class Item {
	constructor( data = {} ) {
		// Default properties from the API.
		this.id = data.id || false;
		this.slug = data.slug || getSlugFromURL( data.url );
		this.title = data.title || '';
		this.subtitle = data.subtitle || '';
		this.description = data.description || '';
		this.brand_name = data.brand_name || '';
		this.short_statement = data.short_statement || '';
		this.date_start = data.date_start || '';
		this.date_end = data.date_end || '';
		this.updated_at = data.updated_at || '';
		this.is_published = data.is_published || false;
		this.image_contain = data.image_contain || false;
		this.image_id = data.image_id || false;
		this.is_featured = data.is_featured || false;
		this.status = data.status || '';
		this.keywords = data.keywords || [];
		this.prog_lang = data.prog_lang || [];
		this.licenses = data.licenses || [];
		this.research_domain = data.research_domain || '';
		this.participating_organisations =
			data.participating_organisations || [];
		this.impact_cnt = data.impact_cnt || 0;
		this.output_cnt = data.output_cnt || 0;
		this.contributor_cnt = data.contributor_cnt || 0;
		this.mention_cnt = data.mention_cnt || 0;
		// Custom properties
		this.labels = data.labels || [];
	}

	getId() {
		return this.id;
	}

	getUrl() {
		return `https://research-software-directory.org/${ Controller.section }/${ this.slug }`;
	}

	getImgUrl() {
		if ( this.image_id ) {
			return `https://research-software-directory.org/image/rpc/get_image?uid=${ this.image_id }`;
		}

		return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; // Transparent 1x1 GIF
	}

	getImageContain() {
		if ( this.image_contain ) {
			return this.image_contain;
		}
		return false;
	}

	getContributorsCount() {
		return this.contributor_cnt || 0;
	}

	getMentionsCount() {
		return this.mention_cnt || 0;
	}

	getImpactCount() {
		return this.impact_cnt || 0;
	}

	getOutputCount() {
		return this.output_cnt || 0;
	}

	getLastUpdated( format = false ) {
		const date = new Date( this.updated_at );

		if ( ! isNaN( date.getTime() ) ) {
			if ( format && 'c' === format ) {
				// Return ISO 8601 formatted date.
				return date.toISOString();
			}

			// Return UNIX timestamp in seconds.
			return Math.floor( date.getTime() / 1000 );
		}

		return this.updated_at || '';
	}

	getDate(
		dateStr,
		format = { year: 'numeric', month: '2-digit', day: '2-digit' }
	) {
		if ( ! dateStr ) return '';

		const date = new Date( dateStr );
		const formatter = new Intl.DateTimeFormat( 'en-UK', format );
		return formatter.format( date );
	}

	getDateStart(
		format = { year: 'numeric', month: '2-digit', day: '2-digit' }
	) {
		return this.getDate( this.date_start, format );
	}

	getDateEnd(
		format = { year: 'numeric', month: '2-digit', day: '2-digit' }
	) {
		return this.getDate( this.date_end, format );
	}

	getProgressPercentage() {
		const dateStart = new Date( this.date_start );
		const dateEnd = new Date( this.date_end );
		const now = new Date();

		if ( ! isNaN( dateStart.getTime() ) && ! isNaN( dateEnd.getTime() ) ) {
			if ( now < dateStart ) {
				return 0;
			} else if ( now > dateEnd ) {
				return 100;
			}

			const total = ( dateEnd - dateStart ) / ( 1000 * 60 * 60 * 24 );
			const elapsed = ( now - dateStart ) / ( 1000 * 60 * 60 * 24 );
			return Math.round( ( elapsed / total ) * 100 );
		}

		return 0;
	}

	getLabels() {
		let html = '<ul class="rsd-results-item-labels">';
		$.each( this.keywords, function ( index, label ) {
			html += `<li class="label">${ label }</li>`;
		} );
		html += '</ul>';

		return html;
	}

	getProps( props ) {
		let html = '<ul class="rsd-results-item-props">';
		const self = this;
		const progressDateFormat = {
			year: 'numeric',
			month: 'short',
		};

		$.each( props, function ( prop, value ) {
			if ( 'Progress' === prop ) {
				html += `
					<li class="rsd-results-item-prop-progress" data-progress-percentage="${ value }">
						<span class="value date-start">${ self.getDateStart( progressDateFormat ) }</span> -
						<span class="value date-end">${ self.getDateEnd( progressDateFormat ) }</span>
						<div class="progress-bar" role="progressbar" tabindex="0" aria-valuenow="${ value }" aria-valuemin="0" aria-valuemax="100">
							<div class="progress-meter" style="width: ${ value }%;"></div>
						</div>
					</li>
					`;
			} else {
				html += `
					<li class="rsd-results-item-prop-${ prop.toLowerCase() }">
						<span aria-hidden="true" class="icon icon-${ prop.toLowerCase() }" title="${ prop }"></span>
						<span class="value">${ value }</span>
						<span class="prop">${ prop.toLowerCase() }</span>
					</li>
					`;
			}
		} );
		html += '</ul>';

		return html;
	}
}
