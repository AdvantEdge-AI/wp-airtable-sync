<?php
/*
Plugin Name: WP Airtable Sync
Description: Syncs data from WordPress to Airtable.
Version: 1.0
Author: <a href="https://advantedgeai.com" target="_blank">AdvantEdge AI</a>
*/

// Temporary debug settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Plugin activation hook for creating field mapping table
register_activation_hook(__FILE__, 'wp_airtable_sync_create_table');

function wp_airtable_sync_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_airtable_field_mapping';

    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        wp_field_name varchar(255) NOT NULL,
        airtable_field_name varchar(255) NOT NULL,
        PRIMARY KEY  (wp_field_name)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Plugin uninstall hook for removing settings from the WP options table and dropping the field mapping table
register_uninstall_hook(__FILE__, 'wp_airtable_sync_uninstall');

function wp_airtable_sync_uninstall() {
    global $wpdb;
    delete_option('airtable_api_key');
    delete_option('airtable_user_base_id');
    delete_option('airtable_user_table_name');

    $table_name = $wpdb->prefix . 'wp_airtable_field_mapping';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}

// Enqueue scripts and styles
function enqueue_admin_scripts() {
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('wp-airtable-sync-styles', plugin_dir_url(__FILE__) . 'styles.css');
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// Add admin menu
function add_admin_menu() {
    add_menu_page('WP Airtable Sync', 'WP Airtable Sync', 'manage_options', 'wp-airtable-sync', 'wp_airtable_sync_admin_page');
    add_submenu_page('wp-airtable-sync', 'Settings', 'Settings', 'manage_options', 'wp-airtable-sync', 'wp_airtable_sync_admin_page');
    add_submenu_page('wp-airtable-sync', 'Users', 'Users', 'manage_options', 'wp-airtable-sync-users', 'wp_airtable_sync_admin_page');
}

add_action('admin_menu', 'add_admin_menu');

// Register settings
function wp_airtable_sync_register_settings() {
    register_setting('wp-airtable-sync-settings-group', 'airtable_api_key');
    register_setting('wp-airtable-sync-settings-group', 'airtable_user_base_id');
    register_setting('wp-airtable-sync-settings-group', 'airtable_user_table_name');
}
add_action('admin_init', 'wp_airtable_sync_register_settings');

// Combined Function for All Tabs
function wp_airtable_sync_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_airtable_field_mapping';

    // Determine which tab is active
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';

    echo '<div class="wrap">';
    echo '<h2>WP Airtable Sync</h2>';

    // Create admin page tabs
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=wp-airtable-sync&tab=settings" class="nav-tab ' . ($active_tab == 'settings' ? 'nav-tab-active' : '') . '">Settings</a>';
    echo '<a href="?page=wp-airtable-sync&tab=users" class="nav-tab ' . ($active_tab == 'users' ? 'nav-tab-active' : '') . '">Users</a>';
    echo '</h2>';

    // Display content based on the active tab
    if ($active_tab == 'settings') {
        // Content for Settings tab
        echo '<h2>Settings</h2>';

        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            echo '<div id="message" class="updated notice notice-success is-dismissible"><p>Settings saved.</p></div>';
        }

        echo '<form method="post" action="options.php">';
        settings_fields('wp-airtable-sync-settings-group');
        do_settings_sections('wp-airtable-sync-settings-group');

        // Airtable API setting
        echo '<div class="settings-section">';
        echo '<h3>Airtable API</h3>';
        echo '<p>Enter your Airtable Personal Access Token below to connect WordPress to Airtable.</p>';
        echo '<table class="form-table">';
        echo '<tr valign="top">';
        echo '<th scope="row">Personal Access Token</th>';
        echo '<td class="field-instructions">';
        echo '<input type="text" name="airtable_api_key" value="' . esc_attr(get_option('airtable_api_key')) . '" size="90" />';
        echo '<br><a href="https://airtable.com/developers/web/guides/personal-access-tokens" target="_blank">Read the Airtable documentation</a> on how to get a Personal Access Token.';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';

        // Airtable base settings
        echo '<div class="settings-section">';
        echo '<h3>Base ID</h3>';
        echo '<p>Enter your Airtable Base ID so WordPress can connect to it.</p>';
        echo '<table class="form-table">';
        echo '<tr valign="top">';
        echo '<th scope="row">Base ID</th>';
        echo '<td class="field-instructions">';
        echo '<input type="text" name="airtable_user_base_id" value="' . esc_attr(get_option('airtable_user_base_id')) . '" size="30" />';
        echo '<br><a href="https://support.airtable.com/docs/finding-airtable-ids" target="_blank">Read the Airtable documentation</a> on how to determine your Base ID.';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';

        // User Airtable settings
        echo '<div class="settings-section">';
        echo '<h3>User Table Name</h3>';
        echo '<p>Enter your User Table Name to export data to.</p>';
        echo '<table class="form-table">';
        echo '<tr valign="top">';
        echo '<th scope="row">User Table Name</th>';
        echo '<td class="field-instructions">';
        echo '<input type="text" name="airtable_user_table_name" value="' . esc_attr(get_option('airtable_user_table_name')) . '" size="30" />';
        echo '<br>The name of your table should be displayed in the active tab above the table. Be careful to input the table name exactly as it appears in your Airtable.';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';

        submit_button('Save Changes', 'primary', 'submit', false);
        echo '</form>';
    }

    if ($active_tab == 'settings') {
        // ... Settings tab content
    } elseif ($active_tab == 'users') {
        // Define the $fields array for the Users tab
        $fields = [
            'Email' => 'email',
            'Username' => 'username',
            'User ID' => 'user_id',
            'First Name' => 'first_name',
            'Last Name' => 'last_name',
            'Phone 1' => 'phone_1',
            'Phone 2' => 'phone_2',
            'Position' => 'position',
            'Company' => 'company',
            'Industry' => 'industry',
            'Company Size' => 'company_size',
            'Locale' => 'locale',
            'Link' => 'link',
            // Add other fields as necessary
        ];

        echo '<h2>Users</h2>';

        // Check for form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log('Form submitted: ' . print_r($_POST, true));
            foreach ($_POST as $wp_field_name => $airtable_field_name) {
                if (array_key_exists($wp_field_name, $fields)) {
                    $airtable_field_name = sanitize_text_field($airtable_field_name);
                    error_log("Attempting to save field mapping for $wp_field_name with value: $airtable_field_name");
                    $result = $wpdb->replace($table_name, [
                        'wp_field_name' => $wp_field_name,
                        'airtable_field_name' => $airtable_field_name,
                    ]);
                    if (false === $result) {
                        error_log("Error: " . $wpdb->last_error);
                        error_log("Last Query: " . $wpdb->last_query);
                    } else {
                        error_log("Query Executed: " . $wpdb->last_query);
                        error_log("Field mapping saved for $wp_field_name: $airtable_field_name");
                    }
                }
            }
        }
        
        // Field mapping form
        echo '<div class="settings-section">';
        echo '<h3>User Field Mapping</h3>';
        echo '<p>Enter the Airtable field name (right) corresponding to each WordPress user field (left). Be sure to input the field names exactly as they appear in your Airtable.</p>';
        echo '<form method="post" action="">';
        echo '<table class="form-table">';
        foreach ($fields as $label => $wp_field_name) {
            $airtable_field_name = $wpdb->get_var("SELECT airtable_field_name FROM $table_name WHERE wp_field_name = '$wp_field_name'");
            echo "<tr>";
            echo "<th scope='row'><label for='$wp_field_name'>$label ($wp_field_name): </label></th>";
            echo "<td><input type='text' id='$wp_field_name' name='$wp_field_name' value='" . esc_attr($airtable_field_name) . "'></td>";
            echo "</tr>";
        }
        echo '</table>';
        submit_button('Save Field Mapping');
        echo '</form>';
        echo '</div>';
    }

    echo '</div>'; // End wrap
}

// Add settings link on the plugins list page
function add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wp-airtable-sync">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
?>
