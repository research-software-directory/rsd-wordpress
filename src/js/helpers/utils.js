/**
 * Util functions
 */

function getSlugFromURL( url ) {
	return url.split( '/' ).pop();
}

// Escape a value for safe interpolation into HTML content or attribute values.
function escapeHtml( value ) {
	return String( value )
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' )
		.replace( /"/g, '&quot;' )
		.replace( /'/g, '&#039;' );
}

export { getSlugFromURL, escapeHtml };
