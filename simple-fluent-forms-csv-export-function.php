<?php

/**
 * Adds export button handling for Fluent Forms Csv Export.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

add_action("admin_init", "ffse_export_button_action");


/**
 *  Function performing the actual export.
 */
function ffse_export_button_action()
{
    // Must not print anything here, since this is an export.
    if (!isset($_POST['download_csv'])) {
        return;
    }
    // check nonce
    if (
        !isset($_POST['download_csv_nonce'])
        || !wp_verify_nonce($_POST['download_csv_nonce'], 'download_csv')
    ) {
        wp_nonce_ays('');
    }

    $form_id = 1;
    if (isset($_POST['form-id'])) {
        $form_id = $_POST['form-id'];
    }

    global $wpdb;

    // Set response headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="form_' . $form_id . '_export.csv"');
    header("Pragma: no-cache");
    header("Expires: 0");

    // clean out other output buffers
    ob_end_clean();

    $fp = fopen('php://output', 'w');

    $table_Name = $wpdb->prefix . 'fluentform_entry_details';

    // get column names
    $columns = $wpdb->get_col("SHOW columns FROM " . $table_Name);

    $header_row = array();

    foreach ($columns as $column) {
        array_push($header_row, $column);
    }

    // write the header
    fputcsv($fp, $header_row);

    // retrieve table data
    $sql_query = $wpdb->prepare("SELECT * FROM `$table_Name` WHERE form_id = %d", $form_id);
    $rows = $wpdb->get_results($sql_query, ARRAY_A);
    if (!empty($rows)) {
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
    }

    fclose($fp);
    exit; // Stop export

}
