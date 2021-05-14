<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area.
 *
 * Plugin Name: Simple Fluent Forms CSV Export
 * Description: Export Fluent Form Data to CSV
 * Author:      Georg Moser
 * Author URI:  https://github.com/Guy-Incognito/simple-fluent-forms-csv-export
 * License URI: -
 * Requires at least: 5.7
 * Requires PHP:      7.2
 * Version:           0.0.2
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . '/simple-fluent-forms-csv-export-menu.php';
require_once plugin_dir_path(__FILE__) . '/simple-fluent-forms-csv-export-function.php';

