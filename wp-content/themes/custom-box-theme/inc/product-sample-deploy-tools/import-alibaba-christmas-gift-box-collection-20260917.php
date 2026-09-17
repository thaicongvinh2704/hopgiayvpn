<?php
/**
 * Import the five supplied Christmas gift-box visualizations as WooCommerce products.
 *
 * The supplied ZIP identifies these as AI-generated design visualizations. This
 * importer deliberately labels them that way and does not invent dimensions,
 * board weight, MOQ, certification, load capacity or production photographs.
 *
 * Run locally after the 30 JPG files have been copied to wp-content/uploads/2026/09:
 *   php tools/import-alibaba-christmas-gift-box-collection-20260917.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

defined('ABSPATH') || exit;

$marker = 'alibaba-christmas-gift-box-collection-20260917';
$upload_dir = wp_upload_dir();
$asset_dir = trailingslashit($upload_dir['basedir']) . '2026/09/';
$bundle_asset_dir = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/09/';
$category_slugs = array('christmas-packaging', 'gift-paper-boxes');

// Pull-deploy support: restore the committed originals into the normal uploads
// directory before creating Media Library attachments. This keeps the admin
// Product Sample Deploy tool self-contained after a hosting git pull.
if (!is_dir($asset_dir)) {
    wp_mkdir_p($asset_dir);
}
foreach (glob($bundle_asset_dir . '*christmas-gift-box*.jpg') ?: array() as $bundled_file) {
    $target_file = $asset_dir . wp_basename($bundled_file);
    if (!file_exists($target_file)) {
        copy($bundled_file, $target_file);
    }
}

$products = array(
    array(
        'slug' => 'custom-red-snowflake-lid-and-base-christmas-gift-box',
        'title' => 'Custom Red Snowflake Lid-and-Base Christmas Gift Box',
        'prefix' => 'red-snowflake-lid-and-base-christmas-gift-box',
        'keyword' => 'custom red Christmas gift box',
        'seo_title' => 'Red Snowflake Christmas Gift Box | VPN Packaging',
        'seo_description' => 'Custom red snowflake lid-and-base Christmas gift box. Discuss rigid structure, printed wrap and fit with VPN Packaging; final specs follow sampling.',
        'structure' => 'Two-piece rigid lid-and-base gift box with a separate lift-off lid.',
        'material' => 'Red wrapped paper exterior with an ivory paper-lined interior; board weight and caliper are confirmed from the brief and approved sample.',
        'closure' => 'Fitted telescoping lid; no hinge, ribbon or handle is shown.',
        'short' => 'A custom red two-piece rigid Christmas gift box with an ivory snowflake print, fitted lift-off lid and plain ivory interior. The supplied six-view set is an AI design visualization for discussing seasonal artwork and structure, not a photograph of a manufactured sample. VPN can develop the final size, board specification, printed wrap, lining and protective packing around the packed product.',
        'description' => 'A shallow rectangular two-piece rigid gift box with a separate full telescoping lid over a matching base. The red exterior carries regular small ivory six-arm snowflakes and dots across the lid and sidewalls. The visual reference shows a plain ivory-lined interior with no insert, ribbon or handle. This format gives a premium gift set a clean reveal and a broad seasonal print area while keeping the opening mechanism simple for assembly and fulfillment.',
        'applications' => 'Corporate holiday gifts, confectionery assortments, candles, beauty sets, accessories and curated Christmas hampers.',
        'categories' => array('rigid-boxes', 'lid-and-base-boxes', 'corporate-gift-packaging'),
        'tags' => array('Christmas gift box', 'red gift box', 'snowflake packaging', 'rigid lid and base box', 'seasonal packaging'),
    ),
    array(
        'slug' => 'custom-kraft-evergreen-tuck-top-christmas-gift-box',
        'title' => 'Custom Kraft Evergreen Tuck-Top Christmas Gift Box',
        'prefix' => 'kraft-evergreen-tuck-top-christmas-gift-box',
        'keyword' => 'custom kraft Christmas gift box',
        'seo_title' => 'Kraft Evergreen Tuck-Top Gift Box | VPN',
        'seo_description' => 'Custom kraft evergreen tuck-top Christmas gift box for lightweight gifts. Confirm paperboard, print, closure fit and final sample with VPN Packaging.',
        'structure' => 'Folding paperboard carton with a rear-attached straight-tuck top, side dust flaps and a front tuck tongue.',
        'material' => 'Natural uncoated brown kraft paperboard with a plain kraft interior; caliper and grade are confirmed from the product brief and approved sample.',
        'closure' => 'Straight-tuck top closure with glued side seam; no ribbon, window or handle is shown.',
        'short' => 'A kraft folding Christmas gift box with a straight-tuck top, evergreen tree print and small red stars. The supplied six-view set is an AI design visualization, not a manufactured-sample photograph or engineering drawing. VPN can adapt the carton around your product dimensions, artwork, paperboard grade, closure tolerance and packing route after sampling.',
        'description' => 'A near-cube kraft folding carton with a standard rear-attached straight-tuck top, two small side dust flaps and one front tuck tongue. The natural brown kraft surface carries dark forest-green tree silhouettes and sparse red stars, while the top panel uses one centered green tree. Its efficient folding construction suits lightweight gifts, seasonal retail packs and small promotional assortments where flat delivery and simple assembly are useful.',
        'applications' => 'Small corporate gifts, candles, soap, accessories, confectionery with a separate primary food-contact layer, retail promotions and event handouts.',
        'categories' => array('folding-carton-boxes', 'corporate-gift-packaging'),
        'tags' => array('kraft Christmas box', 'tuck top box', 'folding carton', 'evergreen packaging', 'seasonal gift packaging'),
    ),
    array(
        'slug' => 'custom-candy-cane-pillow-christmas-gift-box',
        'title' => 'Custom Candy-Cane Pillow Christmas Gift Box',
        'prefix' => 'candy-cane-pillow-christmas-gift-box',
        'keyword' => 'custom candy cane pillow box',
        'seo_title' => 'Candy-Cane Pillow Gift Box | VPN Packaging',
        'seo_description' => 'Custom candy-cane pillow Christmas gift box with curved tuck flaps and seasonal print. Final size, paperboard and fit are confirmed by sampling.',
        'structure' => 'Horizontal pillow box with two gently bulged faces and curved tucked crescent end flaps.',
        'material' => 'Smooth white folding paperboard with a plain white interior; board grade and thickness are confirmed from the packed-product brief and sample.',
        'closure' => 'Curved tucked end flaps; no ribbon, string, handle or foil is shown.',
        'short' => 'A custom white pillow-shaped Christmas gift box with diagonal red-and-white candy-cane stripes and a small holly motif. The supplied six-view set is an AI design visualization, not a photo of a produced sample. VPN can confirm the final size, paperboard, crease geometry, print coverage and end-flap fit around the intended contents.',
        'description' => 'A small horizontal pillow box with a gently curved silhouette, two bulged faces and curved tucked crescent end flaps. Broad red-and-white diagonal stripes run across the main faces and ends, with a small dark-green holly and red-berry motif centered on the front. Unlike a rigid rectangular box, the pillow form creates a lighter gift presentation and can be efficient for small seasonal items when the curved closure is tested with the actual product.',
        'applications' => 'Small confectionery with an appropriate inner wrap, jewelry accessories, soaps, favors, sample kits and lightweight retail gifts.',
        'categories' => array('folding-carton-boxes', 'bakery-packaging-boxes'),
        'tags' => array('pillow box', 'candy cane packaging', 'Christmas favor box', 'folding paper box', 'holiday gift packaging'),
    ),
    array(
        'slug' => 'custom-holly-berry-gable-christmas-gift-box',
        'title' => 'Custom Holly-Berry Gable Christmas Gift Box',
        'prefix' => 'holly-berry-gable-christmas-gift-box',
        'keyword' => 'custom Christmas gable gift box',
        'seo_title' => 'Holly-Berry Gable Gift Box | VPN Packaging',
        'seo_description' => 'Custom holly-berry gable Christmas gift box with integral carry handle. Confirm die-cut, paperboard, load and closure performance by sample.',
        'structure' => 'Gable-top paperboard gift box with an integral double-layer carry handle and matching oblong slots.',
        'material' => 'Warm ivory paperboard body with a solid red roof and handle panels; paperboard grade and load performance require an approved sample.',
        'closure' => 'Interlocking integral roof-and-handle panels with short tabs; no cord, ribbon or window is shown.',
        'short' => 'A custom holly-and-berry gable Christmas gift box with a red integral carry handle, ivory printed body and pitched roof. The supplied six-view set is an AI design visualization and does not establish load capacity or production readiness. VPN can develop the final die-cut, paperboard, handle reinforcement, print and pack-out around the real contents.',
        'description' => 'A conventional gable-top paperboard gift box with a rectangular body, pitched folded roof and two overlapping die-cut integral carry-handle panels. The ivory body carries restrained dark-green holly sprigs with red berries, while the roof and handles are Christmas red. The format combines a gift carton and carry feature in one die-cut structure, making it useful for event distribution and lightweight retail gifts when handle pull and closure engagement are tested.',
        'applications' => 'Bakery and confectionery gift packs, party favors, employee gifts, event handouts and small seasonal retail sets.',
        'categories' => array('folding-carton-boxes', 'bakery-packaging-boxes', 'corporate-gift-packaging'),
        'tags' => array('gable gift box', 'holly berry packaging', 'Christmas carry box', 'die cut handle box', 'seasonal carton'),
    ),
    array(
        'slug' => 'custom-green-tree-magnetic-christmas-gift-box',
        'title' => 'Custom Green Tree Magnetic Christmas Gift Box',
        'prefix' => 'green-tree-magnetic-christmas-gift-box',
        'keyword' => 'custom magnetic Christmas gift box',
        'seo_title' => 'Green Tree Magnetic Gift Box | VPN Packaging',
        'seo_description' => 'Custom green tree magnetic Christmas gift box with book-style opening. Confirm board, concealed closure, tray fit and transit packing by sample.',
        'structure' => 'Square-footprint shallow book-style rigid box with a single rear-hinged cover and front closing flap.',
        'material' => 'Forest-green wrapped paper exterior with an ivory paper-lined tray and inner lid; board caliper and magnetic specification require an approved sample.',
        'closure' => 'Concealed magnetic closure beneath the paper wrap; the visual shows no exposed hardware or ribbon.',
        'short' => 'A forest-green book-style magnetic Christmas gift box with an ivory-lined tray and minimalist tree artwork. The supplied six-view set is an AI design visualization, not a manufactured-sample photograph or certification. VPN can confirm board construction, concealed magnets, tray clearance, print, lining and transit packing around your product set.',
        'description' => 'A shallow square rigid presentation box with a single paper-wrapped hinged cover attached along a rear spine and a short front closing flap. The forest-green exterior carries a centered white three-tier tree, mustard-yellow star and inset white border. The ivory-lined tray is shown empty and without an insert. This book-style opening creates a deliberate reveal for premium gift sets, provided the hinge, closure resistance and product retention are approved with a filled sample.',
        'applications' => 'Premium corporate gifts, beauty and skincare sets, candles, accessories, PR kits and curated Christmas collections.',
        'categories' => array('rigid-boxes', 'magnetic-closure-boxes', 'corporate-gift-packaging'),
        'tags' => array('magnetic gift box', 'green Christmas box', 'book style rigid box', 'premium holiday packaging', 'tree gift box'),
    ),
);

function vpn_xmas_collection_attachment(string $filename, int $parent_id, string $alt, string $title, string $caption, string $path): int {
    global $wpdb;
    $existing = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_vpn_xmas_collection_file' AND meta_value = %s ORDER BY post_id DESC LIMIT 1",
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
    update_post_meta((int) $attachment_id, '_vpn_xmas_collection_file', $filename);
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $alt);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $path);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    return (int) $attachment_id;
}

function vpn_xmas_collection_image_files(string $prefix, string $asset_dir): array {
    $files = glob($asset_dir . '*-' . $prefix . '*.jpg');
    $files = array_values(array_filter($files, static function ($file) use ($prefix) {
        $name = wp_basename($file);
        return (bool) preg_match('/^\d{2}-' . preg_quote($prefix, '/') . '-.+\.jpe?g$/i', $name)
            && !preg_match('/-\d+x\d+\.jpe?g$/i', $name);
    }));
    if (!$files) {
        return array();
    }
    usort($files, static function ($a, $b) { return strnatcasecmp(wp_basename($a), wp_basename($b)); });
    return $files;
}

function vpn_xmas_collection_content(array $product, array $images): string {
    $image_html = '';
    if (!empty($images[0])) {
        $image_html = '<figure class="product-inline-figure product-inline-figure-small"><img src="' . esc_url(wp_get_attachment_image_url($images[0], 'large')) . '" alt="' . esc_attr('AI design visualization of ' . strtolower($product['title']) . ', closed hero view') . '" loading="lazy" decoding="async"><figcaption>AI-generated design visualization for structure and seasonal artwork discussion; not a photograph of a manufactured sample.</figcaption></figure>';
    }
    return '<h2>Quick answer: what this Christmas gift box is</h2>'
        . '<p>' . esc_html($product['description']) . '</p>'
        . $image_html
        . '<h2>Construction and opening mechanism</h2>'
        . '<p>' . esc_html($product['structure']) . ' ' . esc_html($product['closure']) . ' The open and detail views in the supplied collection are intended to make the opening sequence, print field and component relationship easier to discuss before a dieline is released.</p>'
        . '<h2>Materials, printing and seasonal artwork</h2>'
        . '<p>' . esc_html($product['material']) . ' The supplied concept uses ordinary matte flat printing with no foil or embossing shown. Final ink, coating, color references, grain direction and finishing masks should be approved on the selected paperboard rather than inferred from a screen image.</p>'
        . '<p>Christmas artwork should preserve the brand hierarchy while adapting to the available panels, folds, handle cuts or lid seams. Confirm the safe area, bleed, panel reading order and language versions on the actual dieline. If the product is part of a wider campaign, use a controlled version matrix linking SKU, artwork filename, color reference, quantity and carton label.</p>'
        . '<h2>Applications and fit planning</h2>'
        . '<p>' . esc_html($product['applications']) . ' Choose the structure from the packed product, not from the visual alone. Send the complete item list, dimensions, weight, fragile surfaces, desired presentation order, card size and packing route. A filled prototype or weight-accurate dummy should be used to check clearance, product removal, closure resistance and movement.</p>'
        . '<h2>Sampling, quality control and delivery</h2>'
        . '<p>Before bulk production, approve the construction and fit, then the artwork, color, surface and any insert or closure. For the final sample, check cut and crease position, corner or seam finish, print registration, color consistency, scuffing, opening sequence and packed appearance. If this pack will travel through parcel networks, test it inside a protective shipper; a presentation box is not automatically a courier carton.</p>'
        . '<p>Christmas programs have a fixed selling window. Work backward from the date the finished gifts must arrive, allowing time for product measurement, structural sampling, artwork rounds, material procurement, production, inspection, export packing and transport. MOQ, lead time, dimensions, board caliper, certification and load capacity are project-specific and must be confirmed in a written quotation.</p>'
        . '<h2>Important image and specification disclosure</h2>'
        . '<p>The 30 supplied images are AI-generated design visualizations. They show the intended visual direction and structure, but they do not prove a manufactured sample, exact dimensions, board weight, food-contact suitability, certification, MOQ, load capacity or production tolerance. VPN can develop the final product from an approved brief, dieline and physical sample. Request a <a href="' . esc_url(home_url('/contact/#quote')) . '">Christmas packaging quotation</a> with your packed product data and required arrival date.</p>';
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
        'post_excerpt' => $product['short'],
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

    $files = vpn_xmas_collection_image_files($product['prefix'], $asset_dir);
    if (count($files) !== 6) {
        $failures[] = $product['slug'] . ': expected 6 JPG files, found ' . count($files);
        continue;
    }
    $attachment_ids = array();
    foreach ($files as $file) {
        $name = wp_basename($file);
        $role = (int) substr($name, 0, 2);
        $role_name = array(1 => 'closed hero', 2 => 'open interior', 3 => 'print and closure detail', 4 => 'rear and side', 5 => 'top view', 6 => 'feature callout');
        $alt = 'AI design visualization of ' . strtolower($product['title']) . ', ' . ($role_name[$role] ?? 'product view');
        $attachment_ids[] = vpn_xmas_collection_attachment($name, $product_id, $alt, $product['title'] . ' — ' . ($role_name[$role] ?? 'product view'), 'AI-generated design visualization; not a photograph of a manufactured sample.', $file);
    }
    if (in_array(0, $attachment_ids, true)) {
        $failures[] = $product['slug'] . ': attachment write';
        continue;
    }
    $term_ids = array($category_ids['christmas-packaging']);
    foreach ($product['categories'] as $slug) {
        $term = get_term_by('slug', $slug, 'product_cat');
        if ($term && !is_wp_error($term)) {
            $term_ids[] = (int) $term->term_id;
        }
    }
    wp_set_object_terms($product_id, array_values(array_unique($term_ids)), 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type');
    wp_set_object_terms($product_id, $product['tags'], 'product_tag', false);
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    update_post_meta($product_id, '_vpn_visual_status', 'AI-generated design visualization; not a manufactured sample');
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', array(
        array('label' => 'Structure', 'value' => $product['structure']),
        array('label' => 'Material direction', 'value' => $product['material']),
        array('label' => 'Closure', 'value' => $product['closure']),
        array('label' => 'Printing shown', 'value' => 'Ordinary matte flat ink; no foil or embossing shown in the supplied visualization.'),
        array('label' => 'Customization', 'value' => 'Size, paperboard, artwork, color, lining, insert, closure and packing subject to approved brief and sample.'),
        array('label' => 'Dimensions / board weight / MOQ', 'value' => 'Not specified in the supplied visual collection; confirm in a written quotation.'),
        array('label' => 'Visual status', 'value' => 'AI-generated design visualization, not a manufactured sample or certification.'),
        array('label' => 'Manufacturing route', 'value' => 'VPN Vietnam development and production subject to approved specifications and physical sample.'),
    ));
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['seo_title']);
    update_post_meta($product_id, 'rank_math_description', $product['seo_description']);
    update_post_meta($product_id, 'rank_math_primary_product_cat', $category_ids['christmas-packaging']);
    set_post_thumbnail($product_id, $attachment_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($attachment_ids, 1)));
    wp_update_post(array('ID' => $product_id, 'post_content' => vpn_xmas_collection_content($product, $attachment_ids)));
    $imported[] = array('id' => $product_id, 'slug' => $product['slug'], 'images' => count($attachment_ids));
}

if (function_exists('custom_box_sync_christmas_packaging_category')) {
    custom_box_sync_christmas_packaging_category();
}

echo wp_json_encode(array('marker' => $marker, 'imported' => $imported, 'failures' => $failures), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures) {
    exit(1);
}
