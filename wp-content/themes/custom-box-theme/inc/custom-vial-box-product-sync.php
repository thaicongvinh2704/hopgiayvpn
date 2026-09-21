<?php
/**
 * Product-specific SEO/content sync for the Custom Vial Boxes product page.
 */

defined('ABSPATH') || exit;

const CUSTOM_BOX_VIAL_BOXES_SYNC_VERSION = 'custom-vial-boxes-seo-20260921-v5';
const CUSTOM_BOX_VIAL_BOXES_VALIDATION_FAILURES_OPTION = 'custom_box_custom_vial_boxes_validation_failures';

add_action('admin_init', 'custom_box_maybe_sync_custom_vial_boxes_product');
add_action('admin_notices', 'custom_box_custom_vial_boxes_admin_notice');
add_action('wp_head', 'custom_box_custom_vial_boxes_output_canonical_fallback', 5);
add_action('wp_head', 'custom_box_custom_vial_boxes_output_styles', 30);
add_action('wp_head', 'custom_box_custom_vial_boxes_output_schema_fallback', 40);
add_filter('rank_math/frontend/canonical', 'custom_box_custom_vial_boxes_rank_math_canonical', 20);
add_filter('rank_math/json_ld', 'custom_box_custom_vial_boxes_rank_math_json_ld', 40);

function custom_box_maybe_sync_custom_vial_boxes_product(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $product_id = custom_box_sync_custom_vial_boxes_product(false);

    if (is_wp_error($product_id)) {
        update_option('custom_box_custom_vial_boxes_sync_error', $product_id->get_error_message(), false);
        return;
    }

    if ((array) get_option(CUSTOM_BOX_VIAL_BOXES_VALIDATION_FAILURES_OPTION, array())) {
        return;
    }

    update_option('custom_box_custom_vial_boxes_sync_error', '', false);
}

function custom_box_sync_custom_vial_boxes_product(bool $force = false)
{
    $product = get_page_by_path('custom-vial-packaging-box', OBJECT, 'product');

    if (!$product || 'trash' === $product->post_status) {
        return new WP_Error('custom_vial_boxes_missing_product', 'Custom vial packaging box product was not found.');
    }

    $product_id = (int) $product->ID;

    if (!$force && CUSTOM_BOX_VIAL_BOXES_SYNC_VERSION === get_post_meta($product_id, '_custom_box_custom_vial_boxes_sync_version', true)) {
        return $product_id;
    }

    custom_box_sync_custom_vial_boxes_seo_images($product_id);
    custom_box_sync_custom_vial_boxes_terms_and_featured_image($product_id);
    custom_box_update_custom_vial_boxes_images($product_id);

    $allow_decoding_attribute = static function ($allowed_html, $context) {
        if ('post' === $context && isset($allowed_html['img'])) {
            $allowed_html['img']['decoding'] = true;
        }

        return $allowed_html;
    };

    add_filter('wp_kses_allowed_html', $allow_decoding_attribute, 10, 2);
    $updated = wp_update_post(array(
        'ID'           => $product_id,
        'post_title'   => 'Custom Vial Boxes with Protective Inserts',
        'post_name'    => 'custom-vial-packaging-box',
        'post_excerpt' => custom_box_custom_vial_boxes_short_description(),
        'post_content' => custom_box_custom_vial_boxes_long_description($product_id),
        'post_status'  => in_array($product->post_status, array('publish', 'private'), true) ? $product->post_status : 'draft',
    ), true);
    remove_filter('wp_kses_allowed_html', $allow_decoding_attribute, 10);

    if (is_wp_error($updated)) {
        return $updated;
    }

    update_post_meta($product_id, 'rank_math_title', 'Custom Vial Boxes with Inserts | Vietnam Manufacturer');
    update_post_meta($product_id, 'rank_math_description', 'Custom vial boxes with paperboard, EVA, foam or molded-pulp inserts for 2 mL, 5 mL, 10 mL and multi-vial packs. Request a structural sample.');
    update_post_meta($product_id, 'rank_math_focus_keyword', 'custom vial boxes');
    update_post_meta($product_id, '_custom_box_product_hero_bullets', custom_box_custom_vial_boxes_hero_bullets());
    update_post_meta($product_id, '_custom_box_product_faq_html', custom_box_custom_vial_boxes_faq_html());
    update_post_meta($product_id, '_custom_box_hide_auto_description_heading', '1');
    custom_box_update_custom_vial_boxes_specs($product_id);
    custom_box_sync_custom_vial_boxes_internal_links();

    $failures = custom_box_custom_vial_boxes_validation_failures($product_id);
    update_option(CUSTOM_BOX_VIAL_BOXES_VALIDATION_FAILURES_OPTION, $failures, false);
    if ($failures) {
        delete_post_meta($product_id, '_custom_box_custom_vial_boxes_sync_version');
        delete_option('custom_box_custom_vial_boxes_sync_version');
        update_option('custom_box_custom_vial_boxes_sync_error', 'Validation failed: ' . implode(', ', $failures), false);
    } else {
        update_post_meta($product_id, '_custom_box_custom_vial_boxes_sync_version', CUSTOM_BOX_VIAL_BOXES_SYNC_VERSION);
        update_option('custom_box_custom_vial_boxes_sync_version', CUSTOM_BOX_VIAL_BOXES_SYNC_VERSION, false);
    }

    return $product_id;
}

function custom_box_custom_vial_boxes_expected_tag_slugs(): array
{
    return array(
        'custom-packaging',
        'custom-paper-box',
        'pharmaceutical-packaging-boxes',
        'vial-packaging-box',
    );
}

function custom_box_sync_custom_vial_boxes_terms_and_featured_image(int $product_id): void
{
    $tag_names = array(
        'Custom Packaging',
        'Custom Paper Box',
        'Pharmaceutical Packaging Boxes',
        'Vial Packaging Box',
    );
    wp_set_post_terms($product_id, $tag_names, 'product_tag', false);

    $featured_id = custom_box_find_custom_vial_boxes_seo_attachment('custom-vial-packaging-box-1');
    if ($featured_id) {
        set_post_thumbnail($product_id, $featured_id);
    }
}

/**
 * Add contextual inbound links from the three most relevant supporting resources.
 * Stable markers and URL checks keep repeated deploy syncs idempotent.
 */
function custom_box_sync_custom_vial_boxes_internal_links(): void
{
    $target_url = home_url('/product/custom-vial-packaging-box/');
    $target_path = '/product/custom-vial-packaging-box/';

    $pharma_term = get_term_by('slug', 'pharmaceutical-packaging-boxes', 'product_cat');
    if ($pharma_term && !is_wp_error($pharma_term) && false === strpos((string) $pharma_term->description, $target_path)) {
        $description = rtrim((string) $pharma_term->description)
            . "\n\n<!-- custom-vial-boxes-internal-link -->\n"
            . '<p>For small glass containers, compare our <a href="' . esc_url($target_url) . '">custom vial boxes with fitted protective inserts</a>, including single-vial and multi-vial structures.</p>';
        wp_update_term((int) $pharma_term->term_id, 'product_cat', array('description' => $description));
    }

    $insert_guide = get_page_by_path('packaging-inserts-protection-presentation', OBJECT, 'post');
    if ($insert_guide && false === strpos((string) $insert_guide->post_content, $target_path)) {
        $content = rtrim((string) $insert_guide->post_content)
            . "\n\n<!-- custom-vial-boxes-internal-link -->\n"
            . '<p><span style="font-size: 110%;">For a product-specific application, review <a href="' . esc_url($target_url) . '">custom vial boxes with paperboard, EVA, foam or molded-pulp inserts</a> and the measurements needed for a fit-approved sample.</span></p>';
        wp_update_post(array('ID' => (int) $insert_guide->ID, 'post_content' => $content));
    }

    $medical_kit = get_page_by_path('custom-medical-kit-packaging-box', OBJECT, 'product');
    if ($medical_kit && false === strpos((string) $medical_kit->post_content, $target_path)) {
        $content = rtrim((string) $medical_kit->post_content)
            . "\n\n<!-- custom-vial-boxes-internal-link -->\n"
            . '<p>Need a dedicated cavity layout for glass containers? Compare <a href="' . esc_url($target_url) . '">custom vial boxes with protective inserts</a> for single-vial and multi-vial packs.</p>';
        wp_update_post(array('ID' => (int) $medical_kit->ID, 'post_content' => $content));
    }
}

