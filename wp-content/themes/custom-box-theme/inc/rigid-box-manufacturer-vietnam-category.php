<?php
/**
 * SEO, AIO/GEO content and deploy sync for the Rigid Boxes category.
 */

defined('ABSPATH') || exit;

function custom_box_rigid_box_manufacturer_vietnam_category_data(): array {
    return array(
        'slug'             => 'rigid-boxes',
        'term_name'        => 'Rigid Boxes',
        'archive_title'    => 'Rigid Box Manufacturer in Vietnam',
        'seo_title'        => 'Rigid Box Manufacturer Vietnam | Custom Luxury Boxes',
        'seo_description'  => 'Vietnam rigid box manufacturer for custom magnetic, drawer and lid-and-base boxes. Compare greyboard, wrapping, inserts, samples, MOQ and export packing.',
        'focus_keyword'    => 'rigid box manufacturer vietnam',
        'hero_eyebrow'     => 'Custom Rigid Box Factory in Vietnam',
        'hero_description' => 'VPN manufactures made-to-order rigid setup boxes in Ho Chi Minh City, Vietnam for cosmetics, jewelry, gifts, electronics and premium retail products. Structure, wrapping, inserts, finishes and export packing are developed from an approved product brief.',
        'hero_alt'         => 'Custom navy rigid gift box manufactured in Vietnam with silver logo',
        'hero_image'       => array(
            'base'     => 'custom-rigid-gift-box-1',
            'relative' => '2026/05/custom-rigid-gift-box-1.webp',
            'alt'      => 'Custom navy rigid gift box manufactured in Vietnam with silver logo',
            'title'    => 'Custom Navy Rigid Gift Box',
            'caption'  => 'Custom navy rigid gift box with a premium wrapped paperboard structure and silver logo.',
        ),
        'primary_cta'      => array(
            'label' => 'Request a Rigid Box Quote',
            'url'   => home_url('/contact/#quote'),
        ),
        'secondary_cta'    => array(
            'label' => 'Discuss a Rigid Box Sample',
            'url'   => home_url('/contact/#quote'),
        ),
        'faqs'             => array(
            array(
                'question' => 'What is a custom rigid box?',
                'answer'   => 'A custom rigid box, also called a setup box, uses a thick non-folding paperboard core wrapped with printed or specialty paper. Its dimensions, opening style, wrap, insert and finishes are engineered around the product and intended presentation.',
            ),
            array(
                'question' => 'Does VPN operate as a rigid box manufacturer in Vietnam?',
                'answer'   => 'Yes. VPN Paper Box is based in Ho Chi Minh City, Vietnam and develops made-to-order rigid boxes for B2B brands, importers and export buyers. Each quotation is prepared from the product, structure, quantity, artwork, destination and approval requirements.',
            ),
            array(
                'question' => 'Which rigid box styles can be customized?',
                'answer'   => 'Common project directions include two-piece lid-and-base boxes, magnetic book-style boxes, drawer boxes with sleeves and presentation boxes with product-specific inserts. Final feasibility depends on size, product weight, opening action, packing method and production quantity.',
            ),
            array(
                'question' => 'How should greyboard and wrapping paper be specified?',
                'answer'   => 'Specify the board by controlled thickness, stiffness, flatness, moisture behavior and finished-box performance rather than by a generic name alone. Wrapping paper should be approved for print appearance, fold and corner behavior, scuff resistance, adhesive compatibility and the required finish.',
            ),
            array(
                'question' => 'What are the MOQ and lead time for custom rigid boxes from Vietnam?',
                'answer'   => 'MOQ and lead time are project-specific. Box size, structure, board and paper availability, number of artworks, inserts, tooling, finishes, hand assembly, sample approvals and export packing all affect the production plan. Request a written quotation after the specification is defined.',
            ),
            array(
                'question' => 'How should buyers approve rigid boxes before mass production?',
                'answer'   => 'Approve the dieline or construction drawing, a packed structural sample, material references, artwork, color target, finishes, insert fit, opening action and master-carton plan. Any transport test or certification requirement should be agreed before the bulk schedule begins.',
            ),
        ),
    );
}

function custom_box_is_rigid_box_manufacturer_vietnam_category($term = null): bool {
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
        && 'rigid-boxes' === $term->slug;
}

function custom_box_rigid_box_manufacturer_vietnam_category_url(): string {
    $term = get_term_by('slug', 'rigid-boxes', 'product_cat');
    $url = $term && !is_wp_error($term) ? get_term_link($term) : '';

    return !is_wp_error($url) && $url ? $url : home_url('/products/rigid-boxes/');
}

function custom_box_find_rigid_box_manufacturer_vietnam_hero_attachment(): int {
    global $wpdb;

    $data = custom_box_rigid_box_manufacturer_vietnam_category_data();
    $image = $data['hero_image'];
    $like = '%' . $wpdb->esc_like($image['base']) . '%';
    $candidate_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id ASC",
        $like
    ));

    foreach ($candidate_ids as $candidate_id) {
        $attached_file = (string) get_post_meta((int) $candidate_id, '_wp_attached_file', true);
        if ($image['base'] === pathinfo(wp_basename($attached_file), PATHINFO_FILENAME)) {
            return (int) $candidate_id;
        }
    }

    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    $upload_path = trailingslashit($uploads['basedir']) . $image['relative'];
    $bundle_path = get_template_directory()
        . '/inc/product-sample-deploy-assets/uploads/'
        . $image['relative'];

    if (!file_exists($upload_path) && file_exists($bundle_path)) {
        if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
            return 0;
        }
    }

    if (!file_exists($upload_path)) {
        return 0;
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

    update_post_meta((int) $attachment_id, '_wp_attached_file', $image['relative']);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload_path);
    if (!is_wp_error($metadata) && !empty($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }

    return (int) $attachment_id;
}

function custom_box_update_rigid_box_manufacturer_vietnam_hero_attachment(int $attachment_id): void {
    if (!$attachment_id) {
        return;
    }

    $image = custom_box_rigid_box_manufacturer_vietnam_category_data()['hero_image'];
    wp_update_post(array(
        'ID'           => $attachment_id,
        'post_title'   => $image['title'],
        'post_excerpt' => $image['caption'],
    ));
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
}

function custom_box_rigid_box_manufacturer_vietnam_category_is_complete($term, array &$failures = array()): bool {
    $data = custom_box_rigid_box_manufacturer_vietnam_category_data();

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
        'rank_math_canonical_url' => custom_box_rigid_box_manufacturer_vietnam_category_url(),
    );

    foreach ($expected_meta as $key => $value) {
        if ($value !== (string) get_term_meta((int) $term->term_id, $key, true)) {
            $failures[] = $key;
        }
    }

    $thumbnail_id = (int) get_term_meta((int) $term->term_id, 'thumbnail_id', true);
    $attached_file = $thumbnail_id ? (string) get_post_meta($thumbnail_id, '_wp_attached_file', true) : '';
    $image = $data['hero_image'];

    if (!$thumbnail_id || $image['base'] !== pathinfo(wp_basename($attached_file), PATHINFO_FILENAME)) {
        $failures[] = 'category hero image';
    } elseif (
        $image['alt'] !== (string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)
        || $image['title'] !== (string) get_post_field('post_title', $thumbnail_id)
        || $image['caption'] !== (string) get_post_field('post_excerpt', $thumbnail_id)
    ) {
        $failures[] = 'category hero image metadata';
    }

    return empty($failures);
}

