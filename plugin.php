<?php
/*
Plugin Name: Research Software Directory API Table
Description: Displays software information from the Research Software Directory API as a table
Version: 1.0
Author: ctw@ctwhome.com (eScience Center)
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
        $table .= '<td>' . $item['software']['brand_name'] . '</td>';
        $table .= '<td>' . $item['software']['description'] . '</td>';
        $table .= '<td><a href="' . $item['software']['is_published'] . '">' . $item['software']['is_published'] . '</a></td>';
        $table .= '</tr>';
    }

    // Close the table
    $table .= '</tbody>';
    $table .= '</table>';

    // Output the table
    echo $table;
}

// Add shortcode to display the table
add_shortcode( 'research_software_directory_table', 'research_software_directory_api_table' );