function custom_box_custom_vial_boxes_has_inbound_link(string $slug, string $post_type): bool
{
    $post = get_page_by_path($slug, OBJECT, $post_type);

    return !$post || false !== strpos((string) $post->post_content, '/product/custom-vial-packaging-box/');
}

function custom_box_custom_vial_boxes_validation_failures(int $product_id): array
{
    $product = get_post($product_id);
    $failures = array();
    $expected_images = custom_box_custom_vial_boxes_seo_images();
    $stored_ids = get_post_meta($product_id, '_custom_box_custom_vial_boxes_seo_image_ids', true);
    $content = $product ? (string) $product->post_content : '';

    if (
        !$product
        || 'product' !== $product->post_type
        || 'custom-vial-packaging-box' !== $product->post_name
        || 'Custom Vial Boxes with Protective Inserts' !== $product->post_title
    ) {
        $failures[] = 'product identity';
    }
    if (!in_array((string) get_post_status($product_id), array('publish', 'private', 'draft'), true)) {
        $failures[] = 'product status';
    }

    $featured_id = (int) get_post_thumbnail_id($product_id);
    $featured_file = $featured_id ? (string) get_post_meta($featured_id, '_wp_attached_file', true) : '';
    if (!$featured_id || 'custom-vial-packaging-box-1' !== pathinfo(wp_basename($featured_file), PATHINFO_FILENAME)) {
        $failures[] = 'featured image';
    }

    foreach ($expected_images as $key => $image) {
        $attachment_id = is_array($stored_ids) && !empty($stored_ids[$key]) ? (int) $stored_ids[$key] : 0;
        $attachment = $attachment_id ? get_post($attachment_id) : null;
        if (
            !$attachment
            || 'attachment' !== $attachment->post_type
            || $product_id !== (int) $attachment->post_parent
            || $image['title'] !== $attachment->post_title
            || $image['caption'] !== $attachment->post_excerpt
            || $image['alt'] !== get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
            || !wp_get_attachment_url($attachment_id)
        ) {
            $failures[] = $key . ' attachment';
        }
    }

    if (
        8 !== preg_match_all('/<figure\b/i', $content, $unused)
        || 8 !== preg_match_all('/<img\b/i', $content, $unused)
        || false !== strpos($content, 'IMAGE_SLOT_')
        || false !== strpos($content, 'CUSTOM VIAL PACKAGING BOX')
    ) {
        $failures[] = 'content figures or placeholders';
    }

    if (
        1 !== substr_count($content, 'vial-size-planning-table')
        || 1 !== substr_count($content, 'vial-insert-comparison-table')
        || 1 !== substr_count($content, 'id="vial-fit-approval-example"')
        || false === strpos($content, '/packaging-inserts-protection-presentation/')
        || false === strpos($content, '/product/custom-medical-kit-packaging-box/')
    ) {
        $failures[] = 'decision content and internal links';
    }

    $tags = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'slugs'));
    $expected_tags = custom_box_custom_vial_boxes_expected_tag_slugs();
    if (is_wp_error($tags)) {
        $failures[] = 'product tags';
    } else {
        sort($tags);
        sort($expected_tags);
        if ($tags !== $expected_tags) {
            $failures[] = 'exact product tags';
        }
    }

    if (
        'Custom Vial Boxes with Inserts | Vietnam Manufacturer' !== get_post_meta($product_id, 'rank_math_title', true)
        || 'Custom vial boxes with paperboard, EVA, foam or molded-pulp inserts for 2 mL, 5 mL, 10 mL and multi-vial packs. Request a structural sample.' !== get_post_meta($product_id, 'rank_math_description', true)
        || 'custom vial boxes' !== get_post_meta($product_id, 'rank_math_focus_keyword', true)
    ) {
        $failures[] = 'Rank Math metadata';
    }

    $pharma_term = get_term_by('slug', 'pharmaceutical-packaging-boxes', 'product_cat');
    if ($pharma_term && !is_wp_error($pharma_term) && false === strpos((string) $pharma_term->description, '/product/custom-vial-packaging-box/')) {
        $failures[] = 'pharmaceutical category inbound link';
    }
    if (!custom_box_custom_vial_boxes_has_inbound_link('packaging-inserts-protection-presentation', 'post')) {
        $failures[] = 'insert guide inbound link';
    }
    if (!custom_box_custom_vial_boxes_has_inbound_link('custom-medical-kit-packaging-box', 'product')) {
        $failures[] = 'medical kit inbound link';
    }

    return array_values(array_unique($failures));
}

function custom_box_custom_vial_boxes_sync_report(int $product_id): string
{
    $product = get_post($product_id);

    if (!$product || 'product' !== $product->post_type) {
        return "Synced product could not be loaded.\n";
    }

    $content = (string) $product->post_content;
    $faq = (string) get_post_meta($product->ID, '_custom_box_product_faq_html', true);

    $lines = array(
        'Product ID: ' . (int) $product->ID,
        'Status: ' . get_post_status($product->ID),
        'Title: ' . $product->post_title,
        'Slug: ' . $product->post_name,
        'URL: ' . get_permalink($product->ID),
        'Rank Math title: ' . get_post_meta($product->ID, 'rank_math_title', true),
        'Rank Math description: ' . get_post_meta($product->ID, 'rank_math_description', true),
        'Focus keyword: ' . get_post_meta($product->ID, 'rank_math_focus_keyword', true),
        'Long description words: ' . str_word_count(wp_strip_all_tags($content)),
        'Content H1 count: ' . preg_match_all('/<h1\b/i', $content),
        'Image grids/cards: ' . substr_count($content, 'product-content-image-grid') . '/' . substr_count($content, 'product-content-image-card'),
        'Decision tables: ' . substr_count($content, 'vial-size-planning-table') . '/' . substr_count($content, 'vial-insert-comparison-table'),
        'Worked fit example: ' . substr_count($content, 'id="vial-fit-approval-example"'),
        'FAQ items: ' . substr_count($faq, 'faq-item'),
        'Featured image ID: ' . (int) get_post_thumbnail_id($product->ID),
        'Old all-caps phrase in content: ' . substr_count($content . ' ' . $product->post_excerpt . ' ' . $faq, 'CUSTOM VIAL PACKAGING BOX'),
    );

    return implode(PHP_EOL, $lines) . PHP_EOL;
}

function custom_box_custom_vial_boxes_short_description(): string
{
    return 'Custom vial boxes with paperboard, EVA, foam or molded-pulp inserts for nominal 2 mL, 5 mL, 10 mL and multi-vial packs. VPN Paper Box Manufacturer develops each structure from the actual vial dimensions, insert fit, printed information and sample-approval requirements.';
}

function custom_box_custom_vial_boxes_hero_bullets(): array
{
    return array(
        'Custom sizing for nominal 2 mL, 5 mL, 10 mL and other vial formats',
        'Paperboard, EVA, foam or molded-pulp insert options',
        'Single-vial cartons, multi-vial packs and rigid kits',
        'Dieline and physical sample review before mass production',
    );
}

