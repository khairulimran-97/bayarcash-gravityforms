<?php

defined( 'ABSPATH' ) || exit;

function my_custom_interval( $schedules ) {
    $schedules['every_minute'] = array(
        'interval' => 60, // Interval in seconds
        'display'  => __( 'Every Minute' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'my_custom_interval' );

function bayarcash_gf_check() {
    global $wpdb;

    // Get entries with empty or NULL payment_status
    $unpaid_entries = $wpdb->get_results(
        "SELECT id FROM {$wpdb->prefix}gf_entry WHERE payment_status IS NULL OR payment_status = ''"
    );

    // Check if there are any unpaid entries
    if ($unpaid_entries) {
        // Loop through each unpaid entry and trigger the cron event
        foreach ($unpaid_entries as $entry) {
            $entry_id = $entry->id;
            // Construct the URL with entry ID
            $url = home_url("/?callback=gravityformsbayarcash&entry_id=$entry_id");

            // Send GET request to trigger the cron event
            $response = wp_remote_get($url);

            // Check for errors
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log("Error triggering cron event for entry ID $entry_id: $error_message");
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                if ($response_code !== 200) {
                    error_log("Failed to trigger cron event for entry ID $entry_id. Response code: $response_code");
                } else {
                    error_log("Cron event triggered successfully for entry ID $entry_id!");
                }
            }
        }
    } else {
        error_log("No unpaid entries found.");
    }
}
function schedule_cron_event() {
    if ( ! wp_next_scheduled( 'bayarcash_requery_check_gf' ) ) {
        wp_schedule_event( time(), 'every_minute', 'bayarcash_requery_check_gf' );
    }
}

add_action( 'wp', 'schedule_cron_event' );

register_activation_hook( __FILE__, 'schedule_cron_event' );

function unschedule_cron_event() {
    wp_clear_scheduled_hook( 'bayarcash_requery_check_gf' );
}
register_deactivation_hook( __FILE__, 'unschedule_cron_event' );

add_action( 'bayarcash_requery_check_gf', 'bayarcash_gf_check' );
