<?php
/**
 * Plugin Name: VPN Three Paper Bag Category Metadata
 * Description: Reviewed category metadata and logo-bag pagination canonicals; content and design are preserved.
 * Version: 1.0.1
 */
defined('ABSPATH') || exit;

function vpn_paper_category_seo_data_20261105(): array {
    static $data;
    if (null === $data) {
        $path = __DIR__ . '/vpn-paper-category-seo-20261105/release.json';
        $data = is_readable($path) ? json_decode((string) file_get_contents($path), true) : [];
        $data = is_array($data) ? $data : [];
    }
    return $data;
}

function vpn_paper_category_seo_values_20261105(array $row): array {
    return ['rank_math_title' => $row['title'], 'rank_math_description' => $row['description'],
        'rank_math_focus_keyword' => $row['keyword'],
        'rank_math_facebook_title' => $row['title'], 'rank_math_facebook_description' => $row['description'],
        'rank_math_twitter_title' => $row['title'], 'rank_math_twitter_description' => $row['description']];
}

/** Run once per category on an administrator request, resolving IDs by slug in each environment. */
function vpn_paper_category_seo_apply_20261105(): array {
    if (!current_user_can('manage_options')) { return ['errors' => ['Administrator access required.']]; }
    if (!defined('RANK_MATH_VERSION') || !taxonomy_exists('product_cat')) { return ['errors' => ['Rank Math and WooCommerce required.']]; }
    $release = vpn_paper_category_seo_data_20261105();
    if (empty($release['version']) || count($release['categories'] ?? []) !== 3) { return ['errors' => ['Release data missing.']]; }
    $state = get_option('vpn_paper_category_seo_20261105', []);
    $result = ['applied' => [], 'already_applied' => [], 'errors' => []];
    foreach ($release['categories'] as $row) {
        $slug = $row['slug'];
        if (($state[$slug] ?? '') === $release['version']) { $result['already_applied'][] = $slug; continue; }
        $term = get_term_by('slug', $slug, 'product_cat');
        if (!$term || is_wp_error($term)) { $result['errors'][] = $slug . ': existing category required; no term created.'; continue; }
        $values = vpn_paper_category_seo_values_20261105($row);
        $current = [];
        foreach (['rank_math_title','rank_math_description','rank_math_focus_keyword'] as $key) {
            $current[$key] = (string) get_term_meta($term->term_id, $key, true);
        }
        $desired = array_intersect_key($values, $current);
        if ($current !== $desired && !in_array($current, $row['reviewed_before'], true)) {
            $result['errors'][] = $slug . ': metadata differs from reviewed local/hosting snapshot; preserved.';
            continue;
        }
        $backup = [];
        foreach ($values as $key => $value) {
            $backup[$key] = ['exists' => metadata_exists('term', $term->term_id, $key), 'value' => get_term_meta($term->term_id, $key, true)];
        }
        $backup_key = 'vpn_paper_category_seo_before_20261105_' . $slug;
        if (false === get_option($backup_key, false) && !add_option($backup_key, ['term_id' => $term->term_id, 'meta' => $backup], '', false)) {
            $result['errors'][] = $slug . ': cannot save backup.'; continue;
        }
        $failed = false;
        foreach ($values as $key => $value) {
            update_term_meta($term->term_id, $key, wp_slash($value));
            if (get_term_meta($term->term_id, $key, true) !== $value) { $failed = true; break; }
        }
        if ($failed) {
            foreach ($backup as $key => $item) {
                if ($item['exists']) { update_term_meta($term->term_id, $key, wp_slash($item['value'])); }
                else { delete_term_meta($term->term_id, $key); }
            }
            $result['errors'][] = $slug . ': save failed; restored before-state.'; continue;
        }
        $state[$slug] = $release['version'];
        update_option('vpn_paper_category_seo_20261105', $state, false);
        clean_term_cache($term->term_id, 'product_cat');
        // Purge only the affected archive after metadata is stored.
        do_action('litespeed_purge_url', get_term_link($term));
        $result['applied'][] = $slug;
    }
    return $result;
}

