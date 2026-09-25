<?php
/**
 * Import five AI-concept Christmas box designs as locally reviewable WooCommerce products.
 *
 * The source ZIP contains five products with five clean product views plus one
 * feature infographic each. The infographics contain garbled text and are
 * intentionally excluded from the Media Library and product galleries.
 * Product copy identifies every image as a design visualization and leaves
 * unknown dimensions, grades, MOQ, price, certificates, and performance open.
 *
 * Run locally with: php tools/import-alibaba-christmas-boxes-20260925.php
 */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

defined('ABSPATH') || exit;

$marker = 'alibaba-christmas-boxes-20260925';
$upload_dir = wp_upload_dir();
$asset_dir = trailingslashit($upload_dir['basedir']) . '2026/09/';
$bundle_asset_dir = get_template_directory() . '/inc/product-sample-deploy-assets/uploads/2026/09/';
$content_dir = get_template_directory() . '/inc/product-content/christmas-boxes-alibaba-20260925/';

$products = array(
    array(
        'slug' => 'custom-santa-portrait-rigid-christmas-gift-box',
        'title' => 'Custom Santa Portrait Rigid Christmas Gift Box',
        'prefix' => 'santa-portrait-rigid-box',
        'content_file' => 'santa-portrait-rigid-box.html',
        'keyword' => 'santa portrait rigid gift box',
        'seo_title' => 'Santa Portrait Rigid Gift Box | VPN Packaging',
        'seo_description' => 'Custom Santa portrait rigid gift box concept with a cream lift-off lid. Confirm size, board, insert, print and finish by approved sample.',
        'short' => 'A custom Santa portrait rigid Christmas gift box concept with a cream lift-off lid, evergreen base, and small holly accents. The supplied views show a simple empty tray without a divider or ribbon, making this a useful starting point for discussing a clear seasonal reveal. It can be developed around a gift set, ornament, candle, accessory, or other measured product. These are AI-generated design visualizations, not photographs of a manufactured sample. VPN can review the packed item, lid fit, board direction, wrap, print, insert, and export packing from a written brief. Dimensions, material grade, MOQ, price, and performance are confirmed project by project. Buyers should provide dimensions, gift count, weight, and required arrival date so structure and sampling can be assessed before a seasonal order is scheduled.',
        'categories' => array('christmas-packaging', 'rigid-boxes', 'gift-paper-boxes', 'corporate-gift-packaging'),
        'tags' => array('Christmas rigid gift box', 'Santa portrait packaging', 'holiday presentation box', 'custom gift box', 'seasonal paper packaging'),
        'related_one' => 'custom-green-tree-magnetic-christmas-gift-box',
        'related_one_text' => 'Christmas magnetic gift box reference',
        'related_two' => 'custom-christmas-tree-drawer-gift-box',
        'related_two_text' => 'Christmas tree drawer gift box',
        'feature' => 'Custom Santa portrait rigid Christmas gift-box concept with lift-off lid and empty tray.',
        'industrial_use' => 'Seasonal retail, corporate gifting, and gift-set presentation; contents must be specified.',
        'paper_type' => 'Final wrapped board and lining selected from the packed-product brief and approved sample.',
        'box_type' => 'Two-piece rigid-style lift-off lid and base; construction to be confirmed from the brief.',
        'shape' => 'Square-footprint rectangular presentation box with a separate removable lid.',
        'liner_type' => 'Plain-looking empty interior in the visualization; final wrap, liner, and insert are not specified.',
        'color' => 'AI concept palette: cream, evergreen, red, and small holly accents.',
        'visual_reference' => 'Santa portrait rigid gift-box concept; not a production model number.',
    ),
    array(
        'slug' => 'custom-santa-sleigh-christmas-mailer-box',
        'title' => 'Custom Santa Sleigh Christmas Mailer Box',
        'prefix' => 'santa-sleigh-mailer-box',
        'content_file' => 'santa-sleigh-mailer-box.html',
        'keyword' => 'santa sleigh mailer box',
        'seo_title' => 'Santa Sleigh Mailer Box | VPN Packaging',
        'seo_description' => 'Custom Santa sleigh Christmas mailer-box concept. Confirm board, closure, fitment, label area and parcel tests before production.',
        'short' => 'A custom Christmas mailer-box concept with a navy exterior, kraft-colored interior, folding lid, and a red sleigh pulled by cream reindeer. Evergreen trees and stars complete the winter scene. The open view shows an empty interior without a fitment, cushioning, or product, so the package should be developed from the actual packed contents and delivery route. The six source files include an AI design infographic with corrupted text; that image is excluded, and only five clean product views are used. The remaining images are still AI visualizations, not a tested shipping sample. Board, closure, dimensions, transit performance, MOQ, and cost require written confirmation. Before quoting, define whether it ships alone or in an outer carton, and provide quantity, label requirements, destination, and delivery deadline.',
        'categories' => array('christmas-packaging', 'corrugated-mailer-boxes', 'corporate-gift-packaging'),
        'tags' => array('Christmas mailer box', 'Santa sleigh packaging', 'ecommerce gift box', 'holiday shipping box', 'custom mailer packaging'),
        'related_one' => 'custom-red-snowflake-lid-and-base-christmas-gift-box',
        'related_one_text' => 'Christmas lift-off-lid gift box',
        'related_two' => 'custom-nutcracker-folding-carton-christmas-gift-box',
        'related_two_text' => 'Nutcracker folding carton',
        'feature' => 'Custom Christmas mailer concept with fold-over lid, front tuck tab, and sleigh artwork.',
        'industrial_use' => 'Seasonal ecommerce, retail gifting, and corporate gift delivery; packed contents and route must be reviewed.',
        'paper_type' => 'Mailer board grade, liner, flute, and print surface are unverified and must be selected by sample.',
        'box_type' => 'Mailer-style folding box with a fold-over lid and front tuck tab shown in the concept.',
        'shape' => 'Rectangular horizontal mailer with a broad decorated lid and shallow side walls.',
        'liner_type' => 'Kraft-colored plain-looking interior in the visualization; no insert or cushioning is shown.',
        'color' => 'AI concept palette: dark navy, kraft brown, cream, red, evergreen, and white stars.',
        'visual_reference' => 'Santa sleigh mailer-box concept; not a production model number.',
    ),
    array(
        'slug' => 'custom-merry-reindeer-christmas-paper-tube',
        'title' => 'Custom Merry Reindeer Christmas Paper Tube',
        'prefix' => 'merry-reindeer-paper-tube',
        'content_file' => 'merry-reindeer-paper-tube.html',
        'keyword' => 'merry reindeer paper tube',
        'seo_title' => 'Reindeer Christmas Paper Tube | VPN Packaging',
        'seo_description' => 'Custom reindeer Christmas paper-tube concept with red cap. Confirm inner size, tube wall, closure, insert and material declarations by sample.',
        'short' => 'A custom Christmas paper-tube concept with a tall kraft-colored cylindrical body, an evergreen reindeer wearing a red scarf, light snow marks, and a red cap with a cream star. The open view appears empty and does not show an inner bag, insert, or food-contact liner. The round format can be evaluated for a small seasonal gift or set once the product dimensions and removal method are known. All supplied product views are AI-generated design visualizations, not a manufactured tube or material certificate. Wall build, paper grade, cap fit, interior support, dimensions, price, and MOQ remain subject to a written specification and approved sample. Provide measurements, target orientation, quantity, cap preference, and destination so the tube proportions, fitment, and sampling plan can be reviewed.',
        'categories' => array('christmas-packaging', 'paper-tube-packaging', 'corporate-gift-packaging'),
        'tags' => array('Christmas paper tube', 'reindeer gift packaging', 'round paper container', 'holiday gift tube', 'custom tube packaging'),
        'related_one' => 'custom-santa-sleigh-christmas-mailer-box',
        'related_one_text' => 'Santa sleigh Christmas mailer',
        'related_two' => 'custom-christmas-gift-box-with-ribbon',
        'related_two_text' => 'Christmas gift box with ribbon',
        'feature' => 'Custom cylindrical Christmas paper-tube concept with removable-looking red cap and reindeer artwork.',
        'industrial_use' => 'Seasonal gift, retail, and promotional packaging; exact contents and any contact requirements must be specified.',
        'paper_type' => 'Tube wall, wrap, base, and cap materials are not identified; confirm by written specification.',
        'box_type' => 'Cylindrical paper-tube style container with a separate-looking cap.',
        'shape' => 'Upright round tube; usable internal diameter and height to be set from the packed item.',
        'liner_type' => 'Plain-looking empty interior in the visualization; liner, pouch, and insert are not specified.',
        'color' => 'AI concept palette: kraft brown, evergreen, red, cream, and light snow marks.',
        'visual_reference' => 'Merry Reindeer paper-tube concept; not a production model number.',
    ),
    array(
        'slug' => 'custom-christmas-tree-drawer-gift-box',
        'title' => 'Custom Christmas Tree Drawer Gift Box',
        'prefix' => 'christmas-tree-drawer-box',
        'content_file' => 'christmas-tree-drawer-box.html',
        'keyword' => 'christmas tree drawer box',
        'seo_title' => 'Christmas Tree Drawer Gift Box | VPN',
        'seo_description' => 'Custom Christmas tree drawer-box concept with a red pull-out tray. Confirm sleeve fit, tab, board, insert, dimensions and print by sample.',
        'short' => 'A custom Christmas tree drawer-box concept with a cream printed sleeve, evergreen tree artwork, red sliding tray, and a small fabric-like pull tab. The open views show an empty tray and sleeve without a product insert. The sliding reveal can suit a compact gift set when the tray depth, opening force, and product-removal sequence are designed around real contents. The supplied images are AI-generated design visualizations, not a sample with verified materials or pull-tab performance. Sleeve clearance, tray dimensions, board, artwork, insert, price, MOQ, and delivery timing must be confirmed in a written quotation and approved physical sample. Send the tray layout, item weight, quantity by size, and arrival window before artwork is locked; these inputs affect fit, opening feel, sample rounds, and packing.',
        'categories' => array('christmas-packaging', 'drawer-boxes', 'rigid-boxes', 'gift-paper-boxes', 'corporate-gift-packaging'),
        'tags' => array('Christmas drawer box', 'tree gift box', 'sliding tray packaging', 'holiday presentation box', 'custom drawer gift box'),
        'related_one' => 'custom-green-tree-magnetic-christmas-gift-box',
        'related_one_text' => 'green-tree magnetic gift box',
        'related_two' => 'custom-santa-portrait-rigid-christmas-gift-box',
        'related_two_text' => 'Santa portrait rigid gift box',
        'feature' => 'Custom Christmas drawer-box concept with printed sleeve, sliding tray, and front pull tab.',
        'industrial_use' => 'Seasonal gift-set, retail, and corporate presentation; packed contents must be specified.',
        'paper_type' => 'Sleeve and tray grades, wrap, and lining are not confirmed; select from the packed-product brief.',
        'box_type' => 'Sleeve-and-sliding-tray drawer-style presentation box shown with a pull tab.',
        'shape' => 'Rectangular sleeve with a pull-out rectangular tray.',
        'liner_type' => 'Plain-looking empty sleeve and tray interior; insert and lining are not specified.',
        'color' => 'AI concept palette: cream, evergreen, red, gold-colored print details, and green side stripes.',
        'visual_reference' => 'Christmas tree drawer-box concept; not a production model number.',
    ),
    array(
        'slug' => 'custom-nutcracker-folding-carton-christmas-gift-box',
        'title' => 'Custom Nutcracker Folding Carton Christmas Gift Box',
        'prefix' => 'nutcracker-folding-carton',
        'content_file' => 'nutcracker-folding-carton.html',
        'keyword' => 'nutcracker folding carton box',
        'seo_title' => 'Nutcracker Folding Carton Gift Box | VPN',
        'seo_description' => 'Custom nutcracker Christmas folding-carton concept. Confirm board, closure, product fit, print panels, and any food-contact layer by sample.',
        'short' => 'A custom Nutcracker Christmas folding-carton concept with a tall red front panel, green-and-cream character artwork, and evergreen side motifs. The open view shows top tuck flaps and a plain-looking interior without a tray, hang tab, or product. The upright format can be assessed for lightweight seasonal retail gifts after the packed-product dimensions, bottom closure, and display method are defined. The supplied images are AI-generated design visualizations, not a printed production carton or food-safety declaration. Board, dimensions, artwork rights, print finish, fitment, MOQ, and cost require written confirmation. Buyers should provide product dimensions, weight, retail market, quantity, artwork status, and arrival date so the carton, closure, and required copy can be specified. Preserve panel space for a barcode, legal copy, and retailer identifiers in the final dieline.',
        'categories' => array('christmas-packaging', 'folding-carton-boxes', 'gift-paper-boxes'),
        'tags' => array('nutcracker gift box', 'Christmas folding carton', 'holiday retail carton', 'character gift packaging', 'custom paper carton'),
        'related_one' => 'custom-kraft-evergreen-tuck-top-christmas-gift-box',
        'related_one_text' => 'kraft evergreen tuck-top carton',
        'related_two' => 'custom-candy-cane-pillow-christmas-gift-box',
        'related_two_text' => 'candy-cane pillow gift box',
        'feature' => 'Custom Nutcracker Christmas folding-carton concept with a tall character-led front panel.',
        'industrial_use' => 'Seasonal retail, gift, and promotional packaging; contents and any regulatory copy must be specified.',
        'paper_type' => 'Final folding-carton board and coating are not identified; select after fit and print review.',
        'box_type' => 'Tall folding-carton style box with top tuck flaps shown; bottom closure requires confirmation.',
        'shape' => 'Tall narrow rectangular carton with front, rear, side, and top panels.',
        'liner_type' => 'Plain-looking interior in the visualization; liner, bag, and insert are not specified.',
        'color' => 'AI concept palette: red, evergreen, cream, and gold-colored character details.',
        'visual_reference' => 'Nutcracker folding-carton concept; not a production model number.',
    ),
);

