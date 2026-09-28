<?php
/**
 * SEO category page and local term/product sync for custom pizza boxes.
 */

defined('ABSPATH') || exit;

function custom_box_pizza_boxes_category_data(): array {
    return array(
        'slug'             => 'pizza-boxes',
        'term_name'        => 'Pizza Boxes',
        'parent_slug'      => 'food-paper-boxes',
        'archive_title'    => 'Custom Pizza Boxes for Delivery and Foodservice',
        'seo_title'        => 'Custom Pizza Boxes | VPN Paper Box Manufacturer',
        'seo_description'  => 'Compare custom pizza boxes for delivery, takeout and flatbread. Specify fit, board and print from the product, route and destination.',
        'focus_keyword'    => 'custom pizza boxes',
        'hero_eyebrow'     => 'Custom Pizza Box Packaging',
        'hero_description' => 'VPN develops made-to-order pizza cartons in Ho Chi Minh City, Vietnam for pizzerias, restaurants and food-service brands. This collection covers kraft-look, white, personal and elongated flatbread concepts. Set the internal fit, board, closure and artwork from the actual product and service route. The featured kraft image is a concept visualization; confirm final materials and food-contact requirements for the intended market.',
        'hero_proof_points' => array(
            'Kraft-look, white-print, personal and elongated formats',
            'Dimensions and print developed from an approved product brief',
            'Liners, barriers and vents specified by application',
            'Minimum order quantity: 1,000 boxes',
        ),
        'hero_alt'         => 'Kraft pizza delivery box concept visualization for pizzeria and food-service packaging',
        'primary_cta'      => array(
            'label' => 'Request a Pizza Box Quote',
            'url'   => home_url('/custom-pizza-boxes-manufacturer/#vpb-quote'),
        ),
        'secondary_cta'    => array(
            'label' => 'Read the Buyer Guide',
            'url'   => '#pizza-box-buying-guide',
        ),
        'hero_product_slug' => 'kraft-pizza-delivery-box',
        'product_slugs'     => array(
            'custom-pizza-packaging-box',
            'custom-fold-flat-pizza-delivery-box-full-color-print',
            'kraft-pizza-delivery-box',
            'personal-kraft-pizza-box',
            'rectangular-flatbread-pizza-box',
            'white-kraft-pizza-box',
            'white-printed-pizza-box',
        ),
    );
}

function custom_box_is_pizza_boxes_category($term = null): bool {
    if (null === $term) {
        if (!function_exists('is_product_category') || !is_product_category()) {
            return false;
        }
        $term = get_queried_object();
    }

    return $term
        && !is_wp_error($term)
        && isset($term->taxonomy, $term->slug)
        && 'product_cat' === $term->taxonomy
        && 'pizza-boxes' === $term->slug;
}

function custom_box_pizza_boxes_category_url(): string {
    $term = get_term_by('slug', 'pizza-boxes', 'product_cat');
    $url = $term && !is_wp_error($term) ? get_term_link($term) : '';
    return !is_wp_error($url) && $url ? $url : home_url('/products/pizza-boxes/');
}

function custom_box_pizza_boxes_document_title($title) {
    if (!custom_box_is_pizza_boxes_category()) {
        return $title;
    }
    $data = custom_box_pizza_boxes_category_data();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    return $paged > 1 ? sprintf('Custom Pizza Boxes - Page %d | VPN', $paged) : $data['seo_title'];
}
add_filter('pre_get_document_title', 'custom_box_pizza_boxes_document_title', 30);
add_filter('rank_math/frontend/title', 'custom_box_pizza_boxes_document_title', 30);

function custom_box_pizza_boxes_meta_description($description) {
    return custom_box_is_pizza_boxes_category()
        ? custom_box_pizza_boxes_category_data()['seo_description']
        : $description;
}
add_filter('rank_math/frontend/description', 'custom_box_pizza_boxes_meta_description', 30);

function custom_box_pizza_boxes_robots($robots) {
    if (custom_box_is_pizza_boxes_category()) {
        $robots['index'] = 'index';
        $robots['follow'] = 'follow';
    }

    return $robots;
}
add_filter('rank_math/frontend/robots', 'custom_box_pizza_boxes_robots', 40);

function custom_box_pizza_boxes_category_is_complete($term, array &$failures = array()): bool {
    $data = custom_box_pizza_boxes_category_data();

    if (!$term || is_wp_error($term)) {
        $failures[] = 'target category';
        return false;
    }
    if ($data['term_name'] !== $term->name) {
        $failures[] = 'category name';
    }
    if ($data['hero_description'] !== $term->description) {
        $failures[] = 'category description';
    }

    $parent = get_term_by('slug', $data['parent_slug'], 'product_cat');
    if (!$parent || is_wp_error($parent) || (int) $term->parent !== (int) $parent->term_id) {
        $failures[] = 'food paper box parent category';
    }

    $expected_meta = array(
        'rank_math_title'         => $data['seo_title'],
        'rank_math_description'   => $data['seo_description'],
        'rank_math_focus_keyword' => $data['focus_keyword'],
        'rank_math_canonical_url' => custom_box_pizza_boxes_category_url(),
    );
    foreach ($expected_meta as $key => $value) {
        if ($value !== (string) get_term_meta((int) $term->term_id, $key, true)) {
            $failures[] = $key;
        }
    }
    $robots = (array) get_term_meta((int) $term->term_id, 'rank_math_robots', true);
    if (!in_array('index', $robots, true) || !in_array('follow', $robots, true) || in_array('noindex', $robots, true)) {
        $failures[] = 'rank_math_robots';
    }

    $hero_product = get_page_by_path($data['hero_product_slug'], OBJECT, 'product');
    $hero_image_id = $hero_product ? (int) get_post_thumbnail_id((int) $hero_product->ID) : 0;
    if ($hero_image_id && (int) get_term_meta((int) $term->term_id, 'thumbnail_id', true) !== $hero_image_id) {
        $failures[] = 'category thumbnail';
    }

    foreach ($data['product_slugs'] as $slug) {
        $product = get_page_by_path($slug, OBJECT, 'product');
        if (!$product || 'trash' === $product->post_status) {
            continue;
        }
        if (!has_term((int) $term->term_id, 'product_cat', (int) $product->ID)) {
            $failures[] = 'product category assignment: ' . $slug;
        }
    }

    return empty($failures);
}

