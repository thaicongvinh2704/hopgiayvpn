<?php
/**
 * SEO, AIO/GEO content and deploy sync for the Corrugated Mailer Boxes category.
 */

defined('ABSPATH') || exit;

function custom_box_corrugated_mailer_boxes_category_data(): array {
    return array(
        'slug'            => 'corrugated-mailer-boxes',
        'term_name'       => 'Corrugated Mailer Boxes',
        'archive_title'   => 'Custom Corrugated Mailer Boxes',
        'seo_title'       => 'Custom Corrugated Mailer Boxes | Vietnam Manufacturer',
        'seo_description' => 'Custom corrugated mailer boxes made to size with E- or B-flute, inside/outside printing and inserts. Compare options and request a factory quote.',
        'focus_keyword'   => 'custom corrugated mailer boxes',
        'hero_eyebrow'    => 'Custom Printed Shipping Packaging',
        'hero_description' => 'Custom corrugated mailer boxes are one-piece, foldable shipping boxes made from fluted board. VPN develops the dimensions, flute, print and inserts around your product, delivery route and unboxing requirements.',
        'hero_alt'        => 'Custom printed corrugated mailer box with kraft exterior and orange interior',
        'primary_cta'     => array(
            'label' => 'Request a Custom Quote',
            'url'   => home_url('/contact/#quote'),
        ),
        'secondary_cta'   => array(
            'label' => 'Discuss a Structural Sample',
            'url'   => home_url('/contact/#quote'),
        ),
        'faqs'            => array(
            array(
                'question' => 'What information is needed to quote a custom corrugated mailer box?',
                'answer'   => 'Send the packed product dimensions and weight, order quantity, delivery country, artwork status, preferred opening style, print coverage, insert needs and any test or compliance requirements. A physical product sample or pack-out photo reduces sizing assumptions.',
            ),
            array(
                'question' => 'Should I choose E-flute or B-flute for a mailer box?',
                'answer'   => 'E-flute is a common starting point for compact, print-focused ecommerce mailers. B-flute is thicker and can be considered when cushioning or stacking strength matters more. Board grade, product weight, dimensions and route testing must be reviewed together; flute name alone does not determine performance.',
            ),
            array(
                'question' => 'Can a corrugated mailer box ship without an outer carton?',
                'answer'   => 'Sometimes, but not automatically. The answer depends on the packed weight, closure, exposed print, carrier handling, weather risk and required delivery condition. Test the complete packed product on its real distribution route before removing an outer shipper.',
            ),
            array(
                'question' => 'Can you print both the inside and outside of the box?',
                'answer'   => 'Yes. Inside and outside artwork can be planned in CMYK or specified spot colors, subject to the liner and print process. Approve color, registration, rub resistance and score-line behavior on the selected board before mass production.',
            ),
            array(
                'question' => 'When does a mailer box need an insert or divider?',
                'answer'   => 'Use an insert or divider when the product can move, collide with another item, present poorly on opening or place load on a fragile feature. The insert should be designed from the final pack-out and checked for restraint, removal access, assembly time and material recovery.',
            ),
            array(
                'question' => 'What are the MOQ and lead time for custom mailer boxes?',
                'answer'   => 'MOQ and lead time are project-specific. Size, board, printing, finishes, tooling, sample approvals, quantity and destination all affect the production plan. Request a written quote and timeline after the structural and artwork requirements are defined.',
            ),
        ),
    );
}

function custom_box_is_corrugated_mailer_boxes_category($term = null): bool {
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
        && 'corrugated-mailer-boxes' === $term->slug;
}

function custom_box_corrugated_mailer_boxes_category_url(): string {
    $term = get_term_by('slug', 'corrugated-mailer-boxes', 'product_cat');
    $url = $term && !is_wp_error($term) ? get_term_link($term) : '';

    return !is_wp_error($url) && $url ? $url : home_url('/products/corrugated-mailer-boxes/');
}

function custom_box_corrugated_mailer_boxes_category_is_complete($term, array &$failures = array()): bool {
    $data = custom_box_corrugated_mailer_boxes_category_data();

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

    $expected_meta = array(
        'rank_math_title'         => $data['seo_title'],
        'rank_math_description'   => $data['seo_description'],
        'rank_math_focus_keyword' => $data['focus_keyword'],
        'rank_math_canonical_url' => custom_box_corrugated_mailer_boxes_category_url(),
    );

    foreach ($expected_meta as $key => $value) {
        if ($value !== (string) get_term_meta((int) $term->term_id, $key, true)) {
            $failures[] = $key;
        }
    }

    return empty($failures);
}