function vpn_xmas_boxes_20260925_image_files(string $prefix, string $directory): array {
    $files = glob(trailingslashit($directory) . $prefix . '-0*.webp') ?: array();
    $files = array_values(array_filter($files, static function ($file) use ($prefix) {
        return (bool) preg_match('/^' . preg_quote($prefix, '/') . '-0[1-5]-(?:hero|open-interior|paper-detail|rear-side-view|top-artwork-view)\.webp$/i', wp_basename($file));
    }));
    usort($files, static function ($a, $b) { return strnatcasecmp(wp_basename($a), wp_basename($b)); });
    return $files;
}

function vpn_xmas_boxes_20260925_copy_assets(array $products, string $bundle_dir, string $upload_dir): array {
    if (!is_dir($upload_dir) && !wp_mkdir_p($upload_dir)) {
        return array('Upload directory could not be created: ' . $upload_dir);
    }
    $errors = array();
    foreach ($products as $product) {
        foreach (vpn_xmas_boxes_20260925_image_files($product['prefix'], $bundle_dir) as $source) {
            $target = trailingslashit($upload_dir) . wp_basename($source);
            if (file_exists($target)) {
                if (hash_file('sha256', $target) !== hash_file('sha256', $source)) {
                    $errors[] = 'Refusing to overwrite a different upload file: ' . wp_basename($source);
                }
                continue;
            }
            if (!copy($source, $target)) {
                $errors[] = 'Could not copy upload file: ' . wp_basename($source);
            }
        }
    }
    return $errors;
}

