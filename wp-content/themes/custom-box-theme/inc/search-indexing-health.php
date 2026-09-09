<?php
/**
 * Keep search-engine discovery data current after admin and CLI imports.
 *
 * This file only manages Rank Math sitemap/IndexNow state. It does not render
 * frontend markup, enqueue assets, change templates, or modify post content.
 */

defined('ABSPATH') || exit;

function custom_box_search_indexing_sync_version() {
    return '2026-09-09.1';
}

function custom_box_search_indexing_post_types() {
    return array('post', 'page', 'product');
}

function custom_box_search_indexing_taxonomies() {
    return array('category', 'product_cat');
}

/**
 * Store one cache-flush request per PHP process, even during large imports.
 */
function custom_box_queue_rank_math_sitemap_flush($queue = null) {
    static $queued = false;

    if (null !== $queue) {
        $queued = (bool) $queue;
    }

    return $queued;
}

function custom_box_queue_sitemap_flush_for_post($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $post_type = get_post_type($post_id);
    if (in_array($post_type, custom_box_search_indexing_post_types(), true)) {
        custom_box_queue_rank_math_sitemap_flush(true);
    }
}
add_action('save_post', 'custom_box_queue_sitemap_flush_for_post', 100, 1);

function custom_box_queue_sitemap_flush_for_terms($object_id, $terms, $term_taxonomy_ids, $taxonomy) {
    unset($object_id, $terms, $term_taxonomy_ids);

    if (in_array($taxonomy, custom_box_search_indexing_taxonomies(), true)) {
        custom_box_queue_rank_math_sitemap_flush(true);
    }
}
add_action('set_object_terms', 'custom_box_queue_sitemap_flush_for_terms', 100, 4);

function custom_box_queue_sitemap_flush_for_term_cache($term_ids, $taxonomy) {
    unset($term_ids);

    if (in_array($taxonomy, custom_box_search_indexing_taxonomies(), true)) {
        custom_box_queue_rank_math_sitemap_flush(true);
    }
}
add_action('clean_term_cache', 'custom_box_queue_sitemap_flush_for_term_cache', 100, 2);

/**
 * Remove Rank Math's generated XML cache. This does not purge public page cache.
 */
function custom_box_flush_rank_math_sitemap_cache() {
    if (!class_exists('RankMath\\Sitemap\\Cache')) {
        return false;
    }

    try {
        \RankMath\Sitemap\Cache::invalidate_storage();
        custom_box_queue_rank_math_sitemap_flush(false);
        return true;
    } catch (Throwable $error) {
        error_log('Custom Box sitemap cache invalidation failed: ' . $error->getMessage());
        return false;
    }
}

function custom_box_flush_queued_rank_math_sitemap_cache() {
    if (custom_box_queue_rank_math_sitemap_flush()) {
        custom_box_flush_rank_math_sitemap_cache();
    }
}
add_action('shutdown', 'custom_box_flush_queued_rank_math_sitemap_cache', 20);

function custom_box_search_indexing_settings_are_complete() {
    $sitemap = (array) get_option('rank-math-options-sitemap', array());
    $modules = (array) get_option('rank_math_modules', array());
    $instant = (array) get_option('rank-math-options-instant-indexing', array());
    $types   = isset($instant['bing_post_types']) ? (array) $instant['bing_post_types'] : array();
    $key     = isset($instant['indexnow_api_key']) ? (string) $instant['indexnow_api_key'] : '';

    return 'on' === ($sitemap['tax_product_cat_sitemap'] ?? '')
        && in_array('instant-indexing', $modules, true)
        && empty(array_diff(custom_box_search_indexing_post_types(), $types))
        && '' !== $key;
}

/**
 * Return public, indexable URLs for the one-time IndexNow repair submission.
 */
