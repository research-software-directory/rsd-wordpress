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

/*
"id": "77201671-58bf-4d6e-9f3d-d509c0ff5b88",
"slug": "knime-kripodb",
"brand_name": "KNIME node for Kripo",
"concept_doi": "10.5281/zenodo.597262",
"description": "* For cheminformaticans who want to do structure-based protein binding site comparison and bioisosteric replacement for ligand design\n* It makes the Kripo Python library available in the KNIME workflow platform as workflow nodes.\n* Kripo encodes the interactions of protein and bound ligand also known as a pharmacophore into a fingerprint, the fingerprints can be compared to each other to find similar pharmacophores\n* The Kripo software is open source while most other similar software is commercial or requires registration",
"description_url": null,
"description_type": "markdown",
"get_started_url": "https://www.knime.com/3d-e-chem-nodes-for-knime",
"is_published": true,
"short_statement": "A node for the KNIME workflow systems that allows you to compare different binding sites in proteins with each other.",
"created_at": "2022-07-06T15:41:57.646939+00:00",
"updated_at": "2022-07-06T15:41:57.646939+00:00",
*/

/*
brand_name
short_statement
description

I don't have yet:
- Year of the software (releases this year)
- Domain
- License
- Impact metric (Num visits, downloads, citations, etc)

*/

function research_software_directory_api_table() {
    // Call the API
    $response = wp_remote_get( 'https://research-software-directory.org/api/v1/software_for_organisation?select=*,software!left(*)&organisation=eq.35c17f17-6b5f-4385-aa8b-6b1d33a10157&limit=2' );

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
  echo $output
}

// Add shortcode to display the table
// [research_software_directory_table limit="10" organization-id="35c17f17-6b5f-4385-aa8b-6b1d33a10157"]
add_shortcode( 'research_software_directory_table', 'research_software_directory_api_table' );

$atts = shortcode_atts( array(
    'organization-id' => '35c17f17-6b5f-4385-aa8b-6b1d33a10157',
    'limit' => 10,
), $atts, 'research_software_directory_table' );

?>