<?php
/**
 * SEO, AIO/GEO content and deploy sync for the Christmas Packaging category.
 */

defined('ABSPATH') || exit;

function custom_box_christmas_packaging_category_data(): array {
    return array(
        'slug'             => 'christmas-packaging',
        'term_name'        => 'Christmas Paper Bags and Gift Boxes',
        'archive_title'    => 'Custom Christmas Paper Bags & Gift Boxes',
        'seo_title'        => 'Custom Christmas Packaging Boxes & Bags | Vietnam',
        'seo_description'  => 'Custom Christmas paper bags and gift boxes made in Vietnam. Compare structures, papers, printing, inserts, samples and seasonal production planning.',
        'focus_keyword'    => 'custom Christmas packaging',
        'hero_eyebrow'     => 'Seasonal Paper Packaging • Made in Vietnam',
        'hero_description' => 'Custom Christmas paper bags and gift boxes developed around your products, artwork, packing process and delivery window. VPN manufactures made-to-order seasonal packaging in Ho Chi Minh City, Vietnam for retail, corporate gifting, food, beauty and ecommerce programs.',
        'hero_alt'         => 'Green custom Christmas rigid gift box with illustrated lid and satin ribbon bow',
        'hero_image'       => array(
            'relative' => '2026/07/green-christmas-gift-box-with-ribbon.webp',
            'alt'      => 'Green custom Christmas rigid gift box with illustrated lid and satin ribbon bow',
            'title'    => 'Custom Christmas Gift Box With Ribbon',
            'caption'  => 'Existing VPN Christmas packaging reference: a two-piece rigid gift box with seasonal artwork and ribbon.',
        ),
        'product_slugs'    => array(
            'custom-red-snowflake-lid-and-base-christmas-gift-box',
            'custom-kraft-evergreen-tuck-top-christmas-gift-box',
            'custom-candy-cane-pillow-christmas-gift-box',
            'custom-holly-berry-gable-christmas-gift-box',
            'custom-green-tree-magnetic-christmas-gift-box',
            'custom-christmas-gift-box-with-ribbon',
        ),
        'primary_cta'      => array(
            'label' => 'Request a Christmas Packaging Quote',
            'url'   => home_url('/contact/#quote'),
        ),
        'secondary_cta'    => array(
            'label' => 'Compare Bags, Boxes & Materials',
            'url'   => custom_box_christmas_packaging_category_url() . '#christmas-buyer-guide',
        ),
        'hero_proof_points' => array(
            '6 current Christmas gift-box references',
            'Made-to-size structure and insert planning',
            'Sample approval before bulk production',
        ),
        'faqs' => array(
            array(
                'question' => 'What types of custom Christmas packaging can you manufacture?',
                'answer'   => 'The practical range includes luxury paper bags, twisted-handle retail bags, rigid gift boxes, folding cartons, corrugated mailers, sleeves, inserts and coordinated box-and-bag sets. The final structure is selected from product dimensions, packed weight, presentation, packing method and distribution route.',
            ),
            array(
                'question' => 'Can one Christmas design be used across paper bags and gift boxes?',
                'answer'   => 'Yes. A controlled artwork system can carry the same colors, logo, illustration and campaign message across boxes and bags. Dielines, print areas, paper color and finish tolerances still need separate approval for each component.',
            ),
            array(
                'question' => 'Which paper bag handle is suitable for Christmas gift sets?',
                'answer'   => 'Twisted paper handles are a practical retail option, while cotton or ribbon handles support a more premium presentation. Handle choice, reinforcement and bag dimensions must be checked against the real packed weight and carrying route.',
            ),
            array(
                'question' => 'Which Christmas box is best for premium corporate gifts?',
                'answer'   => 'Rigid lid-and-base, drawer and magnetic formats are common starting points for premium multi-item gifts. Corrugated mailers may be more practical when the pack must also travel through parcel networks. Insert fit, opening sequence and the complete packed weight should guide the decision.',
            ),
            array(
                'question' => 'Can Christmas boxes and bags use foil, embossing or specialty paper?',
                'answer'   => 'Yes. Foil, embossing, debossing, spot UV, textured paper, ribbon and contrasting inside print can be evaluated. Suitability depends on the substrate, artwork, fold lines, rub risk, budget and approved production sample.',
            ),
            array(
                'question' => 'Can Christmas packaging be used for cookies, chocolate or other food gifts?',
                'answer'   => 'Yes, but the direct-food-contact layer must be defined separately from the decorative outer package. Confirm whether food touches a liner, tray, cup, pouch or carton, then review material declarations, grease and moisture exposure, storage and destination requirements.',
            ),
            array(
                'question' => 'What information is needed for a Christmas packaging quote?',
                'answer'   => 'Send product dimensions and weight, quantity by size and artwork, preferred bag or box format, paper and finish direction, insert and handle needs, artwork status, packing method, destination, target approval date and required arrival date.',
            ),
            array(
                'question' => 'What are the MOQ and lead time for custom Christmas packaging?',
                'answer'   => 'MOQ and lead time are project-specific. Structure, size, papers, artwork count, printing, finishes, tooling, sampling, quantity and destination all affect the plan. Confirm both in a written quotation after the specification and seasonal delivery window are defined.',
            ),
        ),
    );
}

