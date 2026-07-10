/**
 * RSD WordPress - ESLint flat configuration
 * https://eslint.org/docs/latest/use/configure/configuration-files
 *
 * @module
 * @license Apache-2.0
 */

import wordpress from '@wordpress/eslint-plugin';
import compat from 'eslint-plugin-compat';
import noUnsanitized from 'eslint-plugin-no-unsanitized';
import globals from 'globals';

export default [
	{
		ignores: [ 'dist/**', 'node_modules/**' ],
	},
	...wordpress.configs.recommended,
	compat.configs[ 'flat/recommended' ],
	{
		languageOptions: {
			ecmaVersion: 'latest',
			globals: {
				...globals.browser,
				...globals.jquery,
				jQuery: 'readonly',
				$: 'readonly',
				rsdWordPressVars: 'readonly',
			},
		},
		plugins: {
			'no-unsanitized': noUnsanitized,
		},
		rules: {
			'no-console': 'off',
			'prettier/prettier': 'warn',
			indent: [ 'error', 'tab' ],
			'linebreak-style': [ 'error', 'unix' ],
			quotes: [ 'error', 'single' ],
			semi: [ 'error', 'always' ],
			'no-unsanitized/property': 'error',
			'no-unsanitized/method': [
				'error',
				{
					escape: {
						// API data interpolated into HTML must go through escapeHtml()
						// from helpers/utils.js. Item.getLabels()/getProps() return
						// HTML that is escaped internally.
						methods: [ 'escapeHtml', 'item.getLabels', 'item.getProps' ],
					},
				},
				{
					// Also check jQuery DOM-insertion methods (the default set only
					// covers native APIs such as insertAdjacentHTML).
					append: { properties: [ 0 ] },
					prepend: { properties: [ 0 ] },
					before: { properties: [ 0 ] },
					after: { properties: [ 0 ] },
					html: { properties: [ 0 ] },
					replaceWith: { properties: [ 0 ] },
				},
			],
		},
	},
];
