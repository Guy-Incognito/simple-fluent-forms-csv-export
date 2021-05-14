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

    $export_type = 'table';
    if (isset($_POST['export-type'])) {
        $export_type = $_POST['export-type'];
    }

    global $wpdb;

    // Set response headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="form_' . $form_id . '_export.csv"');
    header("Pragma: no-cache");
    header("Expires: 0");

    // clean out other output buffers
    ob_end_clean();


    if (strcmp($export_type, 'table') == 0) {
        ffse_export_button_action_classic($wpdb, $form_id);
    } else {
        ffse_export_button_action_entries($wpdb, $form_id);
    }


}

/**
 * Exports entry_details table from fluentform.
 *
 * @param $wpdb wpdb the wordpress db object.
 * @param $form_id int the form_id to export.
 */
function ffse_export_button_action_classic($wpdb, $form_id)
{

    $table_name = $wpdb->prefix . 'fluentform_entry_details';

    // get column names
    // works only with mysql.
    $columns = $wpdb->get_col("SHOW columns FROM " . $table_name); // returns first column by default.
    $header_row = array();
    foreach ($columns as $column) {
        array_push($header_row, $column);
    }

    $fp = fopen('php://output', 'w');
    // write the header
    fputcsv($fp, $header_row);

    // retrieve table data
    $sql_query = $wpdb->prepare("SELECT * FROM `$table_name` WHERE form_id = %d", $form_id);
    $rows = $wpdb->get_results($sql_query, ARRAY_A);
    if (!empty($rows)) {
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
    }

    fclose($fp);
    exit; // Stop export
}

/**
 * Transform entries to table with field names as header.
 *
 * @param $wpdb wpdb the wordpress db object.
 * @param $form_id int the form_id to export.
 */
function ffse_export_button_action_entries($wpdb, $form_id)
{

    $entry_details_table_name = $wpdb->prefix . 'fluentform_entry_details';

    // get column names
    $columns = $wpdb->get_col("SELECT DISTINCT field_name FROM " . $entry_details_table_name);
    $header_row = array();
    foreach ($columns as $column) {
        array_push($header_row, $column);
    }

    // write the header
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $header_row);

    $submissions_table_name = $wpdb->prefix . 'fluentform_submissions';

    // retrieve table data
    $sql_query = $wpdb->prepare("SELECT response FROM `$submissions_table_name` WHERE form_id = %d AND status not like 'trashed'", $form_id);
    $entries = $wpdb->get_col($sql_query);
    foreach ($entries as $entry) {
        $json_entry = json_decode($entry, true);
        $entry_row = array();
        foreach ($header_row as $header) {
            $entry = $json_entry[$header];
            if (is_array($entry)) {
                $entry = implode(" ", $entry);
            }
            array_push($entry_row, $entry);
        }
        fputcsv($fp, $entry_row);
    }

    fclose($fp);
    exit; // Stop export

}
