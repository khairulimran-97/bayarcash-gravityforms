<?php

defined( 'ABSPATH' ) || exit;

function my_custom_intervals( $schedules ) {
    // Add 'every_minute' interval
    $schedules['every_minute'] = array(
        'interval' => 60, // 60 seconds
        'display'  => __( 'Every Minute' ),
    );

    // Add 'every_ten_minutes' interval
    $schedules['every_ten_minutes'] = array(
        'interval' => 600, // 600 seconds = 10 minutes
        'display'  => __( 'Every Ten Minutes' ),
    );

    return $schedules;
}
add_filter( 'cron_schedules', 'my_custom_intervals' );

function bayarcash_gf_check(): void
{
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

function gravity_form_auto_failed_check(): void
{
    global $wpdb;

    // Get entry IDs with meta_key "payment_status" and meta_value NULL
    $entry_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT entry_id FROM {$wpdb->prefix}gf_entry_meta WHERE meta_key = %s AND meta_value IS NULL",
            'payment_status'
        )
    );

    if ( ! empty( $entry_ids ) ) {
        foreach ( $entry_ids as $entry_id ) {
            // Count total rows where meta_key is 'payment_status' and meta_value is NULL
            $row_count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}gf_entry_meta WHERE entry_id = %d AND meta_key = %s AND meta_value IS NULL",
                    $entry_id,
                    'payment_status'
                )
            );

            // If total rows are 10 or more, delete the "payment_status" meta and update payment_status to "Failed"
            if ( $row_count >= 10 ) {
                // Delete related rows in wp_gf_entry_meta
                $wpdb->delete(
                    $wpdb->prefix . 'gf_entry_meta',
                    array(
                        'entry_id' => $entry_id,
                        'meta_key' => 'payment_status',
                        'meta_value' => NULL,
                    )
                );

                // Update payment_status in wp_gf_entry to "Failed"
                $wpdb->update(
                    $wpdb->prefix . 'gf_entry',
                    array( 'payment_status' => 'Failed' ),
                    array( 'id' => $entry_id )
                );
            }
        }
    }
}


function schedule_cron_event(): void
{
    if ( ! wp_next_scheduled( 'bayarcash_requery_check_gf' ) ) {
        wp_schedule_event( time(), 'every_minute', 'bayarcash_requery_check_gf' );
    }
}

function schedule_gravity_form_auto_failed_cron(): void
{
    if ( ! wp_next_scheduled( 'gravity_form_auto_failed' ) ) {
        wp_schedule_event( time(), 'every_ten_minutes', 'gravity_form_auto_failed' );
    }
}

add_action( 'wp', 'schedule_cron_event' );
add_action( 'wp', 'schedule_gravity_form_auto_failed_cron' );


register_activation_hook( __FILE__, 'schedule_cron_event' );

function unschedule_cron_event(): void
{
    wp_clear_scheduled_hook( 'bayarcash_requery_check_gf' );
}
register_deactivation_hook( __FILE__, 'unschedule_cron_event' );

add_action( 'bayarcash_requery_check_gf', 'bayarcash_gf_check' );
add_action( 'gravity_form_auto_failed', 'gravity_form_auto_failed_check' );