function custom_box_custom_vial_boxes_image_alts(): array
{
    return array(
        'custom vial boxes with protective insert for glass vials',
        'custom printed vial packaging box for healthcare samples',
        'multi-vial paper box with organized insert cavities',
        'custom vial box with label panel for laboratory products',
        'rigid vial packaging box for premium sample kits',
        'paperboard vial box for cosmetic serum vials',
        'glass vial packaging boxes with custom printed paper structure',
    );
}

function custom_box_custom_vial_boxes_image_captions(): array
{
    return array(
        'Concept example of a protective vial box structure planned around insert fit.',
        'Concept example of custom printed vial packaging for healthcare sample kits.',
        'Concept example of a multi-vial box with organized insert cavities.',
        'Concept example of label-ready vial packaging with clear information panels.',
        'Concept example of a rigid vial packaging option for premium sample kits.',
        'Concept example of a paperboard box for cosmetic serum vials.',
        'Concept examples of vial boxes adjusted by size, insert depth and print layout.',
    );
}

function custom_box_custom_vial_boxes_image_titles(): array
{
    return array(
        'Custom Vial Boxes With Protective Insert',
        'Custom Printed Vial Packaging Box',
        'Multi-Vial Paper Box With Cavities',
        'Custom Vial Box With Label Panel',
        'Rigid Vial Packaging Box',
        'Paperboard Vial Box for Cosmetic Serum',
        'Glass Vial Packaging Boxes',
    );
}

function custom_box_custom_vial_boxes_seo_images(): array
{
    return array(
        'paper_insert' => array(
            'base' => 'custom-vial-box-with-paper-insert',
            'relative' => '2026/08/custom-vial-box-with-paper-insert.webp',
            'title' => 'Custom Vial Box with Paperboard Insert',
            'alt' => 'Custom vial box with die-cut paperboard insert holding a 10 mL amber glass vial',
            'caption' => 'Concept visualization of a custom folding vial carton with a protective paperboard insert.',
            'width' => 1448,
            'height' => 1086,
        ),
        'structure_options' => array(
            'base' => 'custom-vial-box-structure-options',
            'relative' => '2026/08/custom-vial-box-structure-options.webp',
            'title' => 'Custom Vial Box Structure Options',
            'alt' => 'Folding carton sleeve tray and rigid box structures for custom vial packaging',
            'caption' => 'Concept visualization comparing three manufacturable custom vial box structures.',
            'width' => 1448,
            'height' => 1086,
        ),
        'insert_comparison' => array(
            'base' => 'paper-insert-vs-eva-insert-vial-box',
            'relative' => '2026/08/paper-insert-vs-eva-insert-vial-box.webp',
            'title' => 'Paperboard vs EVA Vial Box Inserts',
            'alt' => 'Comparison of paperboard and EVA inserts for custom vial boxes',
            'caption' => 'Concept comparison of a folded paperboard cradle and an EVA insert for glass vial packaging.',
            'width' => 1448,
            'height' => 1086,
        ),
        'dieline_prototype' => array(
            'base' => 'custom-vial-box-dieline-prototype',
            'relative' => '2026/08/custom-vial-box-dieline-prototype.webp',
            'title' => 'Custom Vial Box Dieline Prototype',
            'alt' => 'Custom vial box dieline prototype amber vial and digital caliper on a worktable',
            'caption' => 'Concept visualization of the dieline and prototype preparation stage for a custom vial carton.',
            'width' => 1280,
            'height' => 960,
        ),
        'digital_prototype' => array(
            'base' => 'custom-vial-box-digital-prototype-process',
            'relative' => '2026/08/custom-vial-box-digital-prototype-process.webp',
            'title' => 'Custom Vial Box Digital Prototype Process',
            'alt' => 'Packaging engineer cutting a custom vial box prototype on a digital sample table',
            'caption' => 'Production process illustration of structural development and digital prototyping for a custom vial box.',
            'width' => 1200,
            'height' => 900,
        ),
        'offset_printing' => array(
            'base' => 'custom-vial-box-offset-printing-process',
            'relative' => '2026/08/custom-vial-box-offset-printing-process.webp',
            'title' => 'Custom Vial Box Offset Printing Process',
            'alt' => 'Offset printing press producing flat paperboard sheets for custom vial boxes',
            'caption' => 'Production process illustration of color printing on flat paperboard sheets before die cutting.',
            'width' => 1100,
            'height' => 825,
        ),
        'die_cutting' => array(
            'base' => 'custom-vial-box-die-cutting-process',
            'relative' => '2026/08/custom-vial-box-die-cutting-process.webp',
            'title' => 'Custom Vial Box Die-Cutting Process',
            'alt' => 'Operator inspecting a die-cut custom vial box blank beside an automatic platen machine',
            'caption' => 'Production process illustration of die cutting creasing and waste stripping for a vial box blank.',
            'width' => 1200,
            'height' => 900,
        ),
        'folding_gluing' => array(
            'base' => 'custom-vial-box-folding-gluing-insert-assembly',
            'relative' => '2026/08/custom-vial-box-folding-gluing-insert-assembly.webp',
            'title' => 'Custom Vial Box Folding, Gluing and Insert Assembly',
            'alt' => 'Workers assembling custom vial boxes beside an automatic folder-gluer line',
            'caption' => 'Production process illustration of carton folding gluing and paperboard insert assembly.',
            'width' => 1200,
            'height' => 900,
        ),
        'quality_control' => array(
            'base' => 'custom-vial-box-quality-control-export-packing',
            'relative' => '2026/08/custom-vial-box-quality-control-export-packing.webp',
            'title' => 'Custom Vial Box Quality Control and Export Packing',
            'alt' => 'Quality control worker checking a four-vial box with a digital caliper',
            'caption' => 'Production process illustration of finished vial box inspection and export packing.',
            'width' => 1200,
            'height' => 900,
        ),
    );
}

function custom_box_find_custom_vial_boxes_seo_attachment(string $base): int
{
    global $wpdb;

    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC",
        '%' . $wpdb->esc_like($base) . '%'
    ));

    foreach ($ids as $id) {
        $attached = (string) get_post_meta((int) $id, '_wp_attached_file', true);
        if ($base === pathinfo(wp_basename($attached), PATHINFO_FILENAME)) {
            return (int) $id;
        }
    }

    return 0;
}

function custom_box_create_custom_vial_boxes_seo_attachment(int $post_id, array $image): int
{
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
        return 0;
    }

    $relative = ltrim((string) $image['relative'], '/');
    $upload_path = trailingslashit($uploads['basedir']) . $relative;
    $bundle_path = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/' . $relative;

    if (!file_exists($upload_path) && file_exists($bundle_path)) {
        if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
            return 0;
        }
    }
    if (!file_exists($upload_path)) {
        return 0;
    }

    $type = wp_check_filetype(wp_basename($upload_path), null);
    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $type['type'] ?: 'image/webp',
        'post_title' => $image['title'],
        'post_excerpt' => $image['caption'],
        'post_status' => 'inherit',
        'post_parent' => $post_id,
    ), $upload_path, $post_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_wp_attached_file', $relative);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $upload_path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $image['alt']);

    return (int) $attachment_id;
}

