/**
 * DOM class
 *
 * @class DOM
 */

import { getSlugFromURL } from './utils';
import Item from '../components/item';

let instance = null;

class DOM {
	constructor( container ) {
		if ( ! instance ) {
			this.$container = container || false;
			instance = this;
		}

		return instance;
	}

	setContainer( container ) {
		this.$container = container;
	}

	getItems() {
		const domItems = [];
		const validProps = [
			'contributor_cnt',
			'mention_cnt',
			'impact_cnt',
			'output_cnt',
		];

		this.$container.find( '.rsd-results-item' ).each( function () {
			const $item = $( this );
			const item = new Item( {
				id: $item.data( 'id' ),
				title: $item.find( 'h3' ).text(),
				description: $item.find( '.card-section p' ).text(),
				slug: getSlugFromURL( $item.find( 'h3 a' ).attr( 'href' ) ),
				labels: [],
			} );
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

	getItemsTotal() {
		const count = this.$container
			.find( '.rsd-results-count' )
			.data( 'items-total' );
		return parseInt( count );
	}

	getSearchTerm() {
		return this.$container
			.find( '.rsd-search-input' )
			.val()
			.toLowerCase()
			.trim();
	}

	getOrderBy() {
		const orderBy = this.$container
			.find( '.rsd-sortby-input' )
			.val()
			.toLowerCase()
			.trim();
		return orderBy || false;
	}

	getOrder( orderBy = false ) {
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

	getFilterValues() {
		const filters = {};
		this.$container.find( '.rsd-filters select' ).each( function () {
			const $filter = $( this );
			const identifier = $filter.data( 'filter' );
			const value = $filter.val();
			if (
				value &&
				( value !== '' || ( Array.isArray( value ) && value.length ) )
			) {
				filters[ identifier ] = Array.isArray( value )
					? value
					: [ value ];
			}
		} );
		return filters;
	}

	hasFilterValues() {
		return Object.values( this.getFilterValues() ).some(
			( arr ) => arr.length > 0
		);
	}
}

export default new DOM();