function custom_box_sync_corrugated_mailer_boxes_category(): void {
    if (!taxonomy_exists('product_cat')) {
        return;
    }

    $data = custom_box_corrugated_mailer_boxes_category_data();
    $version = '2026-09-10.1';
    $version_option = 'custom_box_corrugated_mailer_category_sync_version';
    $term = get_term_by('slug', $data['slug'], 'product_cat');
    $failures = array();

    if (
        $version === (string) get_option($version_option)
        && custom_box_corrugated_mailer_boxes_category_is_complete($term, $failures)
    ) {
        return;
    }

    $failures = array();

    if (!$term || is_wp_error($term)) {
        delete_option($version_option);
        custom_box_set_corrugated_mailer_boxes_category_notice('warning', array('target category'));
        return;
    }

    $updated = wp_update_term(
        (int) $term->term_id,
        'product_cat',
        array(
            'name'        => $data['term_name'],
            'description' => $data['hero_description'],
        )
    );

    if (is_wp_error($updated)) {
        delete_option($version_option);
        custom_box_set_corrugated_mailer_boxes_category_notice('warning', array('category update: ' . $updated->get_error_message()));
        return;
    }

    update_term_meta((int) $term->term_id, 'rank_math_title', $data['seo_title']);
    update_term_meta((int) $term->term_id, 'rank_math_description', $data['seo_description']);
    update_term_meta((int) $term->term_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_term_meta((int) $term->term_id, 'rank_math_canonical_url', custom_box_corrugated_mailer_boxes_category_url());

    $thumbnail_id = (int) get_term_meta((int) $term->term_id, 'thumbnail_id', true);
    if ($thumbnail_id) {
        update_post_meta($thumbnail_id, '_wp_attachment_image_alt', $data['hero_alt']);
    }

    clean_term_cache((int) $term->term_id, 'product_cat');
    $term = get_term((int) $term->term_id, 'product_cat');

    if (custom_box_corrugated_mailer_boxes_category_is_complete($term, $failures)) {
        update_option($version_option, $version, false);
        custom_box_set_corrugated_mailer_boxes_category_notice(
            'success',
            array(),
            array(
                'term_id'       => (int) $term->term_id,
                'product_count' => (int) $term->count,
                'thumbnail_id'  => $thumbnail_id,
            )
        );
        return;
    }

    delete_option($version_option);
    custom_box_set_corrugated_mailer_boxes_category_notice('warning', $failures);
}

function custom_box_maybe_sync_corrugated_mailer_boxes_category(): void {
    if (
        !is_admin()
        || !current_user_can('manage_options')
        || (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return;
    }

    custom_box_sync_corrugated_mailer_boxes_category();
}
add_action('admin_init', 'custom_box_maybe_sync_corrugated_mailer_boxes_category', 35);

function custom_box_set_corrugated_mailer_boxes_category_notice(string $type, array $failures = array(), array $details = array()): void {
    if (!function_exists('get_current_user_id') || !get_current_user_id()) {
        return;
    }

    set_transient(
        'custom_box_corrugated_mailer_category_notice_' . get_current_user_id(),
        array(
            'type'     => $type,
            'failures' => array_values(array_unique($failures)),
            'details'  => $details,
        ),
        120
    );
}

function custom_box_corrugated_mailer_boxes_category_notice(): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    $key = 'custom_box_corrugated_mailer_category_notice_' . get_current_user_id();
    $notice = get_transient($key);

    if (!$notice || !is_array($notice)) {
        return;
    }

    delete_transient($key);

    if ('success' === $notice['type']) {
        $details = isset($notice['details']) && is_array($notice['details']) ? $notice['details'] : array();
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html(sprintf(
                'Corrugated Mailer Boxes category synced and verified: term ID %d, %d products, thumbnail ID %d, canonical and Rank Math fields complete.',
                isset($details['term_id']) ? (int) $details['term_id'] : 0,
                isset($details['product_count']) ? (int) $details['product_count'] : 0,
                isset($details['thumbnail_id']) ? (int) $details['thumbnail_id'] : 0
            ))
        );
        return;
    }

    $failures = !empty($notice['failures']) ? implode(', ', array_map('sanitize_text_field', $notice['failures'])) : 'unknown validation failure';
    printf(
        '<div class="notice notice-warning"><p>%s</p></div>',
        esc_html('Corrugated Mailer Boxes category sync is incomplete and will retry. Check: ' . $failures . '.')
    );
}
add_action('admin_notices', 'custom_box_corrugated_mailer_boxes_category_notice');

function custom_box_corrugated_mailer_boxes_paged_canonical($canonical) {
    if (!custom_box_is_corrugated_mailer_boxes_category()) {
        return $canonical;
    }

    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    if ($paged < 2) {
        return custom_box_corrugated_mailer_boxes_category_url();
    }

    $url = get_pagenum_link($paged);
    if (!empty($_GET)) {
        $url = remove_query_arg(array_map('sanitize_key', array_keys(wp_unslash($_GET))), $url);
    }

    return $url;
}
add_filter('rank_math/frontend/canonical', 'custom_box_corrugated_mailer_boxes_paged_canonical', 40);
add_filter('get_canonical_url', 'custom_box_corrugated_mailer_boxes_paged_canonical', 40);

