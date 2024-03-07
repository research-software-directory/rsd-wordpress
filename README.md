# Research Software Directory plugin for WordPress

This repo contains a WordPress integration of the Projects and Software overview for the Research Software Directory, using the RSD API.

## How to use the plugin

To display the overview table on your Wordpress site, use the following shortcode in any post or page:
```shell
[research_software_directory_table limit="4" organisation="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
```

## Local development

This plugin can be used in a new or existing WordPress intallation in a local development environment, like with the application [Local](https://localwp.com/).

First make sure your WordPress installation is functional, then move the plugin file to the `plugins` folder of your installation, which is usually something like `<wordpress_folder>/wp-content/plugins/`.

### Editor configuration

This project uses [EditorConfig](https://editorconfig.org/) to maintain a consistent coding style. Please make sure your editor applies this configuration to any of your code changes for this repo.