function vpn_xmas_boxes_20260925_attachment(string $file, int $parent_id, array $product, int $view): int {
    global $wpdb;
    $filename = wp_basename($file);
    $existing_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_vpn_xmas_boxes_20260925_file' AND meta_value = %s ORDER BY post_id DESC LIMIT 1",
        $filename
    ));
    $views = array(
        1 => array('hero', 'front hero'),
        2 => array('open-interior', 'open interior'),
        3 => array('paper-detail', 'paper and print detail'),
        4 => array('rear-side-view', 'rear and side view'),
        5 => array('top-artwork-view', 'top artwork view'),
    );
    $role = $views[$view][1] ?? 'product view';
    $alt = strtolower($product['title']) . ' for Christmas packaging, ' . $role . ' (AI design visualization)';
    $caption = 'AI-generated design visualization, ' . $role . '; not a photograph of a manufactured sample.';

    if ($existing_id && 'attachment' === get_post_type($existing_id)) {
        wp_update_post(array('ID' => $existing_id, 'post_parent' => $parent_id, 'post_title' => $product['title'] . ' - ' . $role, 'post_excerpt' => $caption));
        update_post_meta($existing_id, '_wp_attachment_image_alt', $alt);
        return $existing_id;
    }
    if (!file_exists($file)) {
        return 0;
    }
    $type = wp_check_filetype($filename, null);
    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => $type['type'] ?: 'image/webp',
        'post_title' => $product['title'] . ' - ' . $role,
        'post_excerpt' => $caption,
        'post_status' => 'inherit',
        'post_parent' => $parent_id,
    ), $file, $parent_id, true);
    if (is_wp_error($attachment_id)) {
        return 0;
    }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    update_post_meta((int) $attachment_id, '_vpn_xmas_boxes_20260925_file', $filename);
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $alt);
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $file);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    return (int) $attachment_id;
}