function custom_box_search_indexing_urls() {
    $urls = array();
    $ids  = get_posts(
        array(
            'post_type'              => custom_box_search_indexing_post_types(),
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    foreach ($ids as $post_id) {
        if (
            class_exists('RankMath\\Helper')
            && !\RankMath\Helper::is_post_indexable($post_id)
        ) {
            continue;
        }

        $url = get_permalink($post_id);
        if ($url) {
            $urls[] = $url;
        }
    }

    if (taxonomy_exists('product_cat')) {
        $terms = get_terms(
            array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
            )
        );

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                if (
                    class_exists('RankMath\\Helper')
                    && !\RankMath\Helper::is_term_indexable($term)
                ) {
                    continue;
                }

                $url = get_term_link($term);
                if (!is_wp_error($url)) {
                    $urls[] = $url;
                }
            }
        }
    }

    return array_values(array_unique(array_filter($urls)));
}

function custom_box_search_indexing_is_live_site() {
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    return in_array($host, array('hopgiayvpn.com', 'www.hopgiayvpn.com'), true);
}

/**
 * Apply the repair after deployment on the next authenticated Admin request.
 */
function custom_box_run_search_indexing_sync() {
    if (
        !current_user_can('manage_options')
        || wp_doing_ajax()
        || wp_doing_cron()
        || (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    $version = custom_box_search_indexing_sync_version();
    if (
        $version === get_option('custom_box_search_indexing_sync_version')
        && custom_box_search_indexing_settings_are_complete()
    ) {
        return;
    }

    if (!defined('RANK_MATH_VERSION')) {
        update_option(
            'custom_box_search_indexing_sync_status',
            array('success' => false, 'reason' => 'rank_math_unavailable', 'time' => time()),
            false
        );
        return;
    }

    $sitemap = (array) get_option('rank-math-options-sitemap', array());
    $sitemap['tax_product_cat_sitemap'] = 'on';
    update_option('rank-math-options-sitemap', $sitemap);

    $modules = array_values(array_unique((array) get_option('rank_math_modules', array())));
    if (!in_array('instant-indexing', $modules, true)) {
        $modules[] = 'instant-indexing';
        update_option('rank_math_modules', $modules);
    }

    $instant = (array) get_option('rank-math-options-instant-indexing', array());
    $instant['bing_post_types'] = array_values(
        array_unique(
            array_merge(
                isset($instant['bing_post_types']) ? (array) $instant['bing_post_types'] : array(),
                custom_box_search_indexing_post_types()
            )
        )
    );

    if (empty($instant['indexnow_api_key'])) {
        $instant['indexnow_api_key'] = str_replace('-', '', wp_generate_uuid4());
    }
    update_option('rank-math-options-instant-indexing', $instant);

    $cache_cleared = custom_box_flush_rank_math_sitemap_cache();
    $submitted     = !custom_box_search_indexing_is_live_site();
    $submitted_urls = 0;
    $response_code  = 0;

    if (custom_box_search_indexing_is_live_site()) {
        $last_attempt = (int) get_option('custom_box_search_indexing_last_attempt');
        if (!$last_attempt || time() - $last_attempt >= 15 * MINUTE_IN_SECONDS) {
            update_option('custom_box_search_indexing_last_attempt', time(), false);
            $urls           = custom_box_search_indexing_urls();
            $submitted_urls = count($urls);

            if ($urls && class_exists('RankMath\\Instant_Indexing\\Api')) {
                $api           = \RankMath\Instant_Indexing\Api::get();
                $submitted     = $api->submit($urls, true);
                $response_code = (int) $api->get_response_code();
            }
        }
    }

    $success = custom_box_search_indexing_settings_are_complete()
        && $cache_cleared
        && $submitted;

    update_option(
        'custom_box_search_indexing_sync_status',
        array(
            'success'          => $success,
            'sitemap_cache'    => $cache_cleared,
            'indexnow'         => $submitted,
            'indexnow_status'  => $response_code,
            'submitted_urls'   => $submitted_urls,
            'time'             => time(),
        ),
        false
    );

    if ($success) {
        update_option('custom_box_search_indexing_sync_version', $version, false);
    } else {
        delete_option('custom_box_search_indexing_sync_version');
    }
}
add_action('admin_init', 'custom_box_run_search_indexing_sync', 30);