add_action('admin_init', static function () {
    if (!current_user_can('manage_options')) { return; }
    $release = vpn_paper_category_seo_data_20261105();
    $state = get_option('vpn_paper_category_seo_20261105', []);
    foreach ($release['categories'] ?? [] as $row) {
        if (($state[$row['slug']] ?? '') !== ($release['version'] ?? '')) {
            $result = vpn_paper_category_seo_apply_20261105();
            update_option('vpn_paper_category_seo_status_20261105', $result, false);
            break;
        }
    }
}, 95);

/** Later filters must respect the saved fields, rather than reinstate old theme defaults. */
function vpn_paper_category_seo_current_20261105(): ?WP_Term {
    if (is_admin() || !function_exists('is_product_category') || !is_product_category()) { return null; }
    $term = get_queried_object();
    if (!($term instanceof WP_Term) || 'product_cat' !== $term->taxonomy) { return null; }
    $release = vpn_paper_category_seo_data_20261105();
    $state = get_option('vpn_paper_category_seo_20261105', []);
    foreach ($release['categories'] ?? [] as $row) {
        if ($row['slug'] === $term->slug && ($state[$term->slug] ?? '') === $release['version']) { return $term; }
    }
    return null;
}

function vpn_paper_category_seo_title_20261105($value) {
    $term = vpn_paper_category_seo_current_20261105();
    if (!$term) { return $value; }
    $saved = (string) get_term_meta($term->term_id, 'rank_math_title', true);
    if ('' === trim($saved)) { return $value; }
    $title = class_exists('RankMath\Helper') ? \RankMath\Helper::replace_vars($saved, $term) : $saved;
    $title = html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES, 'UTF-8');
    $page = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    if ($page > 1 && strpos($saved, '%page') === false) {
        $parts = explode(' | ', $title, 2);
        $title = $parts[0] . ' - Page ' . $page . (isset($parts[1]) ? ' | ' . $parts[1] : '');
    }
    return $title;
}

function vpn_paper_category_seo_description_20261105($value) {
    $term = vpn_paper_category_seo_current_20261105();
    if (!$term) { return $value; }
    $saved = (string) get_term_meta($term->term_id, 'rank_math_description', true);
    if ('' === trim($saved)) { return $value; }
    $description = class_exists('RankMath\Helper') ? \RankMath\Helper::replace_vars($saved, $term) : $saved;
    return html_entity_decode(wp_strip_all_tags($description), ENT_QUOTES, 'UTF-8');
}
foreach (['pre_get_document_title','rank_math/frontend/title','rank_math/opengraph/facebook/title','rank_math/opengraph/twitter/title'] as $hook) {
    add_filter($hook, 'vpn_paper_category_seo_title_20261105', 90);
}
foreach (['rank_math/frontend/description','rank_math/opengraph/facebook/description','rank_math/opengraph/twitter/description'] as $hook) {
    add_filter($hook, 'vpn_paper_category_seo_description_20261105', 90);
}

/** Each product archive page has distinct products and needs its own clean canonical URL. */
function vpn_paper_bag_pagination_canonical_20261105($canonical) {
    if (is_admin() || is_404() || is_feed() || !function_exists('is_product_category')
        || !is_product_category('paper-bags-with-logo')) {
        return $canonical;
    }
    $term = get_queried_object();
    if (!($term instanceof WP_Term) || 'product_cat' !== $term->taxonomy
        || 'paper-bags-with-logo' !== $term->slug) {
        return $canonical;
    }
    $url = get_term_link($term);
    if (is_wp_error($url)) { return $canonical; }
    $page = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    if ($page === 1) { return $url; }
    global $wp_rewrite;
    if (!$wp_rewrite->using_permalinks()) { return add_query_arg('paged', $page, $url); }
    return trailingslashit($url) . user_trailingslashit($wp_rewrite->pagination_base . '/' . $page, 'paged');
}
foreach (['rank_math/frontend/canonical', 'get_canonical_url', 'rank_math/opengraph/facebook/og_url'] as $hook) {
    add_filter($hook, 'vpn_paper_bag_pagination_canonical_20261105', 90);
}
