<?php

class RSD_WP {
	public static function init() {
		if ( ! shortcode_exists( 'research_software_directory_table' ) ) {
			// Add shortcode to display the table
			add_shortcode( 'research_software_directory_table', array( 'RSD_WP', 'display_table' ) );
		}
	}

	/**
	 * Easiest way to render the table.
	 */
	public static function display_table( $atts ) {
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

		return $output;
	}

}
