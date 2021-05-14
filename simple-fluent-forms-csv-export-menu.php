<?php
/**
 * Adds menu entry for Fluent Forms Csv Export.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

add_action('admin_menu', 'ffse_menu');

/**
 * Function appending entry to Wordpress menu.
 */
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

/**
 * Function rendering the admin menu page.
 */
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