function custom_box_sync_rigid_box_manufacturer_vietnam_category(): void {
    if (!taxonomy_exists('product_cat')) {
        return;
    }

    $data = custom_box_rigid_box_manufacturer_vietnam_category_data();
    $version = '2026-09-10.2';
    $version_option = 'custom_box_rigid_box_manufacturer_vietnam_category_sync_version';
    $term = get_term_by('slug', $data['slug'], 'product_cat');
    $failures = array();

    if (
        $version === (string) get_option($version_option)
        && custom_box_rigid_box_manufacturer_vietnam_category_is_complete($term, $failures)
    ) {
        return;
    }

    $failures = array();

    if (!$term || is_wp_error($term)) {
        delete_option($version_option);
        custom_box_set_rigid_box_manufacturer_vietnam_category_notice('warning', array('target category'));
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
        custom_box_set_rigid_box_manufacturer_vietnam_category_notice('warning', array('category update: ' . $updated->get_error_message()));
        return;
    }

    update_term_meta((int) $term->term_id, 'rank_math_title', $data['seo_title']);
    update_term_meta((int) $term->term_id, 'rank_math_description', $data['seo_description']);
    update_term_meta((int) $term->term_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_term_meta((int) $term->term_id, 'rank_math_canonical_url', custom_box_rigid_box_manufacturer_vietnam_category_url());

    $thumbnail_id = custom_box_find_rigid_box_manufacturer_vietnam_hero_attachment();
    if (!$thumbnail_id) {
        delete_option($version_option);
        custom_box_set_rigid_box_manufacturer_vietnam_category_notice('warning', array('category hero image'));
        return;
    }

    custom_box_update_rigid_box_manufacturer_vietnam_hero_attachment($thumbnail_id);
    update_term_meta((int) $term->term_id, 'thumbnail_id', $thumbnail_id);

    clean_term_cache((int) $term->term_id, 'product_cat');
    $term = get_term((int) $term->term_id, 'product_cat');

    if (custom_box_rigid_box_manufacturer_vietnam_category_is_complete($term, $failures)) {
        update_option($version_option, $version, false);
        custom_box_set_rigid_box_manufacturer_vietnam_category_notice(
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
    custom_box_set_rigid_box_manufacturer_vietnam_category_notice('warning', $failures);
}

function custom_box_maybe_sync_rigid_box_manufacturer_vietnam_category(): void {
    if (
        !is_admin()
        || !current_user_can('manage_options')
        || (function_exists('wp_doing_ajax') && wp_doing_ajax())
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (defined('DOING_CRON') && DOING_CRON)
    ) {
        return;
    }

    custom_box_sync_rigid_box_manufacturer_vietnam_category();
}
add_action('admin_init', 'custom_box_maybe_sync_rigid_box_manufacturer_vietnam_category', 37);

function custom_box_set_rigid_box_manufacturer_vietnam_category_notice(string $type, array $failures = array(), array $details = array()): void {
    if (!function_exists('get_current_user_id') || !get_current_user_id()) {
        return;
    }

    set_transient(
        'custom_box_rigid_box_manufacturer_vietnam_notice_' . get_current_user_id(),
        array(
            'type'     => $type,
            'failures' => array_values(array_unique($failures)),
            'details'  => $details,
        ),
        120
    );
}

function custom_box_rigid_box_manufacturer_vietnam_category_notice(): void {
    if (!current_user_can('manage_options')) {
        return;
    }

    $key = 'custom_box_rigid_box_manufacturer_vietnam_notice_' . get_current_user_id();
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
                'Rigid Boxes category synced and verified: term ID %d, %d products, thumbnail ID %d, canonical and Rank Math fields complete.',
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
        esc_html('Rigid Boxes category sync is incomplete and will retry. Check: ' . $failures . '.')
    );
}
add_action('admin_notices', 'custom_box_rigid_box_manufacturer_vietnam_category_notice');

function custom_box_rigid_box_manufacturer_vietnam_paged_canonical($canonical) {
    if (!custom_box_is_rigid_box_manufacturer_vietnam_category()) {
        return $canonical;
    }

    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    if ($paged < 2) {
        return custom_box_rigid_box_manufacturer_vietnam_category_url();
    }

    $url = get_pagenum_link($paged);
    if (!empty($_GET)) {
        $url = remove_query_arg(array_map('sanitize_key', array_keys(wp_unslash($_GET))), $url);
    }

    return $url;
}
add_filter('rank_math/frontend/canonical', 'custom_box_rigid_box_manufacturer_vietnam_paged_canonical', 42);
add_filter('get_canonical_url', 'custom_box_rigid_box_manufacturer_vietnam_paged_canonical', 42);

function custom_box_rigid_box_manufacturer_vietnam_schema_entities(): array {
    if (!custom_box_is_rigid_box_manufacturer_vietnam_category()) {
        return array();
    }

    $data = custom_box_rigid_box_manufacturer_vietnam_category_data();
    $term = get_queried_object();
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $page_url = custom_box_rigid_box_manufacturer_vietnam_paged_canonical(custom_box_rigid_box_manufacturer_vietnam_category_url());
    $item_list_id = $page_url . '#product-list';
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
                'name'        => 'Custom rigid box manufacturing in Vietnam',
                'description' => 'Made-to-order rigid setup boxes developed around product fit, opening action, wrapping, inserts, finishes and export requirements.',
            ),
            'mainEntity'   => array('@id' => $item_list_id),
            'isPartOf'     => array('@id' => home_url('/#website')),
            'publisher'    => array('@id' => home_url('/#organization')),
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

function custom_box_rigid_box_manufacturer_vietnam_rank_math_schema($schema) {
    if (!is_array($schema) || !custom_box_is_rigid_box_manufacturer_vietnam_category()) {
        return $schema;
    }

    $entities = custom_box_rigid_box_manufacturer_vietnam_schema_entities();
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
        $schema['schema-rigidBoxManufacturerVietnamCollection'] = $entities['collection'];
    }

    $schema['schema-rigidBoxManufacturerVietnamItems'] = $entities['items'];
    if (!empty($entities['faq'])) {
        $schema['schema-rigidBoxManufacturerVietnamFaq'] = $entities['faq'];
    }

    return $schema;
}
add_filter('rank_math/json_ld', 'custom_box_rigid_box_manufacturer_vietnam_rank_math_schema', 32);

function custom_box_rigid_box_manufacturer_vietnam_schema_fallback(): void {
    if (defined('RANK_MATH_VERSION') || !custom_box_is_rigid_box_manufacturer_vietnam_category()) {
        return;
    }

    $entities = custom_box_rigid_box_manufacturer_vietnam_schema_entities();
    echo '<script type="application/ld+json">' . wp_json_encode(
        array(
            '@context' => 'https://schema.org',
            '@graph'   => array_values($entities),
        ),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ) . '</script>' . "\n";
}
add_action('wp_head', 'custom_box_rigid_box_manufacturer_vietnam_schema_fallback', 26);
