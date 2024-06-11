/**
 * Filter class
 */

export default class Filter {
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

		this.items = [];
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
		const defaultPlaceholder = 'Filter by ' + this.getTitle().toLowerCase(); // TODO: i18n
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
