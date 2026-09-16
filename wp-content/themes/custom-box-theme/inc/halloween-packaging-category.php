<?php
/**
 * SEO, AIO/GEO content and deploy sync for the Halloween Packaging category.
 */

defined('ABSPATH') || exit;

function custom_box_halloween_packaging_category_data(): array {
    return array(
        'slug'             => 'halloween-packaging',
        'term_name'        => 'Halloween Packaging',
        'archive_title'    => 'Custom Halloween Packaging Boxes & Bags',
        'seo_title'        => 'Custom Halloween Packaging | Vietnam Manufacturer',
        'seo_description'  => 'Custom Halloween packaging boxes and bags made in Vietnam for candy, candles, gifts, beauty and ecommerce. Compare structures, finishes and inserts.',
        'focus_keyword'    => 'custom Halloween packaging',
        'hero_eyebrow'     => 'Seasonal Packaging • Made in Vietnam',
        'hero_description' => 'Custom Halloween packaging includes printed paper boxes, rigid gift boxes, mailers, candy cartons and bags developed around your product, campaign artwork and sales channel. VPN manufactures made-to-order seasonal packaging in Ho Chi Minh City, Vietnam for brands, retailers, confectionery, candle, beauty, gift and ecommerce programs.',
        'hero_alt'         => 'Custom pink Halloween packaging tote with white ghost pattern',
        'hero_image'       => array(
            'base'     => 'custom-halloween-packaging-ghost-tote',
            'relative' => '2026/09/custom-halloween-packaging-ghost-tote.webp',
            'alt'      => 'Custom pink Halloween packaging tote with white ghost pattern',
            'title'    => 'Custom Halloween Packaging with Ghost Pattern',
            'caption'  => 'Seasonal Halloween packaging example with a custom ghost pattern for a branded event campaign.',
        ),
        'product_slugs'    => array(
            'custom-pink-ghost-laminated-woven-tote-bag',
            'custom-creepy-cute-ghost-portal-halloween-paper-bag',
            'custom-pumpkin-lantern-die-cut-handle-halloween-paper-bag',
            'custom-retro-haunted-carnival-halloween-paper-bag',
            'custom-gothic-celestial-raven-halloween-paper-bag',
            'custom-witch-apothecary-kraft-halloween-paper-bag',
            'custom-luxury-halloween-eclipse-octagonal-rigid-gift-box',
            'custom-midnight-manor-halloween-octagonal-rigid-box',
            'custom-orange-halloween-octagonal-rigid-gift-box',
            'custom-halloween-offset-printed-octagonal-rigid-box',
            'custom-halloween-gable-treat-box',
        ),
        'primary_cta'      => array(
            'label' => 'Request a Halloween Packaging Quote',
            'url'   => home_url('/contact/#quote'),
        ),
        'secondary_cta'    => array(
            'label' => 'Discuss a Seasonal Sample',
            'url'   => home_url('/contact/#quote'),
        ),
        'faqs'             => array(
            array(
                'question' => 'What types of custom Halloween packaging can you manufacture?',
                'answer'   => 'The practical range includes folding cartons, rigid gift boxes, corrugated mailers, bakery and candy boxes, sleeves, inserts and branded bags. The final structure is selected from the product dimensions, weight, presentation, packing process and delivery route.',
            ),
            array(
                'question' => 'Can existing box structures be adapted for a Halloween campaign?',
                'answer'   => 'Yes. An approved structure can often be adapted with seasonal artwork, paper, printing or finishes, provided the product, dimensions and performance requirements remain compatible. A revised proof or sample should be approved before production.',
            ),
            array(
                'question' => 'Which packaging is suitable for Halloween candy and baked goods?',
                'answer'   => 'Folding cartons, window boxes, sleeves and rigid gift boxes are common starting points. Direct-food-contact components must be specified separately from outer presentation packaging, and the complete pack should be reviewed for grease, moisture, migration, closure and destination requirements.',
            ),
            array(
                'question' => 'Can Halloween packaging use foil, embossing or spot UV?',
                'answer'   => 'Yes. Foil, embossing, debossing, spot UV, specialty papers and contrasting inside print can be evaluated. Their suitability depends on the substrate, artwork, fold lines, rub risk, budget and approved production sample.',
            ),
            array(
                'question' => 'What information is needed for a Halloween packaging quote?',
                'answer'   => 'Send the packed product dimensions and weight, quantity by size and artwork, target market, preferred structure, material direction, printing and finishes, insert needs, packing method, destination and required delivery date.',
            ),
            array(
                'question' => 'What are the MOQ and lead time for custom Halloween packaging?',
                'answer'   => 'MOQ and lead time are project-specific. Structure, size, materials, number of artworks, printing, finishes, tooling, sampling, quantity and delivery destination all affect the production plan. Confirm the schedule through a written quote after the specification is defined.',
            ),
        ),
    );
}

