<?php
// Add a new column to the post list table
add_filter( 'manage_posts_columns', 'ped_add_expiration_date_column' );
function ped_add_expiration_date_column( $columns ) {
    // Add a new column with the heading 'Expiration Date'
    $columns['ped_expiration_date'] = __( 'Expiration Date', 'ped' );
    return $columns;
}

// Populate the expiration date column
add_action( 'manage_posts_custom_column', 'ped_populate_expiration_date_column', 10, 2 );
function ped_populate_expiration_date_column( $column_name, $post_id ) {
    // If the column is the 'Expiration Date' column, display the expiration date for the post
    if ( 'ped_expiration_date' === $column_name ) {
        $expiration_date = get_post_meta( $post_id, '_ped_expiration_date', true );
        if ( $expiration_date ) {
            echo date_i18n( get_option( 'date_format' ), strtotime( $expiration_date ) );
        } else {
            echo '-';
        }
    }
}
