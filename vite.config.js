/**
 * RSD WordPress - Vite configuration file
 * https://vitejs.dev/config/
 *
 * @module
 * @since 0.8.0
 * @license Apache-2.0
 */

import { defineConfig } from 'vite';
import postcss from 'postcss';
import browserslist from 'browserslist';
import { browserslistToTargets } from 'lightningcss';
import autoprefixer from 'autoprefixer';
import { babel } from '@rollup/plugin-babel';
import { resolve } from 'path';

const isProduction = process.env.NODE_ENV === 'production';
const entryName = process.env.ENTRY_NAME || 'rsd-wordpress';
const entryFile = process.env.ENTRY_FILE || 'src/index.js';

const fileName = ( name, isMinified, ext ) => {
	const min = isMinified ? '.min' : '';
	return `${ name }${ min }.${ ext }`;
};

export default defineConfig( {
	optimizeDeps: {
		exclude: [ 'jquery' ],
	},
	define: {
		'process.env': process.env,
		$: 'window.jQuery',
		jQuery: 'window.jQuery',
	},
	build: {
		emptyOutDir: false,
		minify: isProduction,
		cssMinify: isProduction ? 'lightningcss' : false,
		sourcemap: isProduction,
		lib: {
			name: entryName,
			entry: { [ entryName ]: resolve( __dirname, entryFile ) },
		},
		rollupOptions: {
			output: {
				format: 'iife',
				entryFileNames: ( { name } ) =>
					fileName( name, isProduction, 'js' ),
				chunkFileNames: ( { name } ) =>
					fileName( name, isProduction, 'js' ),
				assetFileNames: ( assetInfo ) => {
					const ext = assetInfo.name.split( '.' ).pop();
					return fileName( entryName, isProduction, ext );
				},
			},
			plugins: isProduction
				? [
					babel( {
						exclude: 'node_modules/**',
						babelHelpers: 'bundled',
					} ),
				]
				: [],
		},
		css: {
			postcss: {
				plugins: [ postcss(), autoprefixer() ],
			},
			transformer: 'lightningcss',
			lightningcss: {
				targets: browserslistToTargets( browserslist( 'defaults' ) ),
			},
		},
	},
} );
