# Research Software Directory plugin for WordPress

This repo contains a WordPress integration of the Projects and Software overview for the Research Software Directory, using the RSD API.

## How to use the plugin

To display the overview (e.g. for the Software section for the Netherlands eScience Center organisation) on your Wordpress site, use the following shortcode in any post or page:
```shell
[research_software_directory section="software" limit="4" organisation="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

### Deployment or unit testing: build compiled bundle files first

The current configuration uses [Vite](https://vitejs.dev/) to build the plugin front end assets, CSS and JS. Before using the plugin in a production environment (which is the default), make sure to run the build script:
```shell
# First install the required Node.js dependencies
$ pnpm install

# Run the build script for production
$ pnpm run build
``` 

This will create minified CSS and JS bundle files according to the project's [Browserslist](https://browsersl.ist/) configuration, and uses [Autoprefixer](https://github.com/postcss/autoprefixer) and [Babel](https://babeljs.io/) to create cross-browser compatible code.

_Note: See the section below for instructions on how to do this for a development environment._

## Local development

### Installing the plugin in WordPress

This plugin can be used in a new or existing WordPress intallation in a local development environment. For a super quick testing environment, the bundled `docker-compose.yml` can be used with the application [Local](https://localwp.com/).

First make sure your WordPress installation is functional, then move the plugin file to the `plugins` folder of your installation, which is usually something like `<wordpress_folder>/wp-content/plugins/`.

### Building compiled bundle files for development

Similar to the above example, before using the plugin in a development environment, make sure to run the build script (which uses watch mode automatically):

```shell
# First install the required Node.js dependencies
$ pnpm install

# Run the build script for production
$ pnpm run dev
``` 

### Editor configuration

This project uses [EditorConfig](https://editorconfig.org/) to maintain a consistent coding style. Please make sure your editor applies this configuration to any of your code changes for this repo.

### WordPress Coding Standards

To ensure code quality and adherence to coding conventions, before committing any changes to the code of this project, please use `phpcs` (see [WordPress Coding Standards for PHP_CodeSniffer](https://github.com/WordPress/WordPress-Coding-Standards)) with the bundled PHP_CS configuration file.

You can also use the bundled project PHP_CS configuration by running:
```shell
# Install PHP_CS and related packages
$ composer install

# Run PHP_CS to check all PHP files in the project
$ composer run lint

# Or use PHP Code Beautifier and Fixer to automatically correct coding standard violations
$ composer run format
```
