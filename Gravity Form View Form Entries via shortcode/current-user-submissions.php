<?php
/**
 * Retrieve all the Gravity Forms submissions made by the current user for a given form.
 *
 * @param int $form_id The ID of the Gravity Form to retrieve submissions for.
 *
 * @return string A HTML table containing the submissions.
 * 
 * @example [gf_user_entries form_id="1"]
 * 
 */
function gf_display_user_entries( $form_id ) {
    if ( ! is_user_logged_in() ) {
        return '<p>You must be logged in to view your submissions.</p>';
    }

    $user_id = get_current_user_id();
    $entries = GFAPI::get_entries( $form_id, array(
        'field_filters' => array(
            array(
                'key'   => 'created_by',
                'value' => $user_id
            )
        )
    ) );

    if ( empty( $entries ) ) {
        return '<p>No submissions found.</p>';
    }

    // Get the form fields to use as table headers
    $form = GFAPI::get_form( $form_id );
    $fields = $form['fields'];

    $output = '<table class="gf-entries-table">';
    $output .= '<thead><tr>';
    
    // Table Headers
    $output .= '<th>Entry ID</th><th>Submission Date</th>';
    foreach ( $fields as $field ) {
        if ( ! empty( $field->label ) ) {
            $output .= '<th>' . esc_html( $field->label ) . '</th>';
        }
    }
    $output .= '</tr></thead><tbody>';

    // Table Body
    foreach ( $entries as $entry ) {
        $output .= '<tr>';
        $output .= '<td>' . esc_html( $entry['id'] ) . '</td>';
        $output .= '<td>' . esc_html( date( 'F j, Y', strtotime( $entry['date_created'] ) ) ) . '</td>';
        
        foreach ( $fields as $field ) {
            if ( ! empty( $field->id ) ) {
                $field_value = isset( $entry[ $field->id ] ) ? $entry[ $field->id ] : '-';
                $output .= '<td>' . esc_html( $field_value ) . '</td>';
            }
        }
        $output .= '</tr>';
    }

    $output .= '</tbody></table>';
    return $output;
}

// Create a Shortcode
add_shortcode( 'gf_user_entries', function( $atts ) {
    $atts = shortcode_atts( array(
        'form_id' => 1
    ), $atts );

    return gf_display_user_entries( $atts['form_id'] );
} );