function custom_box_corrugated_mailer_boxes_schema_entities(): array {
    if (!custom_box_is_corrugated_mailer_boxes_category()) {
        return array();
    }

    $data = custom_box_corrugated_mailer_boxes_category_data();
    $term = get_queried_object();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $page_url = custom_box_corrugated_mailer_boxes_paged_canonical(custom_box_corrugated_mailer_boxes_category_url());
    $item_list_id = $page_url . '#product-list';
    $faq_id = $page_url . '#faq';
    $query_args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
        'tax_query'      => array(
            array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => (int) $term->term_id,
                'include_children' => true,
            ),
        ),
    );
    $default_orderby = function_exists('wc_get_loop_prop') && wc_get_loop_prop('is_search')
        ? 'relevance'
        : get_option('woocommerce_default_catalog_orderby', 'menu_order');
    $orderby_value = isset($_GET['orderby'])
        ? (function_exists('wc_clean') ? wc_clean(wp_unslash($_GET['orderby'])) : sanitize_text_field(wp_unslash($_GET['orderby'])))
        : $default_orderby;

    if (function_exists('WC') && WC()->query) {
        $ordering_args = WC()->query->get_catalog_ordering_args($orderby_value);

        if (!empty($ordering_args['orderby'])) {
            $query_args['orderby'] = $ordering_args['orderby'];
        }

        if (!empty($ordering_args['order'])) {
            $query_args['order'] = $ordering_args['order'];
        }

        if (!empty($ordering_args['meta_key'])) {
            $query_args['meta_key'] = $ordering_args['meta_key'];
        }
    }

    $products = new WP_Query($query_args);
    $items = array();
    $position = (($paged - 1) * 12) + 1;

    foreach ($products->posts as $product_post) {
        $item = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'url'      => get_permalink($product_post),
            'name'     => get_the_title($product_post),
        );
        $image_url = get_the_post_thumbnail_url($product_post, 'large');
        if ($image_url) {
            $item['image'] = $image_url;
        }
        $items[] = $item;
    }

    $faq_entities = array();
    foreach ($data['faqs'] as $faq) {
        $faq_entities[] = array(
            '@type'          => 'Question',
            'name'           => $faq['question'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $faq['answer'],
            ),
        );
    }

    $entities = array(
        'collection' => array(
            '@type'       => 'CollectionPage',
            '@id'         => $page_url . '#webpage',
            'url'         => $page_url,
            'name'        => $paged > 1 ? sprintf('%s - Page %d', $data['archive_title'], $paged) : $data['archive_title'],
            'description' => $data['seo_description'],
            'inLanguage'  => 'en-US',
            'about'       => array(
                '@type'       => 'Thing',
                'name'        => 'Custom corrugated mailer boxes',
                'description' => 'One-piece foldable shipping boxes made from corrugated fiberboard and customized for product fit, protection, printing and unboxing.',
            ),
            'mainEntity'  => array('@id' => $item_list_id),
            'isPartOf'    => array('@id' => home_url('/#website')),
            'publisher'   => array('@id' => home_url('/#organization')),
            'dateModified' => '2026-09-10',
        ),
        'items' => array(
            '@type'           => 'ItemList',
            '@id'             => $item_list_id,
            'name'            => $data['archive_title'] . ' product range',
            'numberOfItems'   => count($items),
            'itemListElement' => $items,
        ),
    );

    if (1 === $paged) {
        $entities['faq'] = array(
            '@type'      => 'FAQPage',
            '@id'        => $faq_id,
            'url'        => $page_url . '#faq',
            'inLanguage' => 'en-US',
            'mainEntity' => $faq_entities,
        );
    }

    return $entities;
}

function custom_box_corrugated_mailer_boxes_rank_math_schema($schema) {
    if (!is_array($schema) || !custom_box_is_corrugated_mailer_boxes_category()) {
        return $schema;
    }

    $entities = custom_box_corrugated_mailer_boxes_schema_entities();
    $collection_key = null;

    foreach ($schema as $key => $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $types = isset($entity['@type']) ? (array) $entity['@type'] : array();
        if (in_array('CollectionPage', $types, true)) {
            $collection_key = $key;
            break;
        }
    }

    if (null !== $collection_key) {
        $schema[$collection_key] = array_merge($schema[$collection_key], $entities['collection']);
    } else {
        $schema['schema-corrugatedMailerCollection'] = $entities['collection'];
    }

    $schema['schema-corrugatedMailerItems'] = $entities['items'];
    if (!empty($entities['faq'])) {
        $schema['schema-corrugatedMailerFaq'] = $entities['faq'];
    }

    return $schema;
}
add_filter('rank_math/json_ld', 'custom_box_corrugated_mailer_boxes_rank_math_schema', 30);

function custom_box_corrugated_mailer_boxes_schema_fallback(): void {
    if (defined('RANK_MATH_VERSION') || !custom_box_is_corrugated_mailer_boxes_category()) {
        return;
    }

    $entities = custom_box_corrugated_mailer_boxes_schema_entities();
    echo '<script type="application/ld+json">' . wp_json_encode(
        array(
            '@context' => 'https://schema.org',
            '@graph'   => array_values($entities),
        ),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_corrugated_mailer_boxes_schema_fallback', 25);
