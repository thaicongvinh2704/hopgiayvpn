<?php
/** Category-specific wholesale bakery landing; catalog remains available on request. */
defined('ABSPATH') || exit;

function vpn_kraft_bakery_is_landing(): bool {
    return vpn_kraft_is_category() && max(1, (int) get_query_var('paged')) === 1
        && !(isset($_GET['orderby']) && is_string($_GET['orderby']))
        && !(isset($_GET['view']) && is_string($_GET['view']) && 'catalog' === wp_unslash($_GET['view']));
}

function vpn_kraft_bakery_products(): array {
    static $products;
    if (null !== $products) { return $products; }
    $products = array();
    $choices = array(
        array('custom-brown-kraft-bakery-paper-bags', 'sos', 'Brown kraft bakery bags', 'Open-top bags for pastries and bakery counter orders.'),
        array('custom-square-bottom-bakery-paper-bags', 'sos', 'Square-bottom bakery bags', 'A flat base and side gussets for counter packing.'),
        array('custom-brown-kraft-grocery-bags', 'sos', 'Kraft SOS & grocery bags', 'A handle-free format for grocery and food-service orders.'),
        array('custom-printed-baguette-paper-bags', 'bread', 'Baguette paper bags', 'Long, narrow bags sized around baguettes and French bread.'),
        array('custom-kraft-side-gusset-bread-bags', 'bread', 'Side-gusset bread bags', 'Expandable sides for your loaf and packing method.'),
        array('custom-printed-flat-bread-paper-bags', 'bread', 'Flat paper bread bags', 'A simple sleeve format with custom dimensions and print.'),
        array('custom-paper-bread-bags-with-window', 'bread', 'Paper bread bags with a window', 'A viewing window positioned around your product and artwork.'),
    );
    foreach ($choices as $choice) {
        $post = get_page_by_path($choice[0], OBJECT, 'product');
        $product = $post && 'publish' === $post->post_status ? wc_get_product($post->ID) : false;
        if (!$product || !$product->get_image_id()) { continue; }
        $products[] = array('product' => $product, 'group' => $choice[1], 'label' => $choice[2], 'description' => $choice[3]);
    }
    return $products;
}

function vpn_kraft_bakery_image(array $item, bool $priority = false): string {
    return wp_get_attachment_image($item['product']->get_image_id(), 'woocommerce_single', false, array(
        'alt' => get_the_title($item['product']->get_id()), 'loading' => $priority ? 'eager' : 'lazy',
        'fetchpriority' => $priority ? 'high' : 'auto', 'decoding' => 'async',
        'sizes' => '(max-width: 600px) 45vw, (max-width: 1000px) 30vw, 290px',
    ));
}

add_filter('template_include', static function ($template) {
    return vpn_kraft_bakery_is_landing()
        ? get_template_directory() . '/template-parts/woocommerce/kraft-bakery-landing.php' : $template;
}, 99);

add_filter('body_class', static function ($classes) {
    if (vpn_kraft_bakery_is_landing()) { $classes[] = 'vpn-kraft-bakery-page'; }
    return $classes;
});

add_action('wp_enqueue_scripts', static function () {
    if (!vpn_kraft_bakery_is_landing()) { return; }
    $base = get_template_directory_uri();
    foreach (array('css/kraft-bakery-landing.css', 'js/kraft-bakery-landing.js') as $asset) {
        $version = (string) filemtime(get_template_directory() . '/assets/' . $asset);
        if (str_ends_with($asset, '.css')) {
            wp_enqueue_style('vpn-kraft-bakery', $base . '/assets/' . $asset, array('main-style', 'responsive-style', 'vpn-kraft-workshop'), $version);
        } else {
            wp_enqueue_script('vpn-kraft-bakery', $base . '/assets/' . $asset, array('main-js'), $version, true);
        }
    }
    wp_localize_script('vpn-kraft-bakery', 'vpnKraftBakery', array(
        'bootstrap' => rest_url('vpn-chat/v1/bootstrap'), 'inbox' => rest_url('vpn-chat/v1/guest/email-quote'),
        'available' => class_exists('VPN_Chat_Email_Quote') && class_exists('VPN_Chat_Settings') && VPN_Chat_Settings::ready(),
        'popupScope' => substr(md5(home_url('/')), 0, 12),
        'cookiePath' => wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/',
        'measure' => 'production' === wp_get_environment_type(),
        // A direct Ads goal must be confirmed; do not copy conflicting IDs from other landing pages.
        'adsGoal' => (string) apply_filters('vpn_kraft_bakery_ads_conversion', ''),
    ));
}, 40);