function custom_box_sync_custom_vial_boxes_seo_images(int $product_id): array
{
    $stored = get_post_meta($product_id, '_custom_box_custom_vial_boxes_seo_image_ids', true);
    $ids = is_array($stored) ? array_map('absint', $stored) : array();

    foreach (custom_box_custom_vial_boxes_seo_images() as $key => $image) {
        $attachment_id = !empty($ids[$key]) ? (int) $ids[$key] : 0;
        if (!$attachment_id || 'attachment' !== get_post_type($attachment_id)) {
            $attachment_id = custom_box_find_custom_vial_boxes_seo_attachment($image['base']);
        }
        if (!$attachment_id) {
            $attachment_id = custom_box_create_custom_vial_boxes_seo_attachment($product_id, $image);
        }
        if (!$attachment_id) {
            continue;
        }

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $image['alt']);
        wp_update_post(array(
            'ID' => $attachment_id,
            'post_parent' => $product_id,
            'post_title' => $image['title'],
            'post_excerpt' => $image['caption'],
        ));
        $ids[$key] = $attachment_id;
    }

    update_post_meta($product_id, '_custom_box_custom_vial_boxes_seo_image_ids', $ids);

    return $ids;
}

function custom_box_custom_vial_boxes_seo_image_figure(int $product_id, string $key): string
{
    $images = custom_box_custom_vial_boxes_seo_images();
    $image = $images[$key] ?? array();
    $ids = get_post_meta($product_id, '_custom_box_custom_vial_boxes_seo_image_ids', true);
    $attachment_id = is_array($ids) && !empty($ids[$key]) ? (int) $ids[$key] : 0;
    $url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'large') : false;

    if (empty($image) || !$attachment_id || !$url) {
        return '';
    }

    return sprintf(
        '<div class="product-content-image-grid product-content-image-grid--single"><figure class="product-content-image-card product-content-image-card--single"><img src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure></div>',
        esc_url($url),
        esc_attr($image['alt']),
        (int) $image['width'],
        (int) $image['height'],
        esc_html($image['caption'])
    );
}

function custom_box_custom_vial_boxes_restore_inline_image_attributes(string $content): string
{
    $restored = preg_replace_callback('/<img\b[^>]*>/i', static function (array $matches): string {
        $tag = $matches[0];

        if (!preg_match('/\sdecoding\s*=/i', $tag)) {
            $tag = (string) preg_replace('/\s*\/?\s*>$/', ' decoding="async">', $tag, 1);
        }

        return $tag;
    }, $content);

    return is_string($restored) ? $restored : $content;
}

function custom_box_custom_vial_boxes_product_image_ids(int $product_id): array
{
    $image_ids = array();
    $featured_id = (int) get_post_thumbnail_id($product_id);

    if ($featured_id) {
        $image_ids[] = $featured_id;
    }

    $gallery_ids = array_filter(array_map('absint', explode(',', (string) get_post_meta($product_id, '_product_image_gallery', true))));

    foreach ($gallery_ids as $gallery_id) {
        if ($gallery_id && !in_array($gallery_id, $image_ids, true)) {
            $image_ids[] = $gallery_id;
        }
    }

    return $image_ids;
}

function custom_box_update_custom_vial_boxes_images(int $product_id): void
{
    $image_ids = custom_box_custom_vial_boxes_product_image_ids($product_id);
    $alts = custom_box_custom_vial_boxes_image_alts();
    $titles = custom_box_custom_vial_boxes_image_titles();
    $captions = custom_box_custom_vial_boxes_image_captions();

    foreach ($image_ids as $index => $image_id) {
        update_post_meta($image_id, '_wp_attachment_image_alt', $alts[$index] ?? $alts[0]);
        wp_update_post(array(
            'ID'           => $image_id,
            'post_parent'  => $product_id,
            'post_title'   => $titles[$index] ?? $titles[0],
            'post_excerpt' => $captions[$index] ?? $captions[0],
        ));
    }
}

function custom_box_update_custom_vial_boxes_specs(int $product_id): void
{
    $specs = get_post_meta($product_id, '_custom_box_product_specs', true);

    if (!is_array($specs)) {
        return;
    }

    foreach ($specs as &$spec) {
        if (empty($spec['label'])) {
            continue;
        }

        if ('Model Number' === $spec['label']) {
            $spec['value'] = 'VPN-CUSTOM-VIAL-BOXES';
        }

        if ('Product Name' === $spec['label']) {
            $spec['value'] = 'Custom Vial Boxes';
        }
    }
    unset($spec);

    update_post_meta($product_id, '_custom_box_product_specs', $specs);
}

function custom_box_custom_vial_boxes_image_grid(int $product_id, array $indexes): string
{
    $image_ids = custom_box_custom_vial_boxes_product_image_ids($product_id);
    $alts = custom_box_custom_vial_boxes_image_alts();
    $captions = custom_box_custom_vial_boxes_image_captions();
    $figures = array();

    foreach ($indexes as $index) {
        if (empty($image_ids[$index])) {
            continue;
        }

        $image_id = (int) $image_ids[$index];
        $image_url = wp_get_attachment_image_url($image_id, 'large');

        if (!$image_url) {
            continue;
        }

        $figures[] = sprintf(
            '<figure class="product-content-image-card"><img src="%s" alt="%s" loading="lazy" decoding="async"><figcaption>%s</figcaption></figure>',
            esc_url($image_url),
            esc_attr($alts[$index] ?? $alts[0]),
            esc_html($captions[$index] ?? $captions[0])
        );
    }

    if (empty($figures)) {
        return '';
    }

    return '<div class="product-content-image-grid">' . implode('', $figures) . '</div>';
}

function custom_box_custom_vial_boxes_long_description(int $product_id): string
{
    $pharma_url = esc_url('https://hopgiayvpn.com/products/pharmaceutical-packaging-boxes/');
    $manufacturer_url = esc_url('https://hopgiayvpn.com/custom-packaging-boxes-manufacturer/');
    $contact_url = esc_url('https://hopgiayvpn.com/contact/');
    $printed_url = esc_url('https://hopgiayvpn.com/products/custom-printed-paper-boxes/');
    $rigid_url = esc_url('https://hopgiayvpn.com/products/rigid-boxes/');
    $folding_url = esc_url('https://hopgiayvpn.com/products/folding-carton-boxes/');
    $materials_url = esc_url('https://hopgiayvpn.com/paper-materials-for-custom-paper-boxes/');
    $insert_guide_url = esc_url('https://hopgiayvpn.com/packaging-inserts-protection-presentation/');
    $medical_kit_url = esc_url('https://hopgiayvpn.com/product/custom-medical-kit-packaging-box/');
    $paper_insert_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'paper_insert');
    $structure_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'structure_options');
    $insert_comparison_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'insert_comparison');
    $digital_prototype_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'digital_prototype');
    $offset_printing_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'offset_printing');
    $die_cutting_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'die_cutting');
    $folding_gluing_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'folding_gluing');
    $quality_control_image = custom_box_custom_vial_boxes_seo_image_figure($product_id, 'quality_control');

    return <<<HTML
