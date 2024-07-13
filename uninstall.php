<?php
// Logging for debugging
error_log( 'Thank You Uninstall Initiated ' );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

	global $wpdb;

	// Logging for debugging
	error_log( 'Thank You page options ' );

	// Sanitize the prefix to prevent SQL injection
	$prefix = esc_sql( 'wctr_' );    

	// Prepare the SQL query with the wpds prefix
    $table_name = $wpdb->prefix . 'options';
    $sql = $wpdb->prepare(
        "DELETE FROM {$table_name} WHERE option_name LIKE %s",
        $wpdb->esc_like($prefix) . '%'
    );

	// Execute the query
	$result = $wpdb->query( $sql );

	// Logging the result for debugging
	if ( $result === false ) {
		error_log( 'Error in delete query: ' . $wpdb->last_error );
	} else {
		error_log( 'Number of rows deleted: ' . $result );
	}

