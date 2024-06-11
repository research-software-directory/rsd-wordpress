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

const baseConfig = {
	build: {
		lib: {
			entry: 'src/index.js',
			name: 'rsd-wordpress',
			fileName: 'rsd-wordpress',
		},
		emptyOutDir: false,
		rollupOptions: {
			output: {
				chunkFileNames: '[name].js',
				assetFileNames: '[name].[ext]',
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
		rollupOptions: {
			...baseConfig.build.rollupOptions,
			output: {
				...baseConfig.build.rollupOptions.output,
				entryFileNames: `${ baseConfig.build.lib.fileName }.js`,
				chunkFileNames: `${ baseConfig.build.lib.fileName }.js`,
				assetFileNames: `${ baseConfig.build.lib.fileName }.css`,
			},
		},
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
			output: {
				...baseConfig.build.rollupOptions.output,
				entryFileNames: `${ baseConfig.build.lib.fileName }.min.js`,
				chunkFileNames: `${ baseConfig.build.lib.fileName }.min.js`,
				assetFileNames: `${ baseConfig.build.lib.fileName }.min.css`,
			},
		},
		css: {
			...baseConfig.build.css,
			cssMinify: 'lightningcss',
		},
	},
};

export default defineConfig( isProduction ? prodConfig : devConfig );