<section class="product-seo-content custom-vial-boxes-content">

  <h2>Custom Vial Boxes Built Around the Vial and Insert</h2>
  <p>Custom vial boxes need more control than a generic carton because a small glass vial can move, tilt or contact the box wall during handling. The structure should be planned around the vial diameter, height, cap or closure, insert depth, opening direction and information panel before artwork is finalized. That approach helps procurement teams compare a folding carton, sleeve-and-tray box or rigid kit on the factors that affect fit and production.</p>
  <p>VPN Paper Box Manufacturer develops custom vial packaging boxes for brands, laboratories, supplement companies, healthcare suppliers, cosmetic sample programs and distributors. The product page is focused on standalone vial boxes, single-vial cartons, multi-vial cartons and protective inserts. The final structure, material and print specification should be confirmed with the real vial and an approved sample.</p>
  <aside class="vial-answer-summary" aria-label="Custom vial box sizing summary">
    <p><strong>Short answer:</strong> choose the insert only after the finished vial has been measured. A nominal 2 mL, 5 mL or 10 mL volume does not define the body diameter, total height, shoulder, cap, label projection or filled weight. Those physical inputs—not the volume label alone—determine the cavity and outer box.</p>
  </aside>

  {$paper_insert_image}

  <h2>Quick Specifications for Custom Vial Packaging</h2>
  <div class="seo-table-wrapper">
    <table class="seo-product-table">
      <thead>
        <tr>
          <th>Specification</th>
          <th>Available Direction</th>
          <th>What to Confirm</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Box styles</td>
          <td>Folding carton, sleeve and tray, drawer box or rigid vial kit</td>
          <td>Opening direction, presentation and packing method</td>
        </tr>
        <tr>
          <td>Insert options</td>
          <td>Die-cut paperboard, EVA, foam, molded pulp, dividers or paper tray</td>
          <td>Vial diameter, depth, clearance and movement control</td>
        </tr>
        <tr>
          <td>Vial capacity</td>
          <td>Single-vial, multi-vial or a custom cavity layout</td>
          <td>Vial count, SKU arrangement and pack-out sequence</td>
        </tr>
        <tr>
          <td>Materials and finishes</td>
          <td>Paperboard, SBS, kraft or rigid board with selected finishing</td>
          <td>Board thickness, print coverage and surface protection</td>
        </tr>
        <tr>
          <td>MOQ and lead time</td>
          <td>Confirmed during quotation</td>
          <td>Structure, material, insert, artwork and order quantity</td>
        </tr>
      </tbody>
    </table>
  </div>

  <h2>Custom Vial Box Styles for Different Buying Programs</h2>
  <p>A <a href="{$folding_url}">folding carton</a> is a practical direction for single-vial products, sample distribution and retail-ready information panels. A sleeve-and-tray structure separates the printed outer sleeve from the inner support, which can make loading and presentation easier to review. A <a href="{$rigid_url}">rigid vial kit</a> gives a more substantial hand feel for premium sample programs or launch sets. Drawer and multi-cavity structures can organize several vials while keeping the opening experience clear.</p>
  <p>The correct choice depends on how the buyer receives, stores and packs the vial. A simple structure may be more efficient when one vial and one label panel are required. A multi-vial carton may need dividers, a fixed orientation and a clear count of cavities. The structure should be evaluated with the finished insert, not from the outside artwork alone.</p>

  {$structure_image}

  <h2>Protective Insert Options for Glass Vials</h2>
  <p>The insert controls vial position, separation and removal. There is no universal “best” material: the appropriate choice depends on the actual vial, packing process, box style, presentation goal and agreed handling checks. Our <a href="{$insert_guide_url}">paper-box insert design guide</a> explains the wider relationship between fit, protection and presentation.</p>
  <div class="seo-table-wrapper" id="vial-insert-comparison-table">
    <table class="seo-product-table">
      <thead>
        <tr>
          <th>Insert type</th>
          <th>Practical fit</th>
          <th>Trade-off to review</th>
          <th>Approve with the sample</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Die-cut paperboard</strong></td>
          <td>Folding cartons, lightweight vials and printable all-paper presentations</td>
          <td>Fold geometry and unsupported spans affect rigidity</td>
          <td>Tab lock, cavity clearance, board spring-back and removal access</td>
        </tr>
        <tr>
          <td><strong>EVA</strong></td>
          <td>Rigid kits needing a precise-looking cavity and consistent presentation</td>
          <td>Material appearance, density and project-specific disposal requirements</td>
          <td>Cavity tolerance, compression, surface contact and extraction force</td>
        </tr>
        <tr>
          <td><strong>Foam</strong></td>
          <td>Cushioning-focused packs or irregular product envelopes</td>
          <td>Foam grade, recovery and compatibility must be specified</td>
          <td>Compression set, dust or odor expectations, cap clearance and movement</td>
        </tr>
        <tr>
          <td><strong>Molded pulp</strong></td>
          <td>Repeatable tray layouts where molded geometry and a fiber appearance suit the brief</td>
          <td>Tooling, surface texture and dimensional tolerance differ from cut inserts</td>
          <td>Part variation, nesting, edge contact, loading direction and vial removal</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p>Paper dividers can also organize multi-vial cartons. Whichever route is selected, a sample with representative finished vials should be checked for side contact, clearance, insert depth, closure pressure and removal effort before approval.</p>

  {$insert_comparison_image}

  <h2>Size Planning for 2 mL, 5 mL, 10 mL and Multi-Vial Boxes</h2>
  <p>The volume is useful for search and product identification, but it is not a manufacturing dimension. Two vials with the same nominal capacity can use different bodies, shoulders, caps and labels. Use the table as an RFQ checklist, then confirm every structure with controlled dimensions or physical samples.</p>
  <div class="seo-table-wrapper" id="vial-size-planning-table">
    <table class="seo-product-table">
      <thead>
        <tr>
          <th>Nominal format</th>
          <th>Measurements required</th>
          <th>Practical starting structure</th>
          <th>Sample approval focus</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>2 mL vial</strong></td>
          <td>Maximum body diameter, shoulder, total height, closure and label projection</td>
          <td>Compact folding carton with a folded paperboard cradle</td>
          <td>Small-part removal, cap clearance and movement inside the cavity</td>
        </tr>
        <tr>
          <td><strong>5 mL vial</strong></td>
          <td>Finished dimensions, filled weight and safe contact areas</td>
          <td>Folding carton or sleeve-and-tray box with a fitted insert</td>
          <td>Body support, label contact, opening direction and closure pressure</td>
        </tr>
        <tr>
          <td><strong>10 mL vial</strong></td>
          <td>Body, shoulder, cap or dropper envelope, label and filled weight</td>
          <td>Folding carton for one vial or rigid kit for a premium program</td>
          <td>Insert depth, headspace, sidewall contact and removal effort</td>
        </tr>
        <tr>
          <td><strong>Multi-vial pack</strong></td>
          <td>Vial count, maximum and minimum envelopes, spacing and pack-out sequence</td>
          <td>Divider, cavity tray, sleeve-and-tray or rigid multi-cavity kit</td>
          <td>Vial-to-vial separation, cavity numbering, orientation and count</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p>If several SKUs share one outer box, provide the largest and smallest controlled dimensions and representative samples from the range. For kits that combine a vial with accessories or instructions, compare our <a href="{$medical_kit_url}">custom medical kit packaging</a> approach before the cavity layout is finalized.</p>

  <section class="vial-fit-approval-example" id="vial-fit-approval-example" aria-labelledby="vial-fit-example-heading">
    <h2 id="vial-fit-example-heading">Worked Fit-Approval Example: 10 mL Vial Box</h2>
    <p class="vial-example-disclosure"><strong>Evidence note:</strong> this is an illustrative engineering workflow, not a named customer case study, certified laboratory result or performance guarantee. Project results depend on the actual vial, specification and agreed test method.</p>
    <div class="seo-table-wrapper">
      <table class="seo-product-table">
        <tbody>
          <tr>
            <th scope="row">Project input</th>
            <td>Representative filled 10 mL vials; controlled maximum body, shoulder, total height and cap dimensions; label projection; filled weight; quantity and destination.</td>
          </tr>
          <tr>
            <th scope="row">Observed design risks</th>
            <td>Body movement, contact with the outer wall, cap pressure when closed and insufficient finger access for removal.</td>
          </tr>
          <tr>
            <th scope="row">Starting structure</th>
            <td>Folding carton with a die-cut paperboard cradle sized from the controlled product envelope, plus defined headspace and a removal feature.</td>
          </tr>
          <tr>
            <th scope="row">Prototype review</th>
            <td>Load representative minimum and maximum samples; close and reopen the carton; inspect contact points, orientation, insert seating and removal during the agreed handling review.</td>
          </tr>
          <tr>
            <th scope="row">Approval record</th>
            <td>Sign off the dieline and physical sample only after the accepted cavity clearance, closure, print position and pack-out sequence are documented.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
  <p class="vial-visual-evidence-note"><strong>About the visuals on this page:</strong> the gallery, process and comparison visuals are identified as concept examples, concept visualizations or production-process illustrations. They help explain the structure; a project-specific physical sample with the real vial remains the approval reference.</p>

  <h2>Materials, Printing and Information Panels</h2>
  <p>Material selection should follow the vial weight, sales channel, print requirements and protective insert. SBS or ivory paperboard can support clean graphics and small information panels. Kraft or recycled paperboard can support a natural visual direction when the board and insert still provide the required support. Rigid board is suited to a premium kit when the added structure is justified by the presentation goal. Buyers can review <a href="{$materials_url}">paper material options for custom paper boxes</a> before confirming the specification.</p>
  <p><a href="{$printed_url}">Custom printed paper boxes</a> for vials can use CMYK printing, Pantone matching, foil stamping, embossing, debossing, spot UV, matte lamination or gloss lamination. The dieline should reserve readable areas for product name, dosage or usage information supplied by the brand, batch or lot fields, barcode, QR code, warning text and multilingual panels. Any regulated wording remains the buyer’s responsibility to approve.</p>

  <h2>In-House Manufacturing Process for Custom Vial Boxes</h2>
  <p>Custom vial packaging requires coordination between structural design, printing, converting, insert production and final inspection. Our integrated manufacturing workflow keeps these stages under one production system, allowing the packaging team to check how the carton, insert and vial work together before an order moves into mass production.</p>
  <p>As a <a href="{$manufacturer_url}">custom packaging manufacturer in Vietnam</a>, we coordinate structural development, printing, converting, insert preparation and inspection within one production workflow.</p>
  <p>Rather than treating the printed carton and protective insert as separate components, we develop them as one packaging structure. The vial dimensions, closure, cavity layout, information panels and packing method are reviewed from the beginning of the project. This approach gives buyers a clearer approval process and reduces the risk of discovering structural problems after printing has started.</p>

  <h3>1. Vial Measurement and Structural Engineering</h3>
  <p>Production begins with the actual vial dimensions or a physical product sample supplied by the buyer. Our structural team reviews the vial height, body diameter, cap or closure, label projection, required clearance, number of vials per box and preferred opening direction.</p>
  <p>The information is used to prepare a CAD dieline covering:</p>
  <ul>
    <li>Outer carton dimensions.</li>
    <li>Panel and flap positions.</li>
    <li>Fold and crease lines.</li>
    <li>Glue areas.</li>
    <li>Insert depth.</li>
    <li>Vial cavity layout.</li>
    <li>Barcode and information zones.</li>
    <li>Loading and removal direction.</li>
  </ul>
  <p>For multi-vial packaging, the layout also defines the number and spacing of cavities so that each vial has a controlled position inside the box.</p>

  <h3>2. Digital Prototype and Fit Testing</h3>
  <p>Before production tooling is prepared, a digital cutting system can be used to produce a white sample or printed prototype. This allows the carton and insert to be assembled without committing the project directly to mass production.</p>
  <p>The prototype is checked with the real vial whenever possible. Our team reviews cavity fit, side clearance, insert depth, closure pressure, panel alignment and the amount of effort required to remove the vial. If the vial moves excessively, contacts the carton wall or is difficult to remove, the dieline can be adjusted before approval.</p>
  <p>The buyer can review both the flat dieline and the assembled sample before authorizing production.</p>
  {$digital_prototype_image}

  <h3>3. Material Preparation and Color-Controlled Printing</h3>
  <p>After the structure and artwork are approved, the selected paperboard is prepared for printing. Depending on the required appearance and structure, the specification may use SBS or ivory paperboard, kraft paperboard, coated stock or printed wrapping paper for a rigid vial kit.</p>
  <p>Multi-color sheetfed offset printing equipment is used for production programs requiring consistent graphics, small information panels, barcodes and brand colors. Print registration, ink density and color consistency are checked against the approved artwork and production reference.</p>
  <p>Where required by the design, the printed sheet can receive a protective coating or move to a separate finishing stage. All regulated product wording, usage information, warnings and market-specific labeling must be supplied and approved by the buyer.</p>
  {$offset_printing_image}

  <h3>4. Surface Finishing</h3>
  <p>The printed sheets can be processed with finishing methods selected for the project, including:</p>
  <ul>
    <li>Matte or gloss lamination.</li>
    <li>Hot foil stamping.</li>
    <li>Embossing or debossing.</li>
    <li>Spot UV.</li>
    <li>Protective coating.</li>
    <li>Selected decorative effects.</li>
  </ul>
  <p>Finishing is reviewed in relation to the board, print coverage, fold lines and information panels. Decorative effects should not interfere with barcode readability, small text or the areas required for batch and lot information.</p>
  <p>The available finishing combination is confirmed during quotation and sampling.</p>

  <h3>5. Automatic Die-Cutting and Creasing</h3>
  <p>After printing and finishing, automatic die-cutting equipment converts the printed sheets into the approved carton shape. The cutting tool creates the outer profile, openings and internal features, while the creasing rules form the fold lines needed for accurate assembly.</p>
  <p>At this stage, operators check:</p>
  <ul>
    <li>Cutting registration against the printed artwork.</li>
    <li>Crease position and folding direction.</li>
    <li>Flap and locking-tab dimensions.</li>
    <li>Waste removal.</li>
    <li>Surface damage around cut and crease lines.</li>
    <li>Consistency between the approved sample and production sheets.</li>
  </ul>
  <p>Accurate die-cutting is especially important for small vial cartons because a minor change in the insert opening or fold position can affect how the vial sits inside the finished package.</p>
  {$die_cutting_image}

  <h3>6. Insert Cutting and Cavity Preparation</h3>
  <p>Protective inserts are produced according to the approved material and cavity layout. Die-cut paperboard inserts can be folded into a cradle or internal support. Paper dividers can separate several vials inside one carton. EVA or foam inserts can be cut with individual cavities when a rigid kit requires a close-fitting presentation. Molded-pulp trays require an approved molded geometry, tolerance and loading direction.</p>
  <p>The insert is checked separately before it is assembled with the outer box. Important inspection points include cavity diameter, depth, spacing, edge condition and alignment with the box opening.</p>
  <p>The final insert material and cutting method depend on the vial weight, packing process, presentation requirement and approved sample.</p>

  <h3>7. Folding, Gluing and Box Assembly</h3>
  <p>Folding cartons move through folding and gluing equipment to form the required seams and panels. Operators monitor fold alignment, glue application, opening function and final carton shape.</p>
  <p>Sleeve-and-tray boxes, drawer boxes and rigid vial kits follow the assembly sequence required by their structure. Inserts are positioned according to the approved sample so that the cavity layout remains aligned with the outer box.</p>
  <p>The production team performs in-process checks instead of waiting until the entire order has been completed. This makes it possible to identify print, cutting, gluing or insert problems earlier in the production run.</p>
  {$folding_gluing_image}

  <h3>8. Finished-Product Quality Control</h3>
  <p>Quality control covers both the appearance of the packaging and its functional fit. Inspection points can include:</p>
  <ul>
    <li>Board and material specification.</li>
    <li>Print color and registration.</li>
    <li>Dieline and finished dimensions.</li>
    <li>Cutting and crease accuracy.</li>
    <li>Glue-seam strength and cleanliness.</li>
    <li>Insert position and cavity layout.</li>
    <li>Vial clearance and movement.</li>
    <li>Closure and opening function.</li>
    <li>Barcode and information-panel readability.</li>
    <li>Surface finishing and visible defects.</li>
    <li>Quantity and export-carton markings.</li>
  </ul>
  <p>A packed sample can be checked with the actual vial before shipment. This inspection provides a production acceptance reference, but it does not replace the buyer’s own product, labeling, transport or regulatory validation.</p>
  {$quality_control_image}

  <h3>9. Export Packing and International Supply Experience</h3>
  <p>Our team has supplied custom vial boxes and related paper packaging for customers in international markets including India, the United States, the United Kingdom, Pakistan and Australia. These projects have given us experience working with different box structures, artwork requirements, shipping destinations and export-packing instructions.</p>
  <p>Buyers sourcing <a href="{$pharma_url}">pharmaceutical packaging boxes</a> can also review our broader packaging category before confirming the structure, insert and artwork requirements for a specific vial project.</p>
  <p>Before shipment, finished vial boxes are counted, protected and packed into export cartons according to the confirmed packing plan. Carton markings, packing quantities and shipping documentation are prepared according to the approved order requirements.</p>
  <p>Market-specific product claims, pharmaceutical labeling and regulatory wording remain subject to the buyer’s approval. Our responsibility is to manufacture the packaging according to the approved structure, artwork, material specification and production sample.</p>

  <h2>From Dieline to Finished Vial Packaging</h2>
  <p>By coordinating structural development, sampling, printing, finishing, die-cutting, insert preparation, assembly, inspection and export packing, VPN Paper Box Manufacturer can support custom vial box projects from the initial packaging brief through finished production.</p>
  <p>To begin a project, use the RFQ checklist below. A product photo, reference box or physical vial sample will help the packaging team prepare a more accurate structural recommendation.</p>

  <h2>MOQ, Lead Time and Quotation Requirements</h2>
  <p>Minimum order quantity and lead time depend on the confirmed box structure, board or rigid material, insert type, finishing, artwork status and order quantity. They should be confirmed in the quotation rather than assumed from a generic product page. Shipping and export packing requirements should also be discussed for the destination market and the finished pack-out.</p>

  <h2>Request a Quote for Custom Vial Boxes</h2>
  <p>For a useful structural quotation, send:</p>
  <ul class="vial-rfq-checklist">
    <li>maximum vial body diameter and total height;</li>
    <li>shoulder, cap, stopper or dropper dimensions;</li>
    <li>label, sleeve or seal projection outside the glass body;</li>
    <li>filled weight and any no-contact areas;</li>
    <li>number of vials per box and the required arrangement;</li>
    <li>preferred box style and paperboard, EVA, foam or molded-pulp insert;</li>
    <li>total order quantity by SKU and artwork status;</li>
    <li>destination market, shipping method and any agreed sample tests.</li>
  </ul>
  <p>Need custom vial boxes with inserts for glass vials, laboratory samples, supplement products or cosmetic sample programs? The team can compare folding cartons, sleeve-and-tray boxes and rigid kits, then prepare a dieline and physical sample for review.</p>
  <p><a class="vial-rfq-button" href="{$contact_url}">Request a custom vial box quotation</a></p>

