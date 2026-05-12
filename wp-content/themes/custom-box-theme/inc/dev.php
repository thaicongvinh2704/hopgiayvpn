<?php
/**
 * Local development helpers.
 */

function custom_box_is_local_development() {
    $host = isset($_SERVER['HTTP_HOST']) ? wp_unslash($_SERVER['HTTP_HOST']) : '';

    return in_array($host, array('localhost', '127.0.0.1', 'localhost:80', '127.0.0.1:80'), true)
        || str_starts_with($host, 'localhost:')
        || str_starts_with($host, '127.0.0.1:');
}

function custom_box_send_local_no_cache_headers() {
    if (!custom_box_is_local_development() || headers_sent()) {
        return;
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
}
add_action('send_headers', 'custom_box_send_local_no_cache_headers');

function custom_box_local_no_cache_meta() {
    if (!custom_box_is_local_development()) {
        return;
    }
    ?>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?php
}
add_action('wp_head', 'custom_box_local_no_cache_meta', 1);