function custom_box_is_christmas_packaging_category($term = null): bool {
    if (null === $term) {
        if (!function_exists('is_product_category') || !is_product_category()) {
            return false;
        }
        $term = get_queried_object();
    }

    return $term && !is_wp_error($term) && isset($term->taxonomy, $term->slug)
        && 'product_cat' === $term->taxonomy && 'christmas-packaging' === $term->slug;
}

function custom_box_christmas_packaging_category_url(): string {
    $term = get_term_by('slug', 'christmas-packaging', 'product_cat');
    $url = $term && !is_wp_error($term) ? get_term_link($term) : '';
    return !is_wp_error($url) && $url ? $url : home_url('/products/christmas-packaging/');
}

function custom_box_christmas_packaging_document_title($title) {
    if (!custom_box_is_christmas_packaging_category()) {
        return $title;
    }
    $data = custom_box_christmas_packaging_category_data();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    return $paged > 1 ? sprintf('Custom Christmas Packaging - Page %d | VPN', $paged) : $data['seo_title'];
}
add_filter('pre_get_document_title', 'custom_box_christmas_packaging_document_title', 30);
add_filter('rank_math/frontend/title', 'custom_box_christmas_packaging_document_title', 30);

function custom_box_christmas_packaging_meta_description($description) {
    return custom_box_is_christmas_packaging_category()
        ? custom_box_christmas_packaging_category_data()['seo_description']
        : $description;
}
add_filter('rank_math/frontend/description', 'custom_box_christmas_packaging_meta_description', 30);

function custom_box_find_christmas_packaging_hero_attachment(): int {
    global $wpdb;
    $relative = custom_box_christmas_packaging_category_data()['hero_image']['relative'];
    $like = '%' . $wpdb->esc_like('/' . wp_basename($relative));
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id ASC LIMIT 1",
        $like
    ));
}

function custom_box_christmas_packaging_category_is_complete($term, array &$failures = array()): bool {
    $data = custom_box_christmas_packaging_category_data();
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
        'rank_math_canonical_url' => custom_box_christmas_packaging_category_url(),
    );
    foreach ($expected_meta as $key => $value) {
        if ($value !== (string) get_term_meta((int) $term->term_id, $key, true)) {
            $failures[] = $key;
        }
    }
    $thumbnail_id = (int) get_term_meta((int) $term->term_id, 'thumbnail_id', true);
    if (!$thumbnail_id || $data['hero_image']['alt'] !== (string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)) {
        $failures[] = 'category hero image';
    }
    foreach ($data['product_slugs'] as $slug) {
        $product = get_page_by_path($slug, OBJECT, 'product');
        if (!$product || !has_term((int) $term->term_id, 'product_cat', (int) $product->ID)) {
            $failures[] = 'product assignment: ' . $slug;
        }
    }
    return empty($failures);
}

