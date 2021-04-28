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
    echo '<div id="message" class="updated fade"><p>'
        . 'Starting export.' . '</p></div>';

    global $wpdb;

    // Use headers so the data goes to a file and not displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');

    // clean out other output buffers
    ob_end_clean();

    $fp = fopen('php://output', 'w');

    // retrieve any table data desired. Members is an example
    $table_Name = $wpdb->prefix . 'fluentform_entry_details';

    // get headers
    $columns = $wpdb->get_col("SHOW columns FROM ".$table_Name);

    // CSV/Excel header label
    $header_row = array();

    foreach ($columns as $column) {
        array_push($header_row, $column[0]);
    }


    //write the header
    fputcsv($fp, $header_row);

    // retrieve any table data desired. Members is an example
    $sql_query = $wpdb->prepare("SELECT * FROM $table_Name", 1);
    $rows = $wpdb->get_results($sql_query, ARRAY_A);
    if (!empty($rows)) {
        foreach ($rows as $Record) {
            $OutputRecord = array($Record['Email'],
                $Record['FirstName'],
                $Record['LastName']);
            fputcsv($fp, $OutputRecord);
        }
    }

    fclose($fp);
    exit;                // Stop any more exporting to the file

}


function ffse_render_plugin_settings_page()
{
    // General check for user permissions.
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient pilchards to access this page.'));
    }

    echo '<div class="wrap">';

    echo '<h2>Fluent Forms Csv Export</h2>';

    // Check whether the button has been pressed AND also check the nonce
    if (isset($_POST['export_button']) && check_admin_referer('export_button_clicked')) {
        // the button has been pressed AND we've passed the security check
        ffse_export_button_action();
    }

    echo '<form action="admin.php?page=fluent-forms-csv-export" method="post">';

    // this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
    wp_nonce_field('export_button_clicked');
    echo '<input type="hidden" value="true" name="export_button" />';
    submit_button('Export to CSV');
    echo '</form>';

    echo '</div>';


}

