<?php
/**
 * Import five supplied Christmas paper-bag visualizations as WooCommerce products.
 *
 * The attached package identifies these as AI-generated concept mockups. This
 * importer keeps that disclosure visible and does not invent dimensions, paper
 * weight, load capacity, food-contact suitability, certification or MOQ.
 *
 * Run after a pull or from Product Sample Deploy:
 *   php tools/import-alibaba-christmas-paper-bags-20260917.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

defined('ABSPATH') || exit;

$marker = 'alibaba-christmas-paper-bags-20260917';
$upload_dir = wp_upload_dir();
$asset_dir = trailingslashit($upload_dir['basedir']) . '2026/09/';
$bundle_asset_dir = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/09/';
$category_slugs = array('christmas-packaging', 'paper-bags-with-logo', 'corporate-gift-packaging');

// Pull-deploy support: restore only this collection's tracked originals into
// normal uploads before creating Media Library attachments.
if (!is_dir($asset_dir)) {
    wp_mkdir_p($asset_dir);
}
foreach (glob($bundle_asset_dir . '*christmas-paper-bag*.jpg') ?: array() as $bundled_file) {
    $target_file = $asset_dir . wp_basename($bundled_file);
    if (!file_exists($target_file)) {
        copy($bundled_file, $target_file);
    }
}

$products = array(
    array(
        'slug' => 'custom-red-snowflake-twisted-handle-christmas-paper-bag',
        'title' => 'Custom Red Snowflake Twisted-Handle Christmas Paper Bag',
        'prefix' => 'red-snowflake-twisted-handle-christmas-paper-bag',
        'keyword' => 'custom red Christmas paper bag',
        'seo_title' => 'Red Snowflake Christmas Paper Bag | VPN Packaging',
        'seo_description' => 'Custom red snowflake Christmas paper bag with twisted handles. Confirm paper grade, handle bonding, size, print and load by approved sample.',
        'structure' => 'Portrait rectangular paper gift bag with moderate rectangular side gussets and a standing glued base.',
        'material' => 'Matte red printed paper exterior with an unprinted ivory interior; paper grade and caliper are confirmed from the approved brief and physical sample.',
        'handle' => 'Exactly two ivory twisted-paper arch handles, one on the front and one on the rear, with bonded ends under paper reinforcement patches.',
        'artwork' => 'Small evenly spaced ivory six-arm snowflakes and tiny dots repeat across the front, rear and side gussets; no foil, logo or text is shown.',
        'applications' => 'Corporate holiday gifts, confectionery with a separate primary food-contact layer, candles, beauty sets, accessories and Christmas hampers.',
        'tags' => array('Christmas paper bag', 'red gift bag', 'snowflake packaging', 'twisted paper handles', 'seasonal retail bag'),
    ),
    array(
        'slug' => 'custom-kraft-evergreen-flat-handle-christmas-paper-bag',
        'title' => 'Custom Kraft Evergreen Flat-Handle Christmas Paper Bag',
        'prefix' => 'kraft-evergreen-flat-handle-christmas-paper-bag',
        'keyword' => 'custom kraft Christmas paper bag',
        'seo_title' => 'Kraft Evergreen Christmas Paper Bag | VPN',
        'seo_description' => 'Custom kraft evergreen Christmas paper bag with flat paper handles. Confirm paper grade, print, handle bonding, size and load by sample.',
        'structure' => 'Medium-tall rectangular kraft paper bag with rectangular side gussets, folded top rim and a standing glued base.',
        'material' => 'Natural brown kraft paper exterior and plain kraft interior; paper grade, caliper and recycled-content claims require written confirmation.',
        'handle' => 'Exactly two natural-brown flat-paper loop handles with square folded bends and glued ends beneath internal rectangular reinforcement patches.',
        'artwork' => 'Small dark-green geometric fir trees and red stars repeat on the front and rear while the side gussets remain plain kraft; ordinary flat ink is shown.',
        'applications' => 'Lightweight corporate gifts, candles, soap, accessories, confectionery with an inner wrap, retail promotions and event handouts.',
        'tags' => array('kraft Christmas bag', 'flat paper handles', 'evergreen packaging', 'natural paper bag', 'seasonal gift bag'),
    ),
    array(
        'slug' => 'custom-ivory-holly-rope-handle-christmas-paper-bag',
        'title' => 'Custom Ivory Holly Rope-Handle Christmas Paper Bag',
        'prefix' => 'ivory-holly-rope-handle-christmas-paper-bag',
        'keyword' => 'custom ivory Christmas paper bag',
        'seo_title' => 'Ivory Holly Rope-Handle Christmas Bag | VPN',
        'seo_description' => 'Custom ivory holly Christmas paper bag with green cotton rope handles. Confirm holes, knots, paper, print, size and load by sample.',
        'structure' => 'Landscape rectangular paper gift bag with generous side gussets, a reinforced folded top rim and a standing glued base.',
        'material' => 'Warm ivory matte paper exterior with solid dark-green side gussets and a plain ivory interior; final paper grade is project-specific.',
        'handle' => 'Exactly two dark-green cotton-rope arch handles, each passing through two round unmetalized holes and anchored with simple internal knots.',
        'artwork' => 'Sparse deep-green three-leaf holly sprigs with three red berries repeat on the front and rear; no stitching, eyelets, foil or text is shown.',
        'applications' => 'Premium retail gifts, beauty and skincare sets, candles, accessories, event kits and curated Christmas collections.',
        'tags' => array('ivory Christmas bag', 'cotton rope handles', 'holly berry packaging', 'premium paper bag', 'holiday retail bag'),
    ),
    array(
        'slug' => 'custom-navy-ornament-rope-handle-christmas-paper-bag',
        'title' => 'Custom Navy Ornament Rope-Handle Christmas Paper Bag',
        'prefix' => 'navy-ornament-rope-handle-christmas-paper-bag',
        'keyword' => 'custom navy Christmas paper bag',
        'seo_title' => 'Navy Ornament Christmas Paper Bag | VPN',
        'seo_description' => 'Custom navy ornament Christmas paper bag with cotton rope handles. Confirm matte ink, holes, knots, paper grade, size and load by sample.',
        'structure' => 'Tall slim rectangular paper gift bag with modest side gussets, a folded top rim and a glued rectangular standing base.',
        'material' => 'Matte midnight-navy printed paper exterior with a plain ivory interior; paper grade and caliper must be selected for the packed product.',
        'handle' => 'Exactly two navy cotton-rope arch handles with four round holes total and small internal rope knots; no metal eyelets are shown.',
        'artwork' => 'Three hanging ornaments use mustard-yellow and ivory ordinary ink with small ivory stars; the mustard is not metallic and no foil is shown.',
        'applications' => 'Premium corporate gifts, wine or accessory sets, beauty kits, candles, PR mailers and seasonal retail programs.',
        'tags' => array('navy Christmas bag', 'ornament gift bag', 'cotton rope paper bag', 'corporate holiday packaging', 'seasonal shopping bag'),
    ),
    array(
        'slug' => 'custom-gingerbread-flat-handle-christmas-paper-bag',
        'title' => 'Custom Gingerbread Flat-Handle Christmas Paper Bag',
        'prefix' => 'gingerbread-flat-handle-christmas-paper-bag',
        'keyword' => 'custom gingerbread Christmas paper bag',
        'seo_title' => 'Gingerbread Christmas Paper Bag | VPN Packaging',
        'seo_description' => 'Custom gingerbread Christmas paper bag with red flat handles. Confirm artwork, paper, reinforcement, size and carrying performance by sample.',
        'structure' => 'Small portrait-front paper gift bag with deep rectangular side gussets, a folded top rim and a standing glued base.',
        'material' => 'Warm cream matte paper exterior with muted-red side gussets and a plain cream interior; final paper grade and caliper require approval.',
        'handle' => 'Exactly two muted-red flat-paper handles with square folded bends and bonded ends beneath internal rectangular reinforcement patches.',
        'artwork' => 'A sparse repeat of brown gingerbread figures, white icing strokes, red buttons and red-and-white candy canes appears on front and rear; no brand or text is shown.',
        'applications' => 'Bakery and confectionery presentation with a separate food-contact layer, party favors, small gifts, event handouts and seasonal promotions.',
        'tags' => array('gingerbread paper bag', 'flat paper handles', 'bakery gift bag', 'Christmas retail packaging', 'cream gift bag'),
    ),
);

function vpn_xmas_bag_attachment(string $filename, int $parent_id, string $alt, string $title, string $caption, string $path): int {
    global $wpdb;
    $existing = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_vpn_xmas_paper_bag_collection_file' AND meta_value = %s ORDER BY post_id DESC LIMIT 1",
        $filename
    ));
    if ($existing && 'attachment' === get_post_type($existing)) {
        wp_update_post(array('ID' => $existing, 'post_parent' => $parent_id, 'post_title' => $title, 'post_excerpt' => $caption));
        update_post_meta($existing, '_wp_attachment_image_alt', $alt);
        return $existing;
    }
    if (!file_exists($path)) {
        return 0;
    }
    $type = wp_check_filetype($filename, null);
    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $type['type'] ?: 'image/jpeg',
        'post_title' => $title,
        'post_excerpt' => $caption,
        'post_status' => 'inherit',
        'post_parent' => $parent_id,
    ), $path, $parent_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_vpn_xmas_paper_bag_collection_file', $filename);
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $alt);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    return (int) $attachment_id;
}

function vpn_xmas_bag_image_files(string $prefix, string $asset_dir): array {
    $files = glob($asset_dir . '*-' . $prefix . '*.jpg');
    $files = array_values(array_filter($files, static function ($file) use ($prefix) {
        $name = wp_basename($file);
        return (bool) preg_match('/^\d{2}-' . preg_quote($prefix, '/') . '-.+\.jpe?g$/i', $name)
            && !preg_match('/-\d+x\d+\.jpe?g$/i', $name);
    }));
    usort($files, static function ($a, $b) { return strnatcasecmp(wp_basename($a), wp_basename($b)); });
    return $files;
}

function vpn_xmas_bag_content(array $product, array $images): string {
    $image_html = '';
    if (!empty($images[0])) {
        $image_html = '<figure class="product-inline-figure product-inline-figure-small"><img src="' . esc_url(wp_get_attachment_image_url($images[0], 'large')) . '" alt="' . esc_attr('AI design visualization of ' . strtolower($product['title']) . ', closed hero view') . '" loading="lazy" decoding="async"><figcaption>AI-generated design visualization for structure and seasonal artwork discussion; not a photograph of a manufactured sample.</figcaption></figure>';
    }
    return '<h2>Quick answer: what this Christmas paper bag is</h2>'
        . '<p>' . esc_html($product['structure']) . ' ' . esc_html($product['artwork']) . ' This is a proposed seasonal paper-bag direction for buyers who need to compare handle types, artwork coverage and presentation before approving a production brief. The supplied six-view set is an AI design visualization, not a photograph of a manufactured bag or a technical drawing.</p>'
        . $image_html
        . '<h2>Construction, gussets and opening</h2>'
        . '<p>The bag is shown standing on a glued base with a folded upper rim and an open mouth. ' . esc_html($product['structure']) . ' The rear, side-gusset and high-angle views help a buyer discuss the visible silhouette, opening sequence and artwork continuity. They do not expose or verify an underside glue pattern, a flat pattern, a die line or a load test. The final blank must be developed around the real packed product.</p>'
        . '<h2>Paper, print and Christmas artwork</h2>'
        . '<p>' . esc_html($product['material']) . ' ' . esc_html($product['artwork']) . ' The collection shows ordinary offset-style flat ink with no foil, embossing, metallic ink or licensed character. Final paper color, grain direction, coating, ink coverage, bleed, safe area and color references should be approved on the selected paper rather than inferred from a screen image. Artwork files should be versioned by SKU, size, language, quantity and approval date.</p>'
        . '<h2>Handle attachment and carrying considerations</h2>'
        . '<p>' . esc_html($product['handle']) . ' The visible handle design is a concept reference, not evidence of carrying performance. Handle material, reinforcement size, hole position, adhesive bond, knot, paper tear resistance and packed weight must be tested with a filled sample. A rope handle is not interchangeable with a paper handle, and a bag intended for a heavier gift set may need a different board, rim reinforcement or construction.</p>'
        . '<h2>Best-fit applications</h2>'
        . '<p>' . esc_html($product['applications']) . ' Select the format from the complete packed contents, not from the visual alone. Confirm the product dimensions, weight, sharp corners, surface protection, presentation order, card or leaflet size and packing method. If food touches the package, define the primary food-contact layer separately and request the relevant material declarations and destination-market review.</p>'
        . '<h2>Sampling, quality control and production hand-off</h2>'
        . '<p>Before production, VPN can convert the chosen direction into a dieline and artwork checklist for approval. A weight-accurate filled sample should be used to check handle pull, rim deformation, gusset fold, base stability, product removal, opening comfort and movement during packing. Inspect print registration, color consistency, edge scuffing, crease position, glue cleanliness, handle alignment and the relationship between front, rear and gusset artwork.</p>'
        . '<p>Christmas programs have a fixed selling window. Work backward from the date finished goods must arrive, allowing time for measurement, structural sampling, artwork rounds, paper and handle procurement, production, inspection, export packing and transport. MOQ, lead time, dimensions, paper weight, load capacity, food-contact suitability, recyclability and certification are project-specific and must be confirmed in a written quotation.</p>'
        . '<h2>Responsible image and specification disclosure</h2>'
        . '<p>The 30 supplied images are AI-generated packaging concept mockups. They show proposed structure, handle placement and seasonal artwork, but they cannot prove strength, food safety, recyclability, stock availability, manufacturing capability, exact dimensions, paper weight, certification or production tolerance. Minor motif-position differences may occur between generated angles. VPN will confirm the final specification from an approved artwork file, production dieline and physical sample. Request a <a href="' . esc_url(home_url('/contact/#quote')) . '">Christmas paper bag quotation</a> with the packed product data and required arrival date.</p>'
        . '<h2>Related Christmas packaging references</h2>'
        . '<p>Compare these bags with the <a href="' . esc_url(home_url('/products/christmas-packaging/')) . '">Christmas paper bags and gift boxes collection</a> and the <a href="' . esc_url(home_url('/products/paper-bags-with-logo/')) . '">paper bags with logo category</a>. A coordinated box-and-bag system can share artwork rules while keeping each component’s dieline, handle, closure and performance approval separate.</p>';
}

$category_ids = array();
foreach ($category_slugs as $category_slug) {
    $category = get_term_by('slug', $category_slug, 'product_cat');
    if ($category && !is_wp_error($category)) {
        $category_ids[$category_slug] = (int) $category->term_id;
    }
}
if (empty($category_ids['christmas-packaging'])) {
    fwrite(STDERR, "Christmas packaging category is missing.\n");
    exit(1);
}

$imported = array();
$failures = array();
foreach ($products as $product) {
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    $post_data = array(
        'post_title' => $product['title'],
        'post_name' => $product['slug'],
        'post_type' => 'product',
        'post_status' => 'publish',
        'post_excerpt' => 'A custom Christmas paper bag concept with ' . strtolower($product['handle']) . ' Seasonal artwork, paper direction and final carrying performance require an approved brief and physical sample.',
    );
    if ($existing) {
        $post_data['ID'] = (int) $existing->ID;
        wp_untrash_post((int) $existing->ID);
        $result = wp_update_post($post_data, true);
        $product_id = (int) $existing->ID;
    } else {
        $result = wp_insert_post($post_data, true);
        $product_id = is_wp_error($result) ? 0 : (int) $result;
    }
    if (is_wp_error($result) || !$product_id) {
        $failures[] = $product['slug'] . ': product write';
        continue;
    }

    $files = vpn_xmas_bag_image_files($product['prefix'], $asset_dir);
    if (count($files) !== 6) {
        $failures[] = $product['slug'] . ': expected 6 JPG files, found ' . count($files);
        continue;
    }
    $attachment_ids = array();
    foreach ($files as $file) {
        $name = wp_basename($file);
        $role = (int) substr($name, 0, 2);
        $role_name = array(1 => 'closed hero', 2 => 'open interior', 3 => 'handle and print detail', 4 => 'rear and side', 5 => 'high-angle opening', 6 => 'feature callout');
        $alt = 'AI design visualization of ' . strtolower($product['title']) . ', ' . ($role_name[$role] ?? 'product view');
        $attachment_ids[] = vpn_xmas_bag_attachment($name, $product_id, $alt, $product['title'] . ' — ' . ($role_name[$role] ?? 'product view'), 'AI-generated design visualization; not a photograph of a manufactured sample.', $file);
    }
    if (in_array(0, $attachment_ids, true)) {
        $failures[] = $product['slug'] . ': attachment write';
        continue;
    }

    $term_ids = array($category_ids['christmas-packaging']);
    foreach ($category_slugs as $category_slug) {
        if (!empty($category_ids[$category_slug])) {
            $term_ids[] = $category_ids[$category_slug];
        }
    }
    wp_set_object_terms($product_id, array_values(array_unique($term_ids)), 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type');
    wp_set_object_terms($product_id, $product['tags'], 'product_tag', false);
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, '_vpn_visual_status', 'AI-generated packaging concept mockup; not a manufactured sample');
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', array(
        array('label' => 'Structure', 'value' => $product['structure']),
        array('label' => 'Paper direction', 'value' => $product['material']),
        array('label' => 'Handle and attachment', 'value' => $product['handle']),
        array('label' => 'Artwork and printing shown', 'value' => $product['artwork']),
        array('label' => 'Gusset and base', 'value' => 'Visible side-gusset and standing glued-base concept; underside glue pattern is not presented as verified engineering documentation.'),
        array('label' => 'Customization', 'value' => 'Size, paper, handle, reinforcement, artwork, coating, insert and packing subject to approved brief and physical sample.'),
        array('label' => 'Dimensions / paper weight / MOQ', 'value' => 'Not specified in the supplied visual collection; confirm in a written quotation.'),
        array('label' => 'Visual status', 'value' => 'AI-generated packaging concept mockup, not a manufactured sample, test report or certification.'),
        array('label' => 'Manufacturing route', 'value' => 'VPN Vietnam development and production subject to approved specifications and physical sample.'),
    ));
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['seo_title']);
    update_post_meta($product_id, 'rank_math_description', $product['seo_description']);
    update_post_meta($product_id, 'rank_math_canonical_url', get_permalink($product_id));
    update_post_meta($product_id, 'rank_math_robots', array('index', 'follow'));
    update_post_meta($product_id, 'rank_math_primary_product_cat', $category_ids['christmas-packaging']);
    set_post_thumbnail($product_id, $attachment_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($attachment_ids, 1)));
    wp_update_post(array('ID' => $product_id, 'post_content' => vpn_xmas_bag_content($product, $attachment_ids)));
    $imported[] = array('id' => $product_id, 'slug' => $product['slug'], 'images' => count($attachment_ids));
}

if (function_exists('custom_box_sync_christmas_packaging_category')) {
    custom_box_sync_christmas_packaging_category();
}

echo wp_json_encode(array('marker' => $marker, 'imported' => $imported, 'failures' => $failures), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures) {
    exit(1);
}
