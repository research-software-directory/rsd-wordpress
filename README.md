# Research Software Directory plugin for WordPress

This repo contains a WordPress integration of the Projects and Software overview for the [Research Software Directory](https://research-software-directory.org/), using the RSD API.

## How to use the plugin

To display the overview on your WordPress site, add the shortcode to any post or page. For example, the Software section for the Netherlands eScience Center organisation:

```text
[research_software_directory section="software" organisation-id="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

To show the Projects section instead:

```text
[research_software_directory section="projects" organisation-id="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

To limit the initial number of items shown at page load to nine:

```text
[research_software_directory section="software" limit="9" organisation-id="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

### Shortcode attributes

| Attribute         | Description                                      | Default                                  |
|-------------------|--------------------------------------------------|------------------------------------------|
| `section`         | Which overview to display: `software` or `projects` | `software`                            |
| `organisation-id` | The RSD organisation UUID to show items for      | Netherlands eScience Center organisation |
| `limit`           | Number of items to show at initial page load     | `48`                                     |

Note: only one `[research_software_directory]` shortcode per page is supported; any further occurrences render a notice instead of a second overview. The shortcode works in post and page content, and also in widgets and template files (where the plugin styles load in the footer).

### Plugin settings

After activating the plugin, go to **Settings → Research Software Directory** in the WordPress admin to configure which filters are shown for each section and to set a default image for items without one.

## Local development

### Requirements

- Node.js >= 22 (see `.nvmrc`) and [pnpm](https://pnpm.io/) for building the front end assets
- Composer for the PHP coding standards tooling

### Installing the plugin in WordPress

This plugin can be used in a new or existing WordPress installation in a local development environment. For a super quick testing environment, the bundled `docker-compose.yml` can be used with the application [Local](https://localwp.com/).

First make sure your WordPress installation is functional, then move the plugin folder to the `plugins` folder of your installation, which is usually something like `<wordpress_folder>/wp-content/plugins/`.

### Building the bundle files

The project uses [Vite](https://vitejs.dev/) to build the plugin front end assets (CSS and JS) into the `dist/` folder. Production builds are minified (`.min` suffix) according to the project's [Browserslist](https://browsersl.ist/) configuration, using [Autoprefixer](https://github.com/postcss/autoprefixer) and [Babel](https://babeljs.io/) for cross-browser compatible code.

Which files WordPress loads depends on the environment: minified `.min` bundles are used when `wp_get_environment_type()` is `production` or `staging`, and unminified bundles otherwise. So make sure to run the build that matches your environment:

```shell
# First install the required Node.js dependencies
$ pnpm install

# Development build in watch mode (unminified)
$ pnpm run dev

# Production build (minified, with sourcemaps)
$ pnpm run build
```

### Coding standards and linting

This project uses [EditorConfig](https://editorconfig.org/) to maintain a consistent coding style. Please make sure your editor applies this configuration to any of your code changes for this repo.

JavaScript is linted with ESLint, using the [WordPress ESLint plugin](https://www.npmjs.com/package/@wordpress/eslint-plugin):

```shell
# Check all JS files in src/
$ pnpm run lint

# Automatically fix violations where possible
$ pnpm run lint:fix
```

PHP follows the [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards), checked with PHP_CodeSniffer using the bundled `.phpcs.xml` configuration. Please run it before committing any PHP changes:

```shell
# Install PHP_CodeSniffer and related packages
$ composer install

# Run PHPCS to check all PHP files in the project
$ composer run lint

# Or use PHP Code Beautifier and Fixer to automatically correct coding standard violations
$ composer run format
```

## Exporting a production-ready plugin bundle

To create a smaller, production-ready ZIP file of all the plugin files without any development related files, use the project `export` script:

```shell
# Export ZIP file to the export/ folder
$ pnpm run export
```

The folder extracted from the ZIP file, which contains the plugin files required for production, can then be used to deploy to a production server.

## License

This plugin is licensed under the [Apache License 2.0](LICENSE).
