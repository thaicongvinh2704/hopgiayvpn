<?php
/**
 * Frontend asset loading.
 */

function custom_box_enqueue_assets() {
    wp_enqueue_style(
        'main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        '4.7'
    );

    wp_enqueue_style(
        'responsive-style',
        get_template_directory_uri() . '/assets/css/responsive.css',
        array('main-style'),
        '4.7'
    );

    wp_enqueue_script(
        'main-js',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        '1.1',
        true
    );
}
add_action('wp_enqueue_scripts', 'custom_box_enqueue_assets');

function load_fontawesome() {
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
        array(),
        '6.5.0'
    );
}
add_action('wp_enqueue_scripts', 'load_fontawesome');