function vpn_xmas_boxes_20260925_inline_figure(int $attachment_id, string $caption, string $alt): string {
    $image = wp_get_attachment_image($attachment_id, 'large', false, array(
        'alt' => $alt,
        'loading' => 'lazy',
        'decoding' => 'async',
        'sizes' => '(max-width: 767px) calc(100vw - 36px), 560px',
    ));
    return '<figure class="product-inline-figure product-inline-figure-small">' . $image
        . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_xmas_boxes_20260925_link(string $slug, string $anchor, int $category_id): string {
    $post = get_page_by_path($slug, OBJECT, 'product');
    $url = $post ? get_permalink($post) : get_term_link($category_id, 'product_cat');
    if (is_wp_error($url)) {
        $url = home_url('/products/christmas-packaging/');
    }
    return '<a href="' . esc_url($url) . '">' . esc_html($anchor) . '</a>';
}

$category_ids = array();
$failures = array();
foreach ($products as $product) {
    foreach ($product['categories'] as $category_slug) {
        if (isset($category_ids[$category_slug])) {
            continue;
        }
        $term = get_term_by('slug', $category_slug, 'product_cat');
        if (!$term || is_wp_error($term)) {
            $failures[] = 'Missing product category: ' . $category_slug;
        } else {
            $category_ids[$category_slug] = (int) $term->term_id;
        }
    }
    $content_file = $content_dir . $product['content_file'];
    if (!is_readable($content_file)) {
        $failures[] = 'Missing content file: ' . $product['content_file'];
    } else {
        $content = (string) file_get_contents($content_file);
        $word_count = str_word_count(wp_strip_all_tags($content));
        if ($word_count < 1500 || $word_count > 2000) {
            $failures[] = $product['slug'] . ': content word count must be 1500-2000; found ' . $word_count;
        }
        if (preg_match('/<h1\b/i', $content)) {
            $failures[] = $product['slug'] . ': long description must not include an H1';
        }
    }
    $files = vpn_xmas_boxes_20260925_image_files($product['prefix'], $bundle_asset_dir);
    if (count($files) !== 5) {
        $failures[] = $product['slug'] . ': expected five clean WebP views, found ' . count($files);
    }
    foreach ($files as $file) {
        $info = @getimagesize($file);
        if (!$info || 'image/webp' !== $info['mime'] || 1000 !== $info[0] || 1000 !== $info[1]) {
            $failures[] = 'Invalid source image: ' . wp_basename($file);
        }
    }
}

if ($failures) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

$asset_failures = vpn_xmas_boxes_20260925_copy_assets($products, $bundle_asset_dir, $asset_dir);
if ($asset_failures) {
    fwrite(STDERR, implode(PHP_EOL, $asset_failures) . PHP_EOL);
    exit(1);
}

// Create or update all product stubs first, so related product links resolve.
$product_ids = array();
foreach ($products as $product) {
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    if ($existing && $marker !== (string) get_post_meta($existing->ID, '_vpn_sample_import', true)) {
        $failures[] = $product['slug'] . ': slug already belongs to another product; refusing to overwrite';
        continue;
    }
    $post_data = array(
        'post_title' => $product['title'],
        'post_name' => $product['slug'],
        'post_type' => 'product',
        'post_status' => 'publish',
        'post_excerpt' => $product['short'],
        'post_date' => current_time('mysql'),
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
        $failures[] = $product['slug'] . ': product write failed';
        continue;
    }
    update_post_meta($product_id, '_vpn_sample_import', $marker);
    $product_ids[$product['slug']] = $product_id;
}

$imported = array();
foreach ($products as $product) {
    if (empty($product_ids[$product['slug']])) {
        continue;
    }
    $product_id = (int) $product_ids[$product['slug']];
    $files = vpn_xmas_boxes_20260925_image_files($product['prefix'], $asset_dir);
    if (count($files) !== 5) {
        $failures[] = $product['slug'] . ': expected five uploaded images, found ' . count($files);
        continue;
    }
    $attachment_ids = array();
    foreach ($files as $index => $file) {
        $attachment_id = vpn_xmas_boxes_20260925_attachment($file, $product_id, $product, $index + 1);
        if (!$attachment_id) {
            $failures[] = $product['slug'] . ': image attachment failed for ' . wp_basename($file);
        } else {
            $attachment_ids[] = $attachment_id;
        }
    }
    if (count($attachment_ids) !== 5) {
        continue;
    }

    $term_ids = array();
    foreach ($product['categories'] as $category_slug) {
        $term_ids[] = $category_ids[$category_slug];
    }
    wp_set_object_terms($product_id, array_values(array_unique($term_ids)), 'product_cat', false);
    wp_set_object_terms($product_id, 'simple', 'product_type');
    wp_set_object_terms($product_id, $product['tags'], 'product_tag', false);

    update_post_meta($product_id, '_vpn_visual_status', 'AI-generated design visualization; not a manufactured sample');
    update_post_meta($product_id, '_regular_price', '');
    update_post_meta($product_id, '_price', '');
    update_post_meta($product_id, '_stock_status', 'instock');
    update_post_meta($product_id, '_manage_stock', 'no');
    update_post_meta($product_id, '_visibility', 'visible');
    update_post_meta($product_id, '_custom_box_product_specs', array(
        array('label' => 'Feature', 'value' => $product['feature']),
        array('label' => 'Industrial Use', 'value' => $product['industrial_use']),
        array('label' => 'Paper Type', 'value' => $product['paper_type']),
        array('label' => 'Box Type', 'value' => $product['box_type']),
        array('label' => 'Shape', 'value' => $product['shape']),
        array('label' => 'Place of Origin', 'value' => 'Vietnam'),
        array('label' => 'Model Number', 'value' => $product['visual_reference']),
        array('label' => 'Brand Name', 'value' => 'VPN'),
        array('label' => 'Province', 'value' => 'Ho Chi Minh City'),
        array('label' => 'Accessories', 'value' => 'No accessories are depicted; inserts, cards, seals, and other components are quoted separately.'),
        array('label' => 'Custom Order', 'value' => 'Accept; final scope follows approved product dimensions, artwork, and sample.'),
        array('label' => 'Liner Type', 'value' => $product['liner_type']),
        array('label' => 'Logo Printing', 'value' => 'Customer logo can be specified on the approved dieline; the supplied concept is not a confirmed customer-branded sample.'),
        array('label' => 'Printing Handling', 'value' => 'Custom print and finish to be selected from the artwork, material, and approved physical sample.'),
        array('label' => 'Color', 'value' => $product['color']),
        array('label' => 'Size', 'value' => 'Customized to the measured product and approved internal fit.'),
        array('label' => 'Thickness', 'value' => 'Board or wall thickness to be selected and confirmed through specification and sampling.'),
        array('label' => 'Single Piece Price', 'value' => 'Quote after structure, material, quantity, finish, packing, and destination are specified.'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => 'Not stated in the supplied visual package; confirm in the written quotation.'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => 'Customer-specific artwork and structure; supplied images are AI-generated design visualizations, not production samples.'),
    ));
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['seo_title']);
    update_post_meta($product_id, 'rank_math_description', $product['seo_description']);
    update_post_meta($product_id, 'rank_math_primary_product_cat', $category_ids['christmas-packaging']);
    update_post_meta($product_id, 'rank_math_robots', array('index', 'follow'));
    set_post_thumbnail($product_id, $attachment_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($attachment_ids, 1)));

    $content = (string) file_get_contents($content_dir . $product['content_file']);
    $content = str_replace(
        array('{{FIGURE_OPEN}}', '{{FIGURE_DETAIL}}', '{{FIGURE_TOP}}'),
        array(
            vpn_xmas_boxes_20260925_inline_figure($attachment_ids[1], 'Open construction view for the ' . strtolower($product['title']) . '; AI design visualization.', 'Open interior view of ' . strtolower($product['title']) . ', AI design visualization'),
            vpn_xmas_boxes_20260925_inline_figure($attachment_ids[2], 'Close detail view of artwork and apparent material on the ' . strtolower($product['title']) . '; the image does not establish a production finish.', 'Detail view of ' . strtolower($product['title']) . ' artwork and apparent paper surface, AI design visualization'),
            vpn_xmas_boxes_20260925_inline_figure($attachment_ids[4], 'Top artwork view of the ' . strtolower($product['title']) . '; use the production dieline and sample to confirm panel positions.', 'Top artwork view of ' . strtolower($product['title']) . ', AI design visualization'),
        ),
        $content
    );
    $category_id = $category_ids['christmas-packaging'];
    $replacements = array(
        '{{CATEGORY_LINK}}' => '<a href="' . esc_url(home_url('/products/christmas-packaging/')) . '">Christmas paper bags and gift-box category</a>',
        '{{RELATED_ONE_URL}}' => vpn_xmas_boxes_20260925_link($product['related_one'], $product['related_one_text'], $category_id),
        '{{RELATED_TWO_URL}}' => vpn_xmas_boxes_20260925_link($product['related_two'], $product['related_two_text'], $category_id),
        '{{GUIDE_URL}}' => '<a href="' . esc_url(home_url('/christmas-packaging-ideas/')) . '">Christmas packaging approval guide</a>',
        '{{CONTACT_URL}}' => '<a href="' . esc_url(home_url('/contact/#quote')) . '">Christmas packaging quotation form</a>',
    );
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
    if (preg_match('/\{\{[A-Z0-9_]+\}\}/', $content)) {
        $failures[] = $product['slug'] . ': unresolved content placeholder';
    }
    wp_update_post(array('ID' => $product_id, 'post_content' => $content));
    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients($product_id);
    }
    clean_post_cache($product_id);
    $imported[] = array('id' => $product_id, 'slug' => $product['slug'], 'images' => count($attachment_ids), 'content_words' => str_word_count(wp_strip_all_tags($content)));
}

echo wp_json_encode(array('scope' => 'local WordPress only', 'marker' => $marker, 'expected_products' => count($products), 'imported' => $imported, 'failures' => $failures), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures || count($imported) !== count($products)) {
    exit(1);
}
