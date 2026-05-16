<?php
/**
 * Frontend asset loading.
 */

function custom_box_is_commerce_context() {
    if (is_post_type_archive('product') || is_singular('product') || is_tax(array('product_cat', 'product_tag'))) {
        return true;
    }

    if (function_exists('is_woocommerce') && is_woocommerce()) {
        return true;
    }

    if (function_exists('is_cart') && is_cart()) {
        return true;
    }

    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        return true;
    }

    return is_page(array('products', 'shop'));
}

function custom_box_enqueue_assets() {
    $is_catalog_page = is_page_template('page-catalog.php') || is_page('catalog');
    $main_css_path = get_template_directory() . '/assets/css/main.css';
    $woocommerce_css_path = get_template_directory() . '/assets/css/woocommerce.css';
    $about_css_path = get_template_directory() . '/assets/css/about.css';
    $contact_css_path = get_template_directory() . '/assets/css/contact.css';
    $catalog_css_path = get_template_directory() . '/assets/css/catalog.css';
    $landing_css_path = get_template_directory() . '/assets/css/landing.css';
    $landing_two_css_path = get_template_directory() . '/assets/css/landing-two.css';
    $responsive_css_path = get_template_directory() . '/assets/css/responsive.css';
    $main_js_path = get_template_directory() . '/assets/js/main.js';

    wp_enqueue_style(
        'main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        file_exists($main_css_path) ? filemtime($main_css_path) : '5.8'
    );

    $responsive_deps = array('main-style');

    if (custom_box_is_commerce_context() || $is_catalog_page) {
        wp_enqueue_style(
            'woocommerce-theme-style',
            get_template_directory_uri() . '/assets/css/woocommerce.css',
            array('main-style'),
            file_exists($woocommerce_css_path) ? filemtime($woocommerce_css_path) : '1.0'
        );

        $responsive_deps[] = 'woocommerce-theme-style';
    }

    wp_enqueue_style(
        'responsive-style',
        get_template_directory_uri() . '/assets/css/responsive.css',
        $responsive_deps,
        file_exists($responsive_css_path) ? filemtime($responsive_css_path) : '5.8'
    );

    if (is_page('about')) {
        wp_enqueue_style(
            'about-style',
            get_template_directory_uri() . '/assets/css/about.css',
            array('main-style', 'responsive-style'),
            file_exists($about_css_path) ? filemtime($about_css_path) : '1.0'
        );
    }

    if (is_page('contact')) {
        wp_enqueue_style(
            'contact-style',
            get_template_directory_uri() . '/assets/css/contact.css',
            array('main-style', 'responsive-style'),
            file_exists($contact_css_path) ? filemtime($contact_css_path) : '1.0'
        );
    }

    if ($is_catalog_page) {
        wp_enqueue_style(
            'catalog-style',
            get_template_directory_uri() . '/assets/css/catalog.css',
            array('main-style', 'woocommerce-theme-style', 'responsive-style'),
            file_exists($catalog_css_path) ? filemtime($catalog_css_path) : '1.0'
        );
    }

    if (is_page_template('page-landing-packaging.php') || is_page('packaging-landing')) {
        wp_enqueue_style(
            'landing-style',
            get_template_directory_uri() . '/assets/css/landing.css',
            array('main-style', 'responsive-style'),
            file_exists($landing_css_path) ? filemtime($landing_css_path) : '1.0'
        );
    }

    if (is_page_template('page-landing-packaging-two.php') || is_page('packaging-landing-two')) {
        wp_enqueue_style(
            'landing-two-style',
            get_template_directory_uri() . '/assets/css/landing-two.css',
            array('main-style', 'responsive-style'),
            file_exists($landing_two_css_path) ? filemtime($landing_two_css_path) : '1.0'
        );
    }

    wp_enqueue_script(
        'main-js',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        file_exists($main_js_path) ? filemtime($main_js_path) : '1.2',
        true
    );
    wp_script_add_data('main-js', 'defer', true);
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

function custom_box_dequeue_non_critical_assets() {
    if (!custom_box_is_commerce_context()) {
        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('wc-blocks-vendors-style');
        wp_dequeue_style('wc-blocks-packages-style');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-inline');

        wp_dequeue_script('wc-add-to-cart');
        wp_dequeue_script('wc-cart-fragments');
        wp_dequeue_script('wc-jquery-blockui');
        wp_dequeue_script('wc-js-cookie');
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('sourcebuster-js');
        wp_dequeue_script('wc-order-attribution');
    }

    if (is_front_page() || is_page(array('about', 'contact', 'catalog', 'packaging-landing', 'packaging-landing-two', 'products'))) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('classic-theme-styles');
        wp_dequeue_style('global-styles');
    }
}

function custom_box_optimize_non_critical_assets() {
    custom_box_dequeue_non_critical_assets();
}
add_action('wp_enqueue_scripts', 'custom_box_optimize_non_critical_assets', 100);
add_action('wp_head', 'custom_box_dequeue_non_critical_assets', 0);
add_action('wp_print_styles', 'custom_box_dequeue_non_critical_assets', 100);
add_action('wp_print_scripts', 'custom_box_dequeue_non_critical_assets', 100);

function custom_box_disable_emoji_assets() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
    remove_action('wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles');
    remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
}
add_action('init', 'custom_box_disable_emoji_assets');

function custom_box_preload_hero_assets() {
    if (!is_front_page() && !is_home()) {
        return;
    }

    printf(
        '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
        esc_url(get_template_directory_uri() . '/assets/images/product-banner1.png')
    );
}
add_action('wp_head', 'custom_box_preload_hero_assets', 1);

function custom_box_non_blocking_styles($html, $handle, $href, $media) {
    $blocked_handles = array(
        'wc-blocks-style',
        'wc-blocks-vendors-style',
        'wc-blocks-packages-style',
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'woocommerce-general',
        'wp-block-library',
        'classic-theme-styles',
        'global-styles',
    );

    if (in_array($handle, $blocked_handles, true) && (!custom_box_is_commerce_context() || 'wp-block-library' === $handle || 'classic-theme-styles' === $handle || 'global-styles' === $handle)) {
        return '';
    }

    if ('font-awesome' !== $handle) {
        return $html;
    }

    $href = esc_url($href);

    return '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n"
        . '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" crossorigin="anonymous">' . "\n"
        . '<noscript><link rel="stylesheet" href="' . $href . '" media="all"></noscript>' . "\n";
}
add_filter('style_loader_tag', 'custom_box_non_blocking_styles', 10, 4);

function custom_box_theme_favicon() {
    $favicon_url = get_template_directory_uri() . '/assets/images/favicon.jpg';

    printf(
        '<link rel="icon" href="%1$s" type="image/jpeg">' . "\n"
        . '<link rel="shortcut icon" href="%1$s" type="image/jpeg">' . "\n"
        . '<link rel="apple-touch-icon" href="%1$s">' . "\n",
        esc_url($favicon_url)
    );
}
add_action('wp_head', 'custom_box_theme_favicon', 2);
