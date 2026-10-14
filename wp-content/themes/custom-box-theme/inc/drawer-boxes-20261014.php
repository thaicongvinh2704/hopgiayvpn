<?php
/** Owner-approved Drawer Boxes presentation. No frontend database writes. */
defined('ABSPATH') || exit;

function vpn_drawer_data(): array {
    static $data;
    if (null === $data) {
        $data = json_decode((string) file_get_contents(__DIR__ . '/drawer-boxes-20261014.json'), true);
        $data = is_array($data) ? $data : array();
    }
    return $data;
}
function vpn_drawer_is_category(): bool {
    return function_exists('is_product_category') && is_product_category('drawer-boxes');
}
function vpn_drawer_product_profile(int $id = 0): array {
    $id = $id ?: (int) get_queried_object_id();
    if ('product' !== get_post_type($id)) { return array(); }
    $slug = get_post_field('post_name', $id);
    foreach (vpn_drawer_data()['products'] ?? array() as $profile) {
        if ($slug === $profile['slug']) { return $profile; }
    }
    return array();
}
function vpn_drawer_context(): bool {
    return vpn_drawer_is_category() || (is_singular('product') && (bool) vpn_drawer_product_profile());
}
function vpn_drawer_category_canonical($url): string {
    if (!vpn_drawer_is_category()) { return (string) $url; }
    $term = get_queried_object();
    $base = get_term_link($term);
    if (is_wp_error($base)) { return (string) $url; }
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    return $paged > 1 ? trailingslashit($base) . 'page/' . $paged . '/' : $base;
}
foreach (array('rank_math/frontend/canonical', 'rank_math/opengraph/facebook/og_url') as $hook) {
    add_filter($hook, 'vpn_drawer_category_canonical', 80);
}
function vpn_drawer_quote_url(string $slug = 'custom', bool $sample = false): string {
    $params = array('drawer_box' => $slug);
    if ($sample) { $params['drawer_sample'] = '1'; }
    return add_query_arg($params, home_url('/contact/')) . '#quote';
}
function vpn_drawer_quote_context(): array {
    if (!is_page('contact') || !isset($_GET['drawer_box']) || !is_string($_GET['drawer_box'])) { return array(); }
    $slug = sanitize_key(wp_unslash($_GET['drawer_box']));
    $name = '';
    if ('custom' === $slug) { $name = 'Custom Drawer Box Packaging'; }
    foreach (vpn_drawer_data()['products'] ?? array() as $p) {
        if ($p['slug'] === $slug) { $name = $p['name']; break; }
    }
    if ('' === $name) { return array(); }
    if (isset($_GET['drawer_sample']) && '1' === $_GET['drawer_sample']) { $name .= ' - custom sample enquiry'; }
    return array('product_value' => $name, 'quote_source' => 'drawer_boxes', 'form_location' => 'drawer-category',
        'current_page_url' => home_url('/products/drawer-boxes/'));
}
function vpn_drawer_listing_ids(): array {
    static $ids;
    if (null !== $ids) { return $ids; }
    $eligible = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'fields' => 'ids',
        'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC',
        'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => 'drawer-boxes'))));
    $first = array();
    foreach (vpn_drawer_data()['models'] ?? array() as $model) {
        $post = get_page_by_path($model['slug'], OBJECT, 'product');
        if ($post && in_array($post->ID, $eligible, true)) { $first[] = $post->ID; }
    }
    $ids = array_values(array_unique(array_merge($first, $eligible)));
    return $ids;
}
function vpn_drawer_card_image(int $id): string {
    $p = vpn_drawer_product_profile($id);
    if (empty($p['images'])) { return ''; }
    $small = $p['images'][0]; $large = end($p['images']);
    return sprintf('<img src="%s" srcset="%s %dw, %s %dw" width="%d" height="%d" alt="%s" loading="lazy" decoding="async" sizes="(max-width: 379px) calc(100vw - 36px), (max-width: 767px) calc(50vw - 28px), 360px">',
        esc_url(get_template_directory_uri() . '/' . $small['file']), esc_url(get_template_directory_uri() . '/' . $small['file']), $small['width'],
        esc_url(get_template_directory_uri() . '/' . $large['file']), $large['width'], $large['width'], $large['height'], esc_attr($p['name']));
}
function vpn_drawer_meta_value($value, string $field) {
    if (vpn_drawer_is_category()) {
        $data = vpn_drawer_data();
        $value = $data[$field];
        if ('title' === $field) {
            $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
            if ($paged > 1) { $value = str_replace(' | Vietnam', ' - Page ' . $paged . ' | Vietnam', $value); }
        }
    } elseif (is_singular('product')) {
        $profile = vpn_drawer_product_profile();
        if ($profile) { $value = $profile[$field]; }
    }
    return $value;
}
foreach (array('pre_get_document_title', 'rank_math/frontend/title', 'rank_math/opengraph/facebook/title', 'rank_math/opengraph/twitter/title') as $hook) {
    add_filter($hook, static fn($value) => vpn_drawer_meta_value($value, 'title'), 80);
}
foreach (array('rank_math/frontend/description', 'rank_math/opengraph/facebook/description', 'rank_math/opengraph/twitter/description') as $hook) {
    add_filter($hook, static fn($value) => vpn_drawer_meta_value($value, 'description'), 80);
}
add_filter('rank_math/json_ld', static function ($data) {
    if (!vpn_drawer_is_category()) { return $data; }
    foreach ($data as &$node) {
        if (is_array($node) && in_array('CollectionPage', (array) ($node['@type'] ?? array()), true)) {
            $node['name'] = vpn_drawer_data()['heading'];
            $node['description'] = vpn_drawer_data()['description'];
            $node['url'] = vpn_drawer_category_canonical($node['url'] ?? '');
        }
    }
    unset($node);
    return $data;
}, 80);
add_filter('body_class', static function ($classes) {
    if (vpn_drawer_context() || vpn_drawer_quote_context()) { $classes[] = 'vpn-drawer-context'; }
    if (vpn_drawer_is_category()) { $classes[] = 'vpn-drawer-category'; }
    return $classes;
});
add_action('wp_enqueue_scripts', static function () {
    if (!vpn_drawer_context() && !vpn_drawer_quote_context()) { return; }
    $path = get_template_directory() . '/assets/css/drawer-boxes-20261014.css';
    $deps = wp_style_is('woocommerce-theme-style', 'registered') ? array('woocommerce-theme-style') : array();
    wp_enqueue_style('vpn-drawer-style', get_template_directory_uri() . '/assets/css/drawer-boxes-20261014.css', $deps, filemtime($path));
    if (vpn_drawer_is_category()) {
        $ui = '/assets/css/drawer-boxes-ui-20261014.css';
        wp_enqueue_style('vpn-drawer-ui', get_template_directory_uri() . $ui, array('vpn-drawer-style'), filemtime(get_template_directory() . $ui));
    }
}, 30);

