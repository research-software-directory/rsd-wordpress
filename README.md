# Research Software Directory plugin for WordPress

This repo contains a WordPress integration of the Projects and Software overview for the Research Software Directory, using the RSD API.

## How to use the plugin

To display the overview (e.g. for the Software section for the Netherlands eScience Center organisation) on your Wordpress site, use one of the following shortcode examples in any post or page:
```shell
[research_software_directory section="software" organisation="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

To show the Projects section for the Netherlands eScience Center:
```shell
[research_software_directory section="projects" organisation="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

To limit the initial amount of nine items at page load:
```shell
[research_software_directory section="software" limit="9" organisation="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

### Exporting production ready plugin bundle

To create a smaller, production-ready ZIP file of all the plugin files without any development related files, use the project `export` script:
```shell
# First install the required Node.js dependencies
$ pnpm install

# Export ZIP file to `/export` folder
$ pnpm run export
```

The folder extracted from the ZIP file, which contains the plugin files required for production, can then be used to deploy to a production server.

### Deployment or testing: build compiled bundle files first

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
