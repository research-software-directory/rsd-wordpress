/**
 * RSD WordPress plugin JavaScript for admin settings.
 *
 * @module rsd-wordpress
 */

'use strict';

// Main function, executed when the page is loaded.
( function ( $ ) {
	let frame;
	const $imgUrl = $( '#rsd-default-img-url' ),
		$imgPreview = $( '#rsd-default-img-preview img' ),
		$selectButton = $( '#rsd-select-default-img' ),
		$removeButton = $( '#rsd-remove-default-img' );

	function setImageUrl( url ) {
		$imgUrl.val( url );

		if ( url ) {
			$imgPreview.removeClass( 'hidden' ).attr( 'src', url );
			$removeButton.removeClass( 'hidden' );
		} else {
			$imgPreview.addClass( 'hidden' ).attr( 'src', '' );
			$removeButton.addClass( 'hidden' );
		}
	}

	$selectButton.on( 'click', function ( e ) {
		e.preventDefault();
		if ( frame ) {
			frame.open();
			return;
		}

		// Define frame as wp.media object
		frame = wp.media( {
			title: 'Choose an image',
			multiple: false,
			library: { type: 'image' },
			button: { text: 'Select image' },
		} );
		frame.on( 'select', function () {
			const selection = frame.state().get( 'selection' ).first() || false;
			if ( ! selection ) {
				return;
			}
			setImageUrl( selection.toJSON().url );
		} );

		frame.open();
	} );

	$removeButton.on( 'click', function ( e ) {
		e.preventDefault();
		setImageUrl( '' );
	} );
} )( jQuery.noConflict() );