</section>
HTML;
}

function custom_box_custom_vial_boxes_faq_html(): string
{
    return <<<'HTML'
<section class="product-faq custom-vial-boxes-faq">
  <div class="container">
    <h2>Custom Vial Boxes FAQ</h2>

    <details class="faq-item">
      <summary>Can you make custom vial boxes for different vial sizes?</summary>
      <div class="faq-answer"><p>Yes. The box and insert can be developed around the vial height, diameter, cap or closure, required clearance and vial count. Send the real product dimensions so the structure can be checked before artwork approval.</p></div>
    </details>

    <details class="faq-item">
      <summary>Is a 2 mL, 5 mL or 10 mL label enough to size the box?</summary>
      <div class="faq-answer"><p>No. Nominal capacity does not define the finished vial envelope. Provide the maximum body diameter, total height, shoulder, cap or closure, label projection and filled weight. The cavity and outer box should be approved with controlled dimensions or representative physical samples.</p></div>
    </details>

    <details class="faq-item">
      <summary>Which insert is better for glass vials: paperboard, EVA, foam or molded pulp?</summary>
      <div class="faq-answer"><p>It depends on the vial, box style, cavity tolerance, packing method and presentation goal. Paperboard can suit folding cartons, EVA or foam can form close-fitting cavities, and molded pulp can suit repeatable fiber-tray layouts. The final choice should be approved with representative vials.</p></div>
    </details>

    <details class="faq-item">
      <summary>Can one box hold multiple vials?</summary>
      <div class="faq-answer"><p>Yes. Multi-vial cartons can use dividers, paperboard cavities or a tray layout to separate the products. The vial count, cavity arrangement and loading sequence should be confirmed before the dieline is finalized.</p></div>
    </details>

    <details class="faq-item">
      <summary>What information is required to create a vial box dieline?</summary>
      <div class="faq-answer"><p>Please provide the vial height, diameter, cap or closure dimensions, vial count, insert preference, box style, information panels, artwork status and shipping market. A product photo or physical sample is useful when the shape has shoulders, a label projection or an unusual closure.</p></div>
    </details>

    <details class="faq-item">
      <summary>Can I order a prototype before mass production?</summary>
      <div class="faq-answer"><p>Prototype and sample approval can be discussed as part of the quotation and structure process. The sample should be tested with the real vial for fit, closure, insert alignment, print position and handling before bulk production is approved.</p></div>
    </details>

    <details class="faq-item">
      <summary>What is the minimum order quantity for custom vial boxes?</summary>
      <div class="faq-answer"><p>The minimum order quantity is confirmed during quotation because it depends on the box structure, material, insert, finishing and order quantity. Send the target quantity with the product dimensions so the team can confirm the applicable production requirements.</p></div>
    </details>
  </div>
</section>
HTML;
}

