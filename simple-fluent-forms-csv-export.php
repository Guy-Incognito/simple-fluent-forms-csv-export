<?php
/**
 * Plugin Name: Simple Fluent Forms CSV Export
 * Description: Export Fluent Form Data to CSV
 * Author:      Georg Moser
 * Author URI:  -
 * License URI: -
 * @version
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

    echo '<div id="message" class="updated fade"><p>'
        . 'Starting export.' . '</p></div>';

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
    $sql_query = "SELECT * FROM " . $table_Name;
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

    echo '<div class="wrap">';
    echo '<h2>Fluent Forms Csv Export</h2>';

    echo '<form method="post" id="download_form" action="">';
    echo '<input type="submit" name="download_csv" class="button-primary" value="Export to CSV" />';
    echo '</form>';

    echo '</div>';

}

