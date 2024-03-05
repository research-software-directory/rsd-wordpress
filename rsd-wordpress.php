<?php
/*
 * Research Software Directory
 *
 * @package     RSD
 * @author      Netherlands eScience Center
 *
 * @wordpress-plugin
 * Plugin Name: Research Software Directory for WordPress
 * Plugin URI:  https://github.com/research-software-directory/rsd-wordpress
 * Description: Displays projects and software information from the Research Software Directory API.
 * Version:     0.0.1
 * Author:      ctw@ctwhome.com (eScience Center), Vincent Twigt (Mezzo Media)
 * Author URI:  https://www.esciencecenter.nl/
 * Text domain: rsd-wordpress
 * License:     Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If the plugin was already loaded, do not load again
if ( defined( 'RSD_WP_LOADED' ) ) {
	return;
}

define( 'RSD_WP_LOADED', true );

// Easiest way to render the table
function research_software_directory_api_table( $atts ) {
	if ( is_admin() ) {
		return;
	}

	// Set default attributes
	$atts = shortcode_atts( array(
		'organization-id' => '35c17f17-6b5f-4385-aa8b-6b1d33a10157',
		'limit' => 10,
	), $atts, 'research_software_directory_table' );

	// Process attributes
	$organization_id = $atts['organization-id'];
	$limit = intval( $atts['limit'] );

	// Call the API
	$url = sprintf( 'https://research-software-directory.org/api/v1/software_for_organisation?select=*,software!left(*)&organisation=eq.%s&limit=%s', $organization_id, $limit );
    $response = wp_remote_get( $url );

    // Check for error
    if ( is_wp_error( $response ) ) {
        echo 'Error: ' . $response->get_error_message();
        return;
    }

    // Decode the API response
    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    // Check if data is returned
    if ( ! $data ) {
        echo 'No data returned from API';
        return;
    }

    // Start building the table
    $table = '<table>';
    $table .= '<thead>';
    $table .= '<tr>';
    $table .= '<th>Software Name</th>';
    $table .= '<th>Description</th>';
    $table .= '<th>is_published</th>';
    $table .= '</tr>';
    $table .= '</thead>';
    $table .= '<tbody>';

    // Loop through the data and add each item to the table
    foreach ( $data as $item ) {
        $table .= '<tr>';
        $table .= '<td><a href="https://research-software-directory.org/software/' . $item['software']['slug'] . '" target="_blank">' . $item['software']['brand_name'] . '</a></td>';
        $table .= '<td>Desc ' . $item['software']['description'] . '</td>';
        $table .= '<td><a href="' . $item['software']['is_published'] . '">' . $item['software']['is_published'] . '</a></td>';
        $table .= '</tr>';
    }

    // Close the table
    $table .= '</tbody>';
    $table .= '</table>';

    // Output the table
    echo $table;

    // Start building HTML output
  $output = '<div class="software-grid">';

   $output .= '<h2>Software</h2>';
  // Loop through each item in the data and display a card
  foreach ( $data as $item ) {
    $output .= '<div class="software-card">';
    $output .= '<h3>' . $item['brand_name'] . '</h3>';
    $output .= '</div>';
  }

  $output .= '</div>';
  echo $output;
}

// Add shortcode to display the table
add_shortcode( 'research_software_directory_table', 'research_software_directory_api_table' );
