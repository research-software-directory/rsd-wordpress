# RSD WordPress Plugin
Integrate the RSD into WordPress.


## Use the plugin inside a page or post inside WordPress
To display the table on your Wordpress site, you can use the shortcode in any post or page providing the followin information:
```shell
[software_grid limit="4" organisation="12345678-1234-1234-1234-1234567890ab"]
```

## Local developent
You will need a WordPress set up. You can eithor do it with an existing WordPRess intallation, use a development environment like the application Local, or use the Docker Compose provided in this repo:
```
docker comppose up
```


Then move the plugin file inside the the folder: `wp_data > wp_content > plugins > plugin_name.php`


