/**
 * RSD WordPress - ESLint flat configuration
 * https://eslint.org/docs/latest/use/configure/configuration-files
 *
 * @module
 * @license Apache-2.0
 */

import wordpress from '@wordpress/eslint-plugin';
import compat from 'eslint-plugin-compat';
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
		rules: {
			'no-console': 'off',
			'prettier/prettier': 'warn',
			indent: [ 'error', 'tab' ],
			'linebreak-style': [ 'error', 'unix' ],
			quotes: [ 'error', 'single' ],
			semi: [ 'error', 'always' ],
		},
	},
];
