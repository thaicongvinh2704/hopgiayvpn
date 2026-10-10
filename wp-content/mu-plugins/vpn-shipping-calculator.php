<?php
/**
 * Plugin Name: VPN Shipping Box Calculator
 * Description: Scoped calculator assets for the imported shipping-box guide.
 */
defined('ABSPATH') || exit;

function vpn_shipping_calculator_article(): bool {
    return is_singular('post') && get_post_meta(get_the_ID(), '_vpn_shipping_guide_import', true) === '20261010';
}

add_action('wp_enqueue_scripts', static function () {
    if (!vpn_shipping_calculator_article()) { return; }
    $base = __DIR__ . '/vpn-shipping-calculator/';
    $url = content_url('/mu-plugins/vpn-shipping-calculator/');
    wp_enqueue_style('vpn-shipping-calculator', $url . 'calculator.css', [], (string) filemtime($base . 'calculator.css'));
    wp_enqueue_script('vpn-shipping-calculator', $url . 'calculator.js', [], (string) filemtime($base . 'calculator.js'), true);
});

// The theme applies wp_kses_post at render time. Permit only inert form controls
// on this marked article; all executable code remains in the enqueued JS asset.
function vpn_shipping_calculator_allowed_html($tags, $context) {
    if ($context !== 'post') { return $tags; }
    $common = ['id' => true, 'class' => true, 'name' => true, 'aria-label' => true];
    $tags['input'] = $common + ['type' => true, 'min' => true, 'step' => true, 'value' => true, 'placeholder' => true];
    $tags['select'] = $common;
    $tags['option'] = ['value' => true, 'selected' => true];
    $tags['textarea'] = $common + ['readonly' => true];
    $tags['label'] = $common + ['for' => true];
    $tags['section'] = ($tags['section'] ?? []) + $common + ['aria-label' => true];
    return $tags;
}

add_filter('wp_kses_allowed_html', static function ($tags, $context) {
    return vpn_shipping_calculator_article() ? vpn_shipping_calculator_allowed_html($tags, $context) : $tags;
}, 10, 2);