function custom_box_is_halloween_packaging_category($term = null): bool {
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
        && 'halloween-packaging' === $term->slug;
}

function custom_box_halloween_packaging_category_url(): string {
    $term = get_term_by('slug', 'halloween-packaging', 'product_cat');
    $url = $term && !is_wp_error($term) ? get_term_link($term) : '';

    return !is_wp_error($url) && $url ? $url : home_url('/products/halloween-packaging/');
}

function custom_box_halloween_packaging_document_title($title) {
    if (!custom_box_is_halloween_packaging_category()) {
        return $title;
    }

    $data = custom_box_halloween_packaging_category_data();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

    return $paged > 1
        ? sprintf('Custom Halloween Packaging - Page %d | VPN', $paged)
        : $data['seo_title'];
}
add_filter('pre_get_document_title', 'custom_box_halloween_packaging_document_title', 30);
add_filter('rank_math/frontend/title', 'custom_box_halloween_packaging_document_title', 30);

function custom_box_halloween_packaging_meta_description($description) {
    if (!custom_box_is_halloween_packaging_category()) {
        return $description;
    }

    return custom_box_halloween_packaging_category_data()['seo_description'];
}
add_filter('rank_math/frontend/description', 'custom_box_halloween_packaging_meta_description', 30);

function custom_box_find_halloween_packaging_hero_attachment(): int {
    global $wpdb;

    $data = custom_box_halloween_packaging_category_data();
    $image = $data['hero_image'];
    $attachment_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_custom_box_halloween_category_asset' AND meta_value = %s ORDER BY post_id ASC LIMIT 1",
        $image['relative']
    ));

    if ($attachment_id && 'attachment' === get_post_type($attachment_id)) {
        return $attachment_id;
    }

    $like = '%' . $wpdb->esc_like('/' . wp_basename($image['relative']));
    $attachment_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id ASC LIMIT 1",
        $like
    ));

    if ($attachment_id && 'attachment' === get_post_type($attachment_id)) {
        return $attachment_id;
    }

    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    $upload_path = trailingslashit($uploads['basedir']) . $image['relative'];
    $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $image['relative'];

    if (!file_exists($upload_path)) {
        if (!file_exists($bundle_path) || !wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
            return 0;
        }
    }

    $filetype = wp_check_filetype(wp_basename($upload_path), null);
    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => $image['title'],
            'post_content'   => '',
            'post_excerpt'   => $image['caption'],
            'post_status'    => 'inherit',
        ),
        $upload_path,
        0,
        true
    );

    if (is_wp_error($attachment_id)) {
        return 0;
    }

    update_attached_file((int) $attachment_id, $upload_path);
    update_post_meta((int) $attachment_id, '_custom_box_halloween_category_asset', $image['relative']);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata((int) $attachment_id, wp_generate_attachment_metadata((int) $attachment_id, $upload_path));

    return (int) $attachment_id;
}

function custom_box_update_halloween_packaging_hero_attachment(int $attachment_id): void {
    if (!$attachment_id) {
        return;
    }

    $image = custom_box_halloween_packaging_category_data()['hero_image'];
    wp_update_post(array(
        'ID'           => $attachment_id,
        'post_title'   => $image['title'],
        'post_excerpt' => $image['caption'],
    ));
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
    update_post_meta($attachment_id, '_custom_box_halloween_category_asset', $image['relative']);
}

