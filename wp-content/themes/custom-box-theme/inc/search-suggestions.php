<?php
/**
 * Lightweight header search autocomplete.
 *
 * @package Custom_Box_Theme
 */

add_action('rest_api_init', 'custom_box_register_search_suggestions_route');

function custom_box_register_search_suggestions_route() {
    register_rest_route('custom-box/v1', '/search-suggestions', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'custom_box_search_suggestions',
        'permission_callback' => '__return_true',
        'args'                => array(
            'q' => array(
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ),
        ),
    ));
}

function custom_box_search_suggestions(WP_REST_Request $request) {
    $query = trim((string) $request->get_param('q'));

    if (strlen($query) < 2) {
        return rest_ensure_response(array());
    }

    $query = substr($query, 0, 60);
    $cache_key = 'custom_box_search_suggestions_' . md5(strtolower($query));
    $cached = get_transient($cache_key);

    if (false !== $cached) {
        return rest_ensure_response($cached);
    }

    $results = array();
    $term_candidates = array();

    if (taxonomy_exists('product_cat')) {
        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'name__like' => $query,
            'number'     => 20,
        ));

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $link = get_term_link($term);

                if (is_wp_error($link)) {
                    continue;
                }

                $title = html_entity_decode($term->name, ENT_QUOTES, get_bloginfo('charset'));
                $score = custom_box_search_suggestion_score($title, $query);

                if (custom_box_search_suggestion_too_broad($score, $query)) {
                    continue;
                }

                $term_candidates[] = array(
                    'score' => $score,
                    'title' => $title,
                    'url'   => esc_url_raw($link),
                    'type'  => __('Category', 'custom-box-theme'),
                );
            }
        }
    }

    usort($term_candidates, 'custom_box_search_suggestion_sort');
    $results = array_slice(array_map('custom_box_search_suggestion_public_item', $term_candidates), 0, 3);
    $remaining = max(0, 6 - count($results));

    if ($remaining > 0) {
        $post_candidates = custom_box_search_suggestion_post_candidates($query);

        foreach ($post_candidates as $candidate) {
            $results[] = array(
                'title' => $candidate['title'],
                'url'   => esc_url_raw(get_permalink($candidate['id'])),
                'type'  => custom_box_search_suggestion_type_label(get_post_type($candidate['id'])),
            );

            if (count($results) >= 6) {
                break;
            }
        }
    }

    set_transient($cache_key, $results, 6 * HOUR_IN_SECONDS);

    return rest_ensure_response($results);
}

function custom_box_search_suggestion_post_candidates($query) {
    global $wpdb;

    $post_types = post_type_exists('product') ? array('product', 'post', 'page') : array('post', 'page');
    $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
    $like = '%' . $wpdb->esc_like($query) . '%';
    $sql = $wpdb->prepare(
        "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ({$placeholders}) AND post_title LIKE %s ORDER BY post_date DESC LIMIT 30",
        array_merge($post_types, array($like))
    );
    $rows = $wpdb->get_results($sql);
    $candidates = array();

    foreach ($rows as $row) {
        $title = html_entity_decode($row->post_title, ENT_QUOTES, get_bloginfo('charset'));
        $score = custom_box_search_suggestion_score($title, $query);

        if (custom_box_search_suggestion_too_broad($score, $query)) {
            continue;
        }

        $candidates[] = array(
            'id'    => (int) $row->ID,
            'score' => $score,
            'title' => $title,
        );
    }

    usort($candidates, 'custom_box_search_suggestion_sort');

    return $candidates;
}

function custom_box_search_suggestion_score($title, $query) {
    $title = strtolower($title);
    $query = strtolower($query);

    if (0 === strpos($title, $query)) {
        return 0;
    }

    if (preg_match('/(^|[\s\-])' . preg_quote($query, '/') . '/i', $title)) {
        return 1;
    }

    if (false !== strpos($title, $query)) {
        return 3;
    }

    return 99;
}

function custom_box_search_suggestion_too_broad($score, $query) {
    return strlen($query) <= 2 && $score > 1;
}

function custom_box_search_suggestion_sort($a, $b) {
    if ($a['score'] === $b['score']) {
        return strcasecmp($a['title'], $b['title']);
    }

    return $a['score'] <=> $b['score'];
}

function custom_box_search_suggestion_public_item($item) {
    unset($item['score']);

    return $item;
}

function custom_box_search_suggestion_type_label($post_type) {
    if ('product' === $post_type) {
        return __('Product', 'custom-box-theme');
    }

    if ('post' === $post_type) {
        return __('Article', 'custom-box-theme');
    }

    return __('Page', 'custom-box-theme');
}
