/**
 * RSD WordPress - Vite configuration file
 * https://vitejs.dev/config/
 *
 * @package RSD_WP
 * @since 0.8.0
 * @license Apache-2.0
 * @link https://research-software-directory.org
 */

import { defineConfig } from 'vite'
import browserslist from 'browserslist';
import {browserslistToTargets} from 'lightningcss';
import { renameSync } from 'fs';
import { resolve } from 'path';

export default defineConfig({
	build: {
		lib: {
			entry: 'src/index.js',
			name: 'rsd-wordpress',
			fileName: 'rsd-wordpress',
		},
		rollupOptions: {
			output: {
				entryFileNames: '[name].min.js',
				chunkFileNames: '[name].min.js',
				assetFileNames: '[name].min.[ext]',
			},
		},
		css: {
			transformer: 'lightningcss',
			lightningcss: {
				targets: browserslistToTargets(browserslist('defaults')),
			},
			cssMinify: 'lightningcss',
		},
	},
	plugins: [
		{
			name: 'rename-output',
			writeBundle(options, bundle) {
				renameSync(resolve(__dirname, 'dist', 'index.min.js'), resolve(__dirname, 'dist', 'rsd-wordpress.min.js'));
				renameSync(resolve(__dirname, 'dist', 'style.min.css'), resolve(__dirname, 'dist', 'rsd-wordpress.min.css'));
			},
		},
	],
})