function custom_box_sync_pizza_boxes_category(): array {
    if (!taxonomy_exists('product_cat')) {
        return array('error' => 'WooCommerce product_cat taxonomy is unavailable.');
    }

    $data = custom_box_pizza_boxes_category_data();
    $parent = get_term_by('slug', $data['parent_slug'], 'product_cat');
    if (!$parent || is_wp_error($parent)) {
        return array('error' => 'Required parent category is missing: ' . $data['parent_slug']);
    }

    $term = get_term_by('slug', $data['slug'], 'product_cat');
    $term_args = array(
        'name'        => $data['term_name'],
        'slug'        => $data['slug'],
        'parent'      => (int) $parent->term_id,
        'description' => $data['hero_description'],
    );
    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($data['term_name'], 'product_cat', $term_args);
        if (is_wp_error($created)) {
            return array('error' => $created->get_error_message());
        }
        $term = get_term((int) $created['term_id'], 'product_cat');
    } else {
        $updated = wp_update_term((int) $term->term_id, 'product_cat', $term_args);
        if (is_wp_error($updated)) {
            return array('error' => $updated->get_error_message());
        }
        $term = get_term((int) $term->term_id, 'product_cat');
    }
    if (!$term || is_wp_error($term)) {
        return array('error' => 'Pizza Boxes category could not be loaded after saving.');
    }

    update_term_meta((int) $term->term_id, 'rank_math_title', $data['seo_title']);
    update_term_meta((int) $term->term_id, 'rank_math_description', $data['seo_description']);
    update_term_meta((int) $term->term_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_term_meta((int) $term->term_id, 'rank_math_canonical_url', custom_box_pizza_boxes_category_url());
    update_term_meta((int) $term->term_id, 'rank_math_robots', array('index', 'follow'));

    $hero_product = get_page_by_path($data['hero_product_slug'], OBJECT, 'product');
    $hero_image_id = $hero_product ? (int) get_post_thumbnail_id((int) $hero_product->ID) : 0;
    if ($hero_image_id) {
        update_term_meta((int) $term->term_id, 'thumbnail_id', $hero_image_id);
        update_term_meta((int) $term->term_id, 'custom_box_category_image_id', $hero_image_id);
    }

    $assigned = array();
    foreach ($data['product_slugs'] as $slug) {
        $product = get_page_by_path($slug, OBJECT, 'product');
        if (!$product || 'trash' === $product->post_status) {
            continue;
        }
        $result = wp_set_object_terms((int) $product->ID, array((int) $term->term_id), 'product_cat', true);
        if (is_wp_error($result)) {
            return array('error' => $result->get_error_message());
        }
        $assigned[] = $slug;
    }

    clean_term_cache((int) $term->term_id, 'product_cat');
    $term = get_term((int) $term->term_id, 'product_cat');
    $failures = array();
    $complete = custom_box_pizza_boxes_category_is_complete($term, $failures);
    if ($complete) {
        update_option('custom_box_pizza_boxes_category_sync_version', '2026-09-28.2', false);
    }

    return array(
        'term_id'       => (int) $term->term_id,
        'url'           => custom_box_pizza_boxes_category_url(),
        'assigned'      => $assigned,
        'product_count' => count($assigned),
        'complete'      => $complete,
        'failures'      => $failures,
    );
}

function custom_box_maybe_sync_pizza_boxes_category(): void {
    if (
        !is_admin()
        || !current_user_can('manage_options')
        || (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return;
    }

    $version = '2026-09-28.2';
    $term = get_term_by('slug', 'pizza-boxes', 'product_cat');
    $failures = array();
    if ($version === (string) get_option('custom_box_pizza_boxes_category_sync_version') && custom_box_pizza_boxes_category_is_complete($term, $failures)) {
        return;
    }

    custom_box_sync_pizza_boxes_category();
}
add_action('admin_init', 'custom_box_maybe_sync_pizza_boxes_category', 38);

function custom_box_create_pizza_boxes_category_if_missing(): void {
    if (taxonomy_exists('product_cat') && !get_term_by('slug', 'pizza-boxes', 'product_cat')) {
        custom_box_sync_pizza_boxes_category();
    }
}
add_action('init', 'custom_box_create_pizza_boxes_category_if_missing', 30);

function custom_box_pizza_boxes_category_paged_canonical($canonical) {
    if (!custom_box_is_pizza_boxes_category()) {
        return $canonical;
    }

    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    if ($paged < 2) {
        return custom_box_pizza_boxes_category_url();
    }

    return trailingslashit(get_pagenum_link($paged));
}
add_filter('rank_math/frontend/canonical', 'custom_box_pizza_boxes_category_paged_canonical', 42);
add_filter('get_canonical_url', 'custom_box_pizza_boxes_category_paged_canonical', 42);