function custom_box_custom_vial_boxes_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $error = (string) get_option('custom_box_custom_vial_boxes_sync_error', '');

    if ('' === $error) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Custom vial boxes sync:</strong> ' . esc_html($error) . '</p></div>';
}

function custom_box_is_custom_vial_boxes_product_page(): bool
{
    return function_exists('is_product')
        && is_product()
        && 'custom-vial-packaging-box' === get_post_field('post_name', get_queried_object_id());
}

function custom_box_custom_vial_boxes_canonical_url(): string
{
    return home_url('/product/custom-vial-packaging-box/');
}

function custom_box_custom_vial_boxes_rank_math_canonical($canonical)
{
    if (custom_box_is_custom_vial_boxes_product_page()) {
        return custom_box_custom_vial_boxes_canonical_url();
    }

    return $canonical;
}

function custom_box_custom_vial_boxes_output_canonical_fallback(): void
{
    if (defined('RANK_MATH_VERSION') || !custom_box_is_custom_vial_boxes_product_page()) {
        return;
    }

    echo '<link rel="canonical" href="' . esc_url(custom_box_custom_vial_boxes_canonical_url()) . '" />' . "\n";
}

function custom_box_custom_vial_boxes_output_styles(): void
{
    if (!custom_box_is_custom_vial_boxes_product_page()) {
        return;
    }
    ?>
    <style>
        .custom-vial-boxes-content {
            margin-top: 40px;
        }

        .custom-vial-boxes-content h2,
        .custom-vial-boxes-faq h2 {
            margin-top: 32px;
            margin-bottom: 14px;
        }

        .custom-vial-boxes-content p,
        .custom-vial-boxes-faq p {
            margin-bottom: 16px;
            line-height: 1.7;
        }

        .seo-table-wrapper {
            overflow-x: auto;
            margin: 24px 0;
        }

        .seo-product-table {
            width: 100%;
            border-collapse: collapse;
        }

        .seo-product-table th,
        .seo-product-table td {
            padding: 12px 14px;
            border: 1px solid rgba(0, 0, 0, 0.12);
            text-align: left;
            vertical-align: top;
        }

        .vial-answer-summary,
        .vial-fit-approval-example,
        .vial-visual-evidence-note {
            padding: 18px 20px;
            border: 1px solid #cbdde8;
            border-left: 4px solid #1c79ad;
            border-radius: 8px;
            background: #f7fbfd;
        }

        .vial-answer-summary,
        .vial-visual-evidence-note {
            margin: 22px 0;
        }

        .vial-fit-approval-example {
            margin: 32px 0;
        }

        .vial-fit-approval-example h2 {
            margin-top: 0;
        }

        .vial-example-disclosure {
            color: #24435a;
        }

        .vial-rfq-checklist {
            columns: 2;
            column-gap: 36px;
            margin: 0 0 24px;
        }

        .vial-rfq-checklist li {
            break-inside: avoid;
            margin-bottom: 10px;
        }

        .vial-rfq-button {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 6px;
            background: #123b5d;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .vial-rfq-button:hover,
        .vial-rfq-button:focus-visible {
            background: #1c79ad;
            color: #fff;
        }

        .product-content-image-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin: 28px 0;
        }

        .product-content-image-grid--single {
            grid-template-columns: minmax(0, 1fr);
            justify-items: center;
        }

        .product-content-image-grid--single .product-content-image-card--single {
            width: min(100%, 640px);
            margin-inline: auto;
        }

        .product-content-image-card {
            margin: 0;
        }

        .product-content-image-grid img {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: contain;
            display: block;
            border-radius: 8px;
            background: #f7f7f5;
        }

        .product-content-image-grid--single img {
            height: auto;
        }

        .custom-vial-boxes-faq {
            padding: 64px 0 52px;
        }

        .custom-vial-boxes-faq .container {
            max-width: 1040px;
            margin-inline: auto;
            padding-inline: 24px;
        }

        .custom-vial-boxes-faq h2 {
            margin-bottom: 24px;
            color: #123b5d;
            font-size: clamp(1.75rem, 2.3vw, 2.15rem);
            line-height: 1.2;
        }

        .custom-vial-boxes-faq .faq-item {
            margin: 0 0 16px;
            border: 1px solid #d4e0e8;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(24, 66, 96, 0.07);
            overflow: hidden;
        }

        .custom-vial-boxes-faq .faq-item summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 21px 26px;
            color: #123b5d;
            font-size: clamp(1.05rem, 1.2vw, 1.2rem);
            font-weight: 600;
            line-height: 1.5;
            cursor: pointer;
            list-style: none;
        }

        .custom-vial-boxes-faq .faq-item summary:hover {
            background: #f7fbfd;
        }

        .custom-vial-boxes-faq .faq-item summary::-webkit-details-marker {
            display: none;
        }

        .custom-vial-boxes-faq .faq-item summary::after {
            flex: 0 0 auto;
            width: 32px;
            height: 32px;
            border: 1px solid #b9cbd8;
            border-radius: 50%;
            color: #123b5d;
            content: '+';
            font-size: 1.35rem;
            font-weight: 400;
            line-height: 29px;
            text-align: center;
        }

        .custom-vial-boxes-faq .faq-item[open] summary::after {
            content: '−';
        }

        .custom-vial-boxes-faq .faq-item summary:focus-visible {
            outline: 3px solid rgba(28, 121, 173, 0.35);
            outline-offset: -3px;
        }

        .custom-vial-boxes-faq .faq-answer {
            max-height: none;
            overflow: visible;
            padding: 0 26px 25px;
            border-top: 1px solid #edf2f5;
            background: #fcfeff;
        }

        .custom-vial-boxes-faq .faq-answer p {
            margin: 18px 0 0;
            color: #24435a;
            font-size: 1rem;
            line-height: 1.75;
        }

        @media (max-width: 767px) {
            .product-content-image-grid {
                grid-template-columns: 1fr;
            }

            .vial-rfq-checklist {
                columns: 1;
            }

            .custom-vial-boxes-faq {
                padding: 48px 0 36px;
            }

            .custom-vial-boxes-faq .container {
                padding-inline: 16px;
            }

            .custom-vial-boxes-faq h2 {
                font-size: 1.65rem;
            }

            .custom-vial-boxes-faq .faq-item summary {
                gap: 16px;
                padding: 18px;
                font-size: 1.05rem;
            }

            .custom-vial-boxes-faq .faq-answer {
                padding: 0 18px 21px;
            }
        }
    </style>
    <?php
}

