<?php
/*
Plugin Name: Post Expiration Date
Plugin URI: https://example.com/
Description: Allows administrators to set an expiration date for posts.
Version: 1.0.0
Author: Your Name
Author URI: https://example.com/
License: GPL2
*/

require_once('inc/init.php');

// Add a meta box to the post editor screen to allow users to set the expiration date
function ped_add_meta_box() {
    add_meta_box(
        'ped_expiration_date',
        'Post Expiration Date',
        'ped_meta_box_callback',
        'post',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ped_add_meta_box' );

// Render the meta box content
function ped_meta_box_callback( $post ) {
    // Use nonce for verification
    wp_nonce_field( basename( __FILE__ ), 'ped_nonce' );

    // Get the current expiration date
    $expiration_date = get_post_meta( $post->ID, '_ped_expiration_date', true );

    // Output the HTML for the meta box
    echo '<label for="ped_expiration_date">' . __( 'Expiration Date:' ) . '</label>';
    echo '<input type="date" id="ped_expiration_date" name="ped_expiration_date" value="' . esc_attr( $expiration_date ) . '">';
}

// Save the expiration date when the post is saved
function ped_save_post( $post_id ) {
    // Check if the current user has permission to edit the post
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check if the nonce is valid
    if ( ! isset( $_POST['ped_nonce'] ) || ! wp_verify_nonce( $_POST['ped_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    // Save the expiration date
    if ( isset( $_POST['ped_expiration_date'] ) ) {
        update_post_meta( $post_id, '_ped_expiration_date', sanitize_text_field( $_POST['ped_expiration_date'] ) );
    } else {
        delete_post_meta( $post_id, '_ped_expiration_date' );
    }
}
add_action( 'save_post', 'ped_save_post' );

// Check if a post has expired
function ped_check_post_expiry( $query ) {
    if ( ! is_admin() && $query->is_main_query() ) {
        $today = date( 'Y-m-d' );
        $meta_query = array(
            array(
                'key' => '_ped_expiration_date',
                'value' => $today,
                'compare' => '<=',
                'type' => 'DATE',
            ),
        );
        $query->set( 'meta_query', $meta_query );
    }
}
add_action( 'pre_get_posts', 'ped_check_post_expiry' );

add_action( 'wp', 'ped_check_post_expiration' );
function ped_check_post_expiration() {
    // Only check for expired posts on the front-end
    if ( ! is_admin() ) {
        // Get the expiration date for the current post
        $expiration_date = get_post_meta( get_the_ID(), '_ped_expiration_date', true );
        // If the post is expired, display a custom message and exit
        if ( $expiration_date && strtotime( $expiration_date ) < time() ) {
            wp_die( 'This post has expired.' );
        }
    }
}
