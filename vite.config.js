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

const isProduction = process.env.NODE_ENV === 'production';

const fileName = ( name, isMinified, ext ) => {
	const min = isMinified ? '.min' : '';
	return `${ name }${ min }.${ ext }`;
};

const baseConfig = {
	optimizeDeps: {
		exclude: [ 'jquery' ],
	},
	define: {
		'process.env': process.env,
		$: 'window.jQuery',
		jQuery: 'window.jQuery',
	},
	build: {
		lib: {
			entry: {
				'rsd-wordpress': 'src/index.js',
				'rsd-wordpress-admin': 'src/index-admin.js',
			},
			name: 'rsd-wordpress',
		},
		emptyOutDir: false,
		rollupOptions: {
			output: {
				entryFileNames: ( { name } ) =>
					fileName( name, isProduction, 'js' ),
				chunkFileNames: ( { name } ) =>
					fileName( name, isProduction, 'js' ),
				assetFileNames: ( assetInfo ) => {
					const nameParts = assetInfo.name.split( '.' );
					const ext = nameParts.length > 1 ? nameParts.pop() : 'css';
					if ( ext === 'css' ) {
						return fileName( 'rsd-wordpress', isProduction, ext );
					}
					return fileName( assetInfo.name, isProduction, ext );
				},
			},
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
};

const devConfig = {
	...baseConfig,
	build: {
		...baseConfig.build,
		minify: false,
		css: {
			...baseConfig.build.css,
			cssMinify: false,
		},
	},
};

const prodConfig = {
	...baseConfig,
	build: {
		...baseConfig.build,
		minify: true,
		sourcemap: true,
		rollupOptions: {
			...baseConfig.build.rollupOptions,
			plugins: [
				babel( {
					exclude: 'node_modules/**',
					babelHelpers: 'bundled',
				} ),
			],
		},
		css: {
			...baseConfig.build.css,
			cssMinify: 'lightningcss',
		},
	},
};

export default defineConfig( isProduction ? prodConfig : devConfig );
