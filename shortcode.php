<?php

function custom_form_search_shortcode($atts) {
    ob_start();
    ?>
    <form method="get" action="">
        <input type="text" name="search" placeholder="Search">
        <input type="submit" value="Search">
    </form>
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'info';
    if (isset($_GET['search'])) {
        $search = sanitize_text_field($_GET['search']);
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE name LIKE %s", '%' . $wpdb->esc_like($search) . '%'));
        if ($results) {
            foreach ($results as $result) {
                echo '<p>' . esc_html($result->name) . '</p>';
            }
        } else {
            echo '<p>No results found.</p>';
        }
    }
    return ob_get_clean();
}
add_shortcode('custom_form_search', 'custom_form_search_shortcode');