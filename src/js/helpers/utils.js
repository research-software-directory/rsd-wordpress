/**
 * Util functions
 */

function getSlugFromURL( url ) {
	return url.split( '/' ).pop();
}

export { getSlugFromURL };
