<?php
/*
Plugin Name: Custom Form Plugin
Description: A plugin to create a form on the admin side and store data in a separate table.
Version: 1.0
Author: Your Name
*/

function create_custom_form_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'data'; // Assuming 'data' is the table name

    // Check if the table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != null;

    // If the table does not exist, create it
    if (!$table_exists) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            serial_no varchar(255) NOT NULL,
            name varchar(255) NOT NULL,
            training_title varchar(255) NOT NULL,
            date varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Hook the function to the plugin activation
register_activation_hook(__FILE__, 'create_custom_form_table');

function display_custom_form() {
    echo '
    <style>
        .custom-form {
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .custom-form input[type="text"], .custom-form input[type="date"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .custom-form input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 3px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .custom-form input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
    <div class="custom-form">
        <form action="" method="post">
            <label for="id">ID:</label><br>
            <input type="text" id="id" name="id" required><br>
            <label for="serial_no">Serial No:</label><br>
            <input type="text" id="serial_no" name="serial_no" required><br>
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required><br>
            <label for="training_title">Training Title:</label><br>
            <input type="text" id="training_title" name="training_title" required><br>
            <label for="date">Date:</label><br>
            <input type="text" id="date" name="date" required><br>
            <input type="submit" name="submit" value="Submit">
        </form>
    </div>';
}

function handle_form_submission() {
    global $wpdb;
    if (isset($_POST['submit'])) {
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $serial_no = isset($_POST['serial_no']) ? sanitize_text_field($_POST['serial_no']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $training_title = isset($_POST['training_title']) ? sanitize_text_field($_POST['training_title']) : '';
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : ''; // Sanitizing the date field
        
        if ($id && $serial_no && $name && $training_title && $date) {
            $table_name = $wpdb->prefix . 'data'; // Assuming 'data' is the table name
            $wpdb->insert($table_name, array(
                'id' => $id,
                'serial_no' => $serial_no,
                'name' => $name,
                'training_title' => $training_title,
                'date' => $date
            ));

            // Store the date and training_title as transients
            set_transient('custom_form_date', $date, HOUR_IN_SECONDS);
            set_transient('custom_form_training_title', $training_title, HOUR_IN_SECONDS);
        }
    }
}



add_action('admin_menu', 'add_custom_form_menu');
function add_custom_form_menu() {
    add_menu_page('Custom Form', 'Custom Form', 'manage_options', 'custom-form-plugin', 'display_custom_form');
}

add_action('admin_init', 'handle_form_submission');






add_action('admin_menu', 'add_custom_form_page');
function add_custom_form_page() {
    add_menu_page('Certificate Form', 'Certificate Form', 'manage_options', 'custom-form-plugin-form', 'display_custom_form_page');
}

function id_shortcode() {
    $id = get_transient('custom_form_id');
    return $id ? $id : 'ID not found';
}
add_shortcode('custom_form_id', 'id_shortcode');

function serial_no_shortcode() {
    $serial_no = get_transient('custom_form_serial_no');
    // return $ ? $serial_no : 'Serial No not found';
    $output = !empty($serial_no) ? $serial_no : 'No data';
    return '<span style="font-family: \'Poppins\', sans-serif; font-size: 12px;">Serial No: ' . $output . '</span>';
}
add_shortcode('custom_form_serial_no', 'serial_no_shortcode');

function name_shortcode() {
    $name = get_transient('custom_form_name');
    // return $name ? $name : 'Name not found';
    return '<span style="font-family: \'Poppins\', sans-serif; font-size: 24px;">' . $name . '</span>';
}
add_shortcode('custom_form_name', 'name_shortcode');

function date_shortcode() {
    $date = get_transient('custom_form_date');
    // return $date ? $date : 'Date not found';
    return '<span style="font-family: \'Poppins\', sans-serif; font-size: 24px;">' . $date . '</span>';
}
add_shortcode('custom_form_date', 'date_shortcode');

function training_title_shortcode() {
    $training_title = get_transient('custom_form_training_title');
    // return $training_title ? $training_title : 'Training Title not found';
    return '<span style="font-family: \'Poppins\', sans-serif; font-size: 24px;">' . $training_title . '</span>';
}
add_shortcode('custom_form_training_title', 'training_title_shortcode');



function display_custom_form_page() {
    global $wpdb;
    if (isset($_POST['submit'])) {
        $serial_no = isset($_POST['serial_no']) ? sanitize_text_field($_POST['serial_no']) : '';
        if ($serial_no) {
            $table_name = $wpdb->prefix . 'data'; // Assuming 'data' is the table name
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE serial_no = %s", $serial_no));
            if ($results) {
                foreach ($results as $row) {
                    set_transient('custom_form_id', $row->id, 12 * HOUR_IN_SECONDS);
                    set_transient('custom_form_serial_no', $row->serial_no, 12 * HOUR_IN_SECONDS);
                    set_transient('custom_form_name', $row->name, 12 * HOUR_IN_SECONDS);
                }
            } else {
                echo 'No data found for the given Serial No.';
            }
        }
    }
    echo '
    <style>
        .custom-form {
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .custom-form label {
            display: block;
            margin-bottom: 5px;
        }
        .custom-form input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .custom-form input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 3px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .custom-form input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
    <div class="custom-form">
        <form action="" method="post">
            <label for="serial_no">Serial No:</label>
            <input type="text" id="serial_no" name="serial_no" required>
            <input type="submit" name="submit" value="Submit">
        </form>
    </div>';
    
}
function fetch_data_from_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'data'; // Assuming 'data' is the table name

    // Fetch all records from the table
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    return $results;
}
function display_data_in_table() {
    $data = fetch_data_from_db();

    if (!empty($data)) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Serial No</th><th>Name</th></tr>';
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['serial_no']) . '</td>';
            echo '<td>' . esc_html($row['name']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo 'No data found.';
    }
}
function add_custom_form_menu1() {
    add_menu_page('Custom Form Data', 'Custom Form Data', 'manage_options', 'custom-form-data', 'display_data_in_table');
}

add_action('admin_menu', 'add_custom_form_menu1');

function custom_form_search_shortcode() {
    ob_start(); // Start output buffering
    ?>
    <form action="" method="get" id="custom-form-search">
        <input type="text" name="serial_no" placeholder="Enter Serial No" required>
        <input type="submit" value="Search">
    </form>
    <?php
    return ob_get_clean(); // Return the output buffer content
}
add_shortcode('custom_form_search', 'custom_form_search_shortcode');

function handle_custom_form_search() {
    global $wpdb, $custom_search_results; // Declare the global variable
    $table_name = $wpdb->prefix . 'data'; // Assuming 'data' is the table name

    if (isset($_GET['serial_no'])) {
        $serial_no = sanitize_text_field($_GET['serial_no']);
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE serial_no = %s", $serial_no), ARRAY_A);

        if (!empty($results)) {
            // Store the results in the global variable instead of echoing them
            $custom_search_results = '<div class="search-results">';
            $custom_search_results .= '<h2>Search Results</h2>';
            $custom_search_results .= '<table>';
            $custom_search_results .= '<tr><th>ID</th><th>Serial No</th><th>Name</th><th>Download PDF</th></tr>';
            foreach ($results as $row) {
                $custom_search_results .= '<tr>';
                $custom_search_results .= '<td>' . esc_html($row['id']) . '</td>';
                $custom_search_results .= '<td>' . esc_html($row['serial_no']) . '</td>';
                $custom_search_results .= '<td>' . esc_html($row['name']) . '</td>';
                // Construct the download link for the PDF
                $pdf_filename = $row['serial_no'] . '.pdf';
                $pdf_url = site_url('/wp-content/uploads/2024/04/' . $pdf_filename); // Construct the URL based on the serial number
                $custom_search_results .= '<td><a href="' . esc_url($pdf_url) . '" download>Download PDF</a></td>';
                $custom_search_results .= '</tr>';
            }
            $custom_search_results .= '</table>';
            $custom_search_results .= '</div>';
        } else {
            $custom_search_results = '<p>No results found for the given serial number.</p>' ;
        }
    }
}
add_action('template_redirect', 'handle_custom_form_search');


function display_custom_search_results() {
    global $custom_search_results; // Access the global variable
    if (isset($custom_search_results)) {
        return $custom_search_results; // Return the stored results
    } else {
        return ''; // Return an empty string if no results are stored
    }
}
add_shortcode('custom_search_results', 'display_custom_search_results');


