# WP Airtable Sync
The plugin is designed to synchronize data between WordPress and Airtable, with functionalities for setting up database tables, handling plugin uninstallation, and including necessary scripts and styles. View the readme.txt file for a more detailed explanation of the plugin code.

# Debug Settings
The code sets PHP error reporting settings to display all errors. This is for debugging purposes. The lines ini_set('display_errors', 1); and ini_set('display_startup_errors', 1); enable the display of errors, while error_reporting(E_ALL); ensures all types of errors, warnings, and notices are shown. This is only here during development and will be removed before completion of the plugin.

#Plugin Activation Hook
The register_activation_hook function is used. This hook is triggered when the plugin is activated in WordPress. It's connected to a function named wp_airtable_sync_create_table.

# Table Creation Function
The wp_airtable_sync_create_table function is defined, which involves creating a database table for the plugin. This function uses the global $wpdb object, which is WordPress’s database abstraction layer and provides a way to interact with the database.

# Table Creation Logic
The wp_airtable_sync_create_table function creates a new table named wp_airtable_field_mapping in the WordPress database. The table includes two columns: wp_field_name and airtable_field_name, both of type varchar(255). The wp_field_name column is set as the primary key. The table creation SQL statement uses the WordPress database charset and collation settings, ensuring compatibility with the existing WordPress installation.

# Plugin Uninstall Hook
The plugin defines a register_uninstall_hook function, which is executed when the plugin is uninstalled. This hook is linked to a function named wp_airtable_sync_uninstall.

# Uninstall Function
The wp_airtable_sync_uninstall function handles cleanup tasks upon plugin uninstallation. It deletes several options from the WordPress options table: airtable_api_key, airtable_user_base_id, and airtable_user_table_name. These options store configuration settings for the plugin. Additionally, the function drops the wp_airtable_field_mapping table from the database, removing all data related to the plugin.

# Enqueue Scripts and Styles
The function enqueue_admin_scripts is defined for enqueuing JavaScript and CSS files.
