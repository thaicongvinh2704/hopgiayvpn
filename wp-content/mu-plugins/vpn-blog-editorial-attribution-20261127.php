<?php
/**
 * Plugin Name: VPN Packaging Editorial Attribution
 * Description: Identifies VPN's editorial team on the three reviewed sourcing guides.
 */
defined('ABSPATH') || exit;

function vpn_blog_editorial_attribution_applies(): bool {
    if (!is_singular('post')) {
        return false;
    }
    $id = get_queried_object_id();
    return $id > 0 && get_post_meta($id, '_vpn_blog_evidence_20261127', true) === '1';
}

add_filter('the_author', static function ($name) {
    return vpn_blog_editorial_attribution_applies() && get_the_ID() === get_queried_object_id()
        ? 'VPN Packaging Editorial Team'
        : $name;
});

add_filter('get_the_author_job_title', static function ($title) {
    return vpn_blog_editorial_attribution_applies() && get_the_ID() === get_queried_object_id()
        ? 'Buyer guides from VPN Packaging'
        : $title;
});

add_filter('get_the_author_description', static function ($bio) {
    return vpn_blog_editorial_attribution_applies() && get_the_ID() === get_queried_object_id()
        ? 'The VPN Packaging Editorial Team prepares B2B packaging guides using the factory’s published material and cited technical sources. Product illustrations explain construction and fit. Dimensions and performance are confirmed on a project sample.'
        : $bio;
});

add_filter('author_link', static function ($url, $author_id) {
    $id = get_queried_object_id();
    return vpn_blog_editorial_attribution_applies() && (int) get_post_field('post_author', $id) === (int) $author_id
        ? home_url('/about/#about-factory')
        : $url;
}, 10, 2);

add_filter('rank_math/json_ld', static function ($data) {
    if (!is_array($data) || !vpn_blog_editorial_attribution_applies()) {
        return $data;
    }
    foreach ($data as &$entity) {
        $types = is_array($entity) ? ($entity['@type'] ?? []) : [];
        if (array_intersect(array('Article', 'BlogPosting', 'NewsArticle'), (array) $types)) {
            $entity['author'] = [
                '@type' => 'Organization',
                'name'  => 'VPN Packaging Editorial Team',
                'url'   => home_url('/about/#about-factory'),
            ];
        }
    }
    unset($entity);
    return $data;
}, 90);

add_filter('body_class', static function (array $classes): array {
    if (vpn_blog_editorial_attribution_applies()) {
        $classes[] = 'vpn-buyer-guide';
    }
    return $classes;
});

add_action('wp_enqueue_scripts', static function (): void {
    if (!vpn_blog_editorial_attribution_applies()) {
        return;
    }
    $relative = '/assets/css/blog-buyer-guides-20261127.css';
    $file = get_theme_file_path($relative);
    if (is_file($file)) {
        wp_enqueue_style('vpn-blog-buyer-guides', get_theme_file_uri($relative), ['main-style'], (string) filemtime($file));
    }
}, 100);