function vpn_drawer_product_faq(array $p): string {
    $items = array(
        array('What can I customize for this box?', $p['detail'] . ' Size, material, insert, printing and finishing can be developed around your design and packed product.'),
        array('What is the minimum order?', 'The general minimum is 1,000 units. Send the dimensions, quantity and delivery country for a project quotation.'),
        array('How are custom samples priced?', 'Samples are made to your design requirements and the sample fee depends on the design. Orders over 5,000 pcs qualify for a refund of 50% of the sample fee. Design support is available.'),
        array('How long do samples and production take?', 'Sampling takes 7–15 days depending on the order. Production takes 7–45 days after sample approval, depending on the order. The schedule is confirmed for your project; transit time is separate.'),
        array('How is the packaging checked and packed?', 'Quality is checked at each production stage. Finished boxes are packed in cartons or shrink-wrapped bundles according to the order requirements.'),
    );
    $html = '<section class="vpn-drawer-page vpn-drawer-product-faq"><div class="container"><h2>Custom order and sample questions</h2><div class="vpn-drawer-faq">';
    foreach ($items as [$question, $answer]) { $html .= '<details><summary>' . esc_html($question) . '</summary><p>' . esc_html($answer) . '</p></details>'; }
    return $html . '</div><p>Share your email and packaging brief so sales can follow up with the options and quotation for your project.</p></div></section>';
}