function custom_box_halloween_packaging_category_is_complete($term, array &$failures = array()): bool {
    $data = custom_box_halloween_packaging_category_data();

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
        'rank_math_canonical_url' => custom_box_halloween_packaging_category_url(),
    );

    foreach ($expected_meta as $key => $value) {
        if ($value !== (string) get_term_meta((int) $term->term_id, $key, true)) {
            $failures[] = $key;
        }
    }

    $robots = (array) get_term_meta((int) $term->term_id, 'rank_math_robots', true);
    if (!in_array('index', $robots, true) || in_array('noindex', $robots, true)) {
        $failures[] = 'rank_math_robots';
    }

    $thumbnail_id = (int) get_term_meta((int) $term->term_id, 'thumbnail_id', true);
    $image = $data['hero_image'];
    if (
        !$thumbnail_id
        || $image['alt'] !== (string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)
        || $image['title'] !== (string) get_post_field('post_title', $thumbnail_id)
    ) {
        $failures[] = 'category hero image';
    }

    foreach ($data['product_slugs'] as $product_slug) {
        $product = get_page_by_path($product_slug, OBJECT, 'product');
        if (!$product || !has_term((int) $term->term_id, 'product_cat', (int) $product->ID)) {
            $failures[] = 'product assignment: ' . $product_slug;
        }
    }

    return empty($failures);
}

function custom_box_sync_halloween_packaging_category(): void {
    if (!taxonomy_exists('product_cat')) {
        return;
    }

    $data = custom_box_halloween_packaging_category_data();
    $version = '2026-09-16.4';
    $version_option = 'custom_box_halloween_packaging_category_sync_version';
    $term = get_term_by('slug', $data['slug'], 'product_cat');
    $failures = array();

    if (
        $version === (string) get_option($version_option)
        && custom_box_halloween_packaging_category_is_complete($term, $failures)
    ) {
        return;
    }

    $parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');
    $parent_id = $parent && !is_wp_error($parent) ? (int) $parent->term_id : 0;

    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term(
            $data['term_name'],
            'product_cat',
            array(
                'slug'        => $data['slug'],
                'parent'      => $parent_id,
                'description' => $data['hero_description'],
            )
        );

        if (is_wp_error($created) || empty($created['term_id'])) {
            delete_option($version_option);
            return;
        }

        $term = get_term((int) $created['term_id'], 'product_cat');
    }

    $updated = wp_update_term(
        (int) $term->term_id,
        'product_cat',
        array(
            'name'        => $data['term_name'],
            'parent'      => $parent_id,
            'description' => $data['hero_description'],
        )
    );

    if (is_wp_error($updated)) {
        delete_option($version_option);
        return;
    }

    update_term_meta((int) $term->term_id, 'rank_math_title', $data['seo_title']);
    update_term_meta((int) $term->term_id, 'rank_math_description', $data['seo_description']);
    update_term_meta((int) $term->term_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_term_meta((int) $term->term_id, 'rank_math_canonical_url', custom_box_halloween_packaging_category_url());
    update_term_meta((int) $term->term_id, 'rank_math_robots', array('index'));

    $thumbnail_id = custom_box_find_halloween_packaging_hero_attachment();
    if ($thumbnail_id) {
        custom_box_update_halloween_packaging_hero_attachment($thumbnail_id);
        update_term_meta((int) $term->term_id, 'thumbnail_id', $thumbnail_id);
        update_term_meta((int) $term->term_id, 'custom_box_category_image_id', $thumbnail_id);
    }

    foreach ($data['product_slugs'] as $product_slug) {
        $product = get_page_by_path($product_slug, OBJECT, 'product');
        if ($product && 'trash' !== get_post_status($product)) {
            wp_set_object_terms((int) $product->ID, array((int) $term->term_id), 'product_cat', true);
        }
    }

    $allowed_slugs = array_flip($data['product_slugs']);
    $assigned_products = get_posts(array(
        'post_type'      => 'product',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => (int) $term->term_id,
            ),
        ),
    ));

    foreach ($assigned_products as $assigned_product_id) {
        $assigned_slug = (string) get_post_field('post_name', (int) $assigned_product_id);
        if (!isset($allowed_slugs[$assigned_slug])) {
            wp_remove_object_terms((int) $assigned_product_id, (int) $term->term_id, 'product_cat');
        }
    }

    clean_term_cache((int) $term->term_id, 'product_cat');
    $term = get_term((int) $term->term_id, 'product_cat');
    $failures = array();

    if (custom_box_halloween_packaging_category_is_complete($term, $failures)) {
        update_option($version_option, $version, false);
    } else {
        delete_option($version_option);
    }
}