function custom_box_sync_christmas_packaging_category(): void {
    if (!taxonomy_exists('product_cat')) {
        return;
    }
    $data = custom_box_christmas_packaging_category_data();
    $version = '2026-09-17.4';
    $option = 'custom_box_christmas_packaging_category_sync_version';
    $term = get_term_by('slug', $data['slug'], 'product_cat');
    $failures = array();
    if ($version === (string) get_option($option) && custom_box_christmas_packaging_category_is_complete($term, $failures)) {
        return;
    }
    $parent = get_term_by('slug', 'custom-packaging-boxes', 'product_cat');
    $parent_id = $parent && !is_wp_error($parent) ? (int) $parent->term_id : 0;
    if (!$term || is_wp_error($term)) {
        $created = wp_insert_term($data['term_name'], 'product_cat', array(
            'slug' => $data['slug'], 'parent' => $parent_id, 'description' => $data['hero_description'],
        ));
        if (is_wp_error($created) || empty($created['term_id'])) {
            delete_option($option);
            return;
        }
        $term = get_term((int) $created['term_id'], 'product_cat');
    }
    $updated = wp_update_term((int) $term->term_id, 'product_cat', array(
        'name' => $data['term_name'], 'parent' => $parent_id, 'description' => $data['hero_description'],
    ));
    if (is_wp_error($updated)) {
        delete_option($option);
        return;
    }
    update_term_meta((int) $term->term_id, 'rank_math_title', $data['seo_title']);
    update_term_meta((int) $term->term_id, 'rank_math_description', $data['seo_description']);
    update_term_meta((int) $term->term_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_term_meta((int) $term->term_id, 'rank_math_canonical_url', custom_box_christmas_packaging_category_url());
    update_term_meta((int) $term->term_id, 'rank_math_robots', array('index'));

    $image_id = custom_box_find_christmas_packaging_hero_attachment();
    if ($image_id) {
        wp_update_post(array('ID' => $image_id, 'post_title' => $data['hero_image']['title'], 'post_excerpt' => $data['hero_image']['caption']));
        update_post_meta($image_id, '_wp_attachment_image_alt', $data['hero_image']['alt']);
        update_term_meta((int) $term->term_id, 'thumbnail_id', $image_id);
        update_term_meta((int) $term->term_id, 'custom_box_category_image_id', $image_id);
    }
    foreach ($data['product_slugs'] as $slug) {
        $product = get_page_by_path($slug, OBJECT, 'product');
        if ($product && 'trash' !== get_post_status($product)) {
            wp_set_object_terms((int) $product->ID, array((int) $term->term_id), 'product_cat', true);
        }
    }

    // Keep the seasonal archive focused: products removed from the manifest
    // remain available in their original categories but not in this hub.
    $allowed_slugs = array_flip($data['product_slugs']);
    $assigned_products = get_posts(array(
        'post_type'      => 'product',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => array(array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => (int) $term->term_id,
        )),
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
    if (custom_box_christmas_packaging_category_is_complete($term, $failures)) {
        update_option($option, $version, false);
    } else {
        delete_option($option);
    }
}

function custom_box_maybe_sync_christmas_packaging_category(): void {
    if (!is_admin() || !current_user_can('manage_options') || (function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined('REST_REQUEST') && REST_REQUEST) || (defined('DOING_CRON') && DOING_CRON)) {
        return;
    }
    custom_box_sync_christmas_packaging_category();
}
add_action('admin_init', 'custom_box_maybe_sync_christmas_packaging_category', 41);

function custom_box_christmas_packaging_paged_canonical($canonical) {
    if (!custom_box_is_christmas_packaging_category()) {
        return $canonical;
    }
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    return $paged > 1 ? get_pagenum_link($paged) : custom_box_christmas_packaging_category_url();
}
add_filter('rank_math/frontend/canonical', 'custom_box_christmas_packaging_paged_canonical', 41);
add_filter('get_canonical_url', 'custom_box_christmas_packaging_paged_canonical', 41);

function custom_box_christmas_packaging_schema_entities(): array {
    if (!custom_box_is_christmas_packaging_category()) {
        return array();
    }
    $data = custom_box_christmas_packaging_category_data();
    $term = get_queried_object();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $page_url = custom_box_christmas_packaging_paged_canonical(custom_box_christmas_packaging_category_url());
    $products = new WP_Query(array(
        'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 12, 'paged' => $paged,
        'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
        'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => (int) $term->term_id, 'include_children' => true)),
    ));
    $items = array();
    $position = (($paged - 1) * 12) + 1;
    foreach ($products->posts as $product_post) {
        $item = array('@type' => 'ListItem', 'position' => $position++, 'url' => get_permalink($product_post), 'name' => get_the_title($product_post));
        $image = get_the_post_thumbnail_url($product_post, 'large');
        if ($image) {
            $item['image'] = $image;
        }
        $items[] = $item;
    }
    $list_id = $page_url . '#product-list';
    $entities = array(
        'collection' => array(
            '@type' => 'CollectionPage', '@id' => $page_url . '#webpage', 'url' => $page_url,
            'name' => $paged > 1 ? sprintf('%s - Page %d', $data['archive_title'], $paged) : $data['archive_title'],
            'description' => $data['seo_description'], 'inLanguage' => 'en-US',
            'about' => array('@type' => 'Thing', 'name' => 'Custom Christmas packaging', 'description' => 'Made-to-order Christmas paper bags, gift boxes and coordinated packaging sets.'),
            'mainEntity' => array('@id' => $list_id), 'isPartOf' => array('@id' => home_url('/#website')),
            'publisher' => array('@id' => home_url('/#organization')), 'dateModified' => '2026-09-17',
        ),
        'items' => array('@type' => 'ItemList', '@id' => $list_id, 'name' => $data['archive_title'] . ' product range', 'numberOfItems' => count($items), 'itemListElement' => $items),
    );
    if (1 === $paged) {
        $questions = array();
        foreach ($data['faqs'] as $faq) {
            $questions[] = array('@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $faq['answer']));
        }
        $entities['faq'] = array('@type' => 'FAQPage', '@id' => $page_url . '#faq', 'url' => $page_url . '#faq', 'inLanguage' => 'en-US', 'mainEntity' => $questions);
    }
    return $entities;
}

function custom_box_christmas_packaging_rank_math_schema($schema) {
    if (!is_array($schema) || !custom_box_is_christmas_packaging_category()) {
        return $schema;
    }
    $entities = custom_box_christmas_packaging_schema_entities();
    foreach ($schema as $key => $entity) {
        if (is_array($entity) && in_array('CollectionPage', (array) ($entity['@type'] ?? array()), true)) {
            $schema[$key] = array_merge($entity, $entities['collection']);
            unset($entities['collection']);
            break;
        }
    }
    foreach ($entities as $key => $entity) {
        $schema['schema-christmasPackaging-' . $key] = $entity;
    }
    return $schema;
}
add_filter('rank_math/json_ld', 'custom_box_christmas_packaging_rank_math_schema', 35);

function custom_box_christmas_packaging_schema_fallback(): void {
    if (defined('RANK_MATH_VERSION') || !custom_box_is_christmas_packaging_category()) {
        return;
    }
    echo '<script type="application/ld+json">' . wp_json_encode(array('@context' => 'https://schema.org', '@graph' => array_values(custom_box_christmas_packaging_schema_entities())), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_christmas_packaging_schema_fallback', 27);
