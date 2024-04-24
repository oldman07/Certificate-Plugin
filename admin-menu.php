<?php

function display_custom_form() {
    echo '
    <form action="" method="post">
        ID: <input type="text" name="id" required><br>
        Serial No: <input type="text" name="serial_no" required><br>
        Name: <input type="text" name="name" required><br>
        <input type="submit" name="submit" value="Submit">
    </form>';
}
function handle_form_submission() {
    global $wpdb;
    if (isset($_POST['submit'])) {
        $id = sanitize_text_field($_POST['id']);
        $serial_no = sanitize_text_field($_POST['serial_no']);
        $name = sanitize_text_field($_POST['name']);
        
        $table_name = $wpdb->prefix . 'data'; // Assuming 'info' is the table name
        $wpdb->insert($table_name, array(
            'id' => $id,
            'serial_no' => $serial_no,
            'name' => $name
        ));
    }
}
