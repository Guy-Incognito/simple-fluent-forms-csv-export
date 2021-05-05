<?php
/**
 * Plugin Name: Simple Fluent Forms CSV Export
 * Description: Export Fluent Form Data to CSV
 * Author:      Georg Moser
 * Author URI:  https://github.com/Guy-Incognito/simple-fluent-forms-csv-export
 * License URI: -
 * Requires at least: 5.7
 * Requires PHP:      7.2
 * Version:           0.0.1
 */

add_action('admin_menu', 'ffse_menu');
add_action("admin_init", "ffse_export_button_action");

function ffse_menu()
{
    add_menu_page(
        'Fluent Forms Csv Export', // Html Page title
        'Fluent Forms Csv Export', // Menu title
        'manage_options', // capability of user required to display this item.
        'fluent-forms-csv-export', // menu slug
        'ffse_render_plugin_settings_page', // The function to be called to output the content for this page.
        'dashicons-media-spreadsheet', // Icon
        3
    );
}

function ffse_export_button_action()
{
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

    // Use headers so the data goes to a file and not displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');
    header("Pragma: no-cache");
    header("Expires: 0");

    // clean out other output buffers
    ob_end_clean();

    $fp = fopen('php://output', 'w');

    // retrieve any table data desired. Members is an example
    $table_Name = $wpdb->prefix . 'fluentform_entry_details';

    // get headers
    $columns = $wpdb->get_col("SHOW columns FROM " . $table_Name);

    // CSV/Excel header label
    $header_row = array();

    foreach ($columns as $column) {
        array_push($header_row, $column);
    }

    //write the header
    fputcsv($fp, $header_row);

    // retrieve any table data desired. Members is an example
    $sql_query = $wpdb->prepare("SELECT * FROM `$table_Name` WHERE form_id = %d", $form_id);
    $rows = $wpdb->get_results($sql_query, ARRAY_A);
    if (!empty($rows)) {
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
    }

    fclose($fp);
    exit;                // Stop any more exporting to the file

}


function ffse_render_plugin_settings_page()
{
    // General check for user permissions.
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient privileges to access this page.'));
    }

    // Get available forms
    global $wpdb;
    $table_Name = $wpdb->prefix . 'fluentform_forms';
    $form_ids = $wpdb->get_results("SELECT id FROM " . $table_Name, ARRAY_A);

    echo '<div class="wrap">';
    echo '<h2>Fluent Forms Csv Export</h2>';

    echo '<form method="post" id="download_form" action="">';
    echo '<label for="form-id">Form Id</label>';
    echo '<select name="form-id" id="form-id">';
    if (!empty($form_ids)) {
        foreach ($form_ids as $form_id) {
            echo '<option value="' . $form_id['id'] . '">' . $form_id['id'] . '</option>';
        }
    }
    echo '</select>';
    echo '<input type="submit" name="download_csv" class="button-primary" value="Export to CSV" />';
    wp_nonce_field('download_csv', 'download_csv_nonce');
    echo '</form>';

    echo '</div>';

}