function custom_box_maybe_sync_halloween_packaging_category(): void {
    if (
        !is_admin()
        || !current_user_can('manage_options')
        || (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return;
    }

    custom_box_sync_halloween_packaging_category();
}
add_action('admin_init', 'custom_box_maybe_sync_halloween_packaging_category', 40);

function custom_box_halloween_packaging_paged_canonical($canonical) {
    if (!custom_box_is_halloween_packaging_category()) {
        return $canonical;
    }

    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    return $paged > 1 ? get_pagenum_link($paged) : custom_box_halloween_packaging_category_url();
}
add_filter('rank_math/frontend/canonical', 'custom_box_halloween_packaging_paged_canonical', 40);
add_filter('get_canonical_url', 'custom_box_halloween_packaging_paged_canonical', 40);

function custom_box_halloween_packaging_schema_entities(): array {
    if (!custom_box_is_halloween_packaging_category()) {
        return array();
    }

    $data = custom_box_halloween_packaging_category_data();
    $term = get_queried_object();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $page_url = custom_box_halloween_packaging_paged_canonical(custom_box_halloween_packaging_category_url());
    $products = new WP_Query(array(
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
    ));
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

    $item_list_id = $page_url . '#product-list';
    $entities = array(
        'collection' => array(
            '@type'        => 'CollectionPage',
            '@id'          => $page_url . '#webpage',
            'url'          => $page_url,
            'name'         => $paged > 1 ? sprintf('%s - Page %d', $data['archive_title'], $paged) : $data['archive_title'],
            'description'  => $data['seo_description'],
            'inLanguage'   => 'en-US',
            'about'        => array(
                '@type'       => 'Thing',
                'name'        => 'Custom Halloween packaging',
                'description' => 'Made-to-order seasonal boxes and bags developed for Halloween retail, gifting, food, candle, beauty and ecommerce campaigns.',
            ),
            'mainEntity'   => array('@id' => $item_list_id),
            'isPartOf'     => array('@id' => home_url('/#website')),
            'publisher'    => array('@id' => home_url('/#organization')),
            'dateModified' => '2026-09-16',
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
        $entities['faq'] = array(
            '@type'      => 'FAQPage',
            '@id'        => $page_url . '#faq',
            'url'        => $page_url . '#faq',
            'inLanguage' => 'en-US',
            'mainEntity' => $faq_entities,
        );
    }

    return $entities;
}

function custom_box_halloween_packaging_rank_math_schema($schema) {
    if (!is_array($schema) || !custom_box_is_halloween_packaging_category()) {
        return $schema;
    }

    $entities = custom_box_halloween_packaging_schema_entities();
    foreach ($schema as $key => $entity) {
        if (is_array($entity) && in_array('CollectionPage', (array) ($entity['@type'] ?? array()), true)) {
            $schema[$key] = array_merge($entity, $entities['collection']);
            unset($entities['collection']);
            break;
        }
    }

    foreach ($entities as $key => $entity) {
        $schema['schema-halloweenPackaging-' . $key] = $entity;
    }

    return $schema;
}
add_filter('rank_math/json_ld', 'custom_box_halloween_packaging_rank_math_schema', 34);

function custom_box_halloween_packaging_schema_fallback(): void {
    if (defined('RANK_MATH_VERSION') || !custom_box_is_halloween_packaging_category()) {
        return;
    }

    echo '<script type="application/ld+json">' . wp_json_encode(
        array(
            '@context' => 'https://schema.org',
            '@graph'   => array_values(custom_box_halloween_packaging_schema_entities()),
        ),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_halloween_packaging_schema_fallback', 26);