function custom_box_custom_vial_boxes_schema(): array
{
    return array(
        '@context'     => 'https://schema.org/',
        '@type'        => 'Product',
        'name'         => 'Custom Vial Boxes with Protective Inserts',
        'description'  => 'Custom vial boxes with paperboard, EVA, foam or molded-pulp inserts for nominal 2 mL, 5 mL, 10 mL and multi-vial packaging, sized from the finished vial.',
        'url'          => custom_box_custom_vial_boxes_canonical_url(),
        'brand'        => array(
            '@type' => 'Brand',
            'name'  => 'VPN Paper Box Manufacturer',
        ),
        'manufacturer' => array(
            '@type'   => 'Organization',
            'name'    => 'VPN Paper Box Manufacturer',
            'address' => array(
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Ho Chi Minh City',
                'addressCountry'  => 'VN',
            ),
        ),
        'sku'          => 'custom-vial-boxes',
        'category'     => 'Vial Packaging Boxes',
        'additionalProperty' => array(
            array(
                '@type' => 'PropertyValue',
                'name'  => 'Packaging scope',
                'value' => 'Secondary paper packaging for glass vials',
            ),
            array(
                '@type' => 'PropertyValue',
                'name'  => 'Nominal vial formats',
                'value' => '2 mL, 5 mL, 10 mL and multi-vial; final dimensions required',
            ),
            array(
                '@type' => 'PropertyValue',
                'name'  => 'Insert options',
                'value' => 'Paperboard, EVA, foam and molded pulp',
            ),
            array(
                '@type' => 'PropertyValue',
                'name'  => 'Approval input',
                'value' => 'Controlled dimensions or representative physical vials before production',
            ),
        ),
    );
}

function custom_box_custom_vial_boxes_rank_math_json_ld($data)
{
    if (!is_array($data) || !custom_box_is_custom_vial_boxes_product_page()) {
        return $data;
    }

    $schema = custom_box_custom_vial_boxes_schema();
    unset($schema['@context']);

    $has_product_schema = false;

    foreach ($data as $key => $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $types = isset($entity['@type']) ? (array) $entity['@type'] : array();

        if (array_intersect($types, array('Product', 'WooCommerceProduct', 'ProductGroup'))) {
            $data[$key] = array_merge($entity, $schema);
            $has_product_schema = true;
        }
    }

    if (!$has_product_schema) {
        $data['customVialBoxesProduct'] = $schema;
    }

    return $data;
}

function custom_box_custom_vial_boxes_output_schema_fallback(): void
{
    if (defined('RANK_MATH_VERSION') || !custom_box_is_custom_vial_boxes_product_page()) {
        return;
    }

    echo '<script type="application/ld+json">' . wp_json_encode(custom_box_custom_vial_boxes_schema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
