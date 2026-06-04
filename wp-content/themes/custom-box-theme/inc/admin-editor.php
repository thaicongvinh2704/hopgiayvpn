<?php
/**
 * Admin editor customizations.
 *
 * @package Custom_Box_Theme
 */

add_filter('tiny_mce_before_init', 'custom_box_tinymce_fontsize_formats', 999);

/**
 * Adds percentage font-size presets to the Classic Editor dropdown.
 *
 * @param array $init TinyMCE init settings.
 * @return array
 */
function custom_box_tinymce_fontsize_formats($init)
{
    $init['fontsize_formats'] = implode(' ', array(
        '8pt',
        '10pt',
        '12pt',
        '14pt',
        '18pt',
        '24pt',
        '36pt',
        '80%',
        '90%',
        '100%',
        '110%',
        '120%',
        '130%',
        '140%',
        '150%',
        '175%',
        '200%',
    ));

    return $init;
}
