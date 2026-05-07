<?php
/**
 * Theme bootstrap.
 *
 * Keep this file small: feature code lives in /inc.
 */

$custom_box_inc_files = array(
    'inc/setup.php',
    'inc/enqueue.php',
    'inc/admin-category-fields.php',
);

foreach ($custom_box_inc_files as $custom_box_inc_file) {
    require_once get_template_directory() . '/' . $custom_box_inc_file;
}
