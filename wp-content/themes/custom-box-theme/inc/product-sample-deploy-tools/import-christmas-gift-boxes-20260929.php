<?php
/** Import five Christmas gift-box concepts from the 2026-09-29 bundled image and content package. */

if (!defined('ABSPATH')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

defined('ABSPATH') || exit;

$marker = 'christmas-gift-boxes-20260929';
$theme_dir = get_template_directory();
$content_dir = $theme_dir . '/inc/product-content/' . $marker . '/';
$bundle_dir = $theme_dir . '/inc/product-sample-deploy-assets/uploads/2026/09/';
$uploads = wp_upload_dir();
$upload_dir = trailingslashit($uploads['basedir']) . '2026/09/';
$image_roles = array(
    1 => array('hero', 'front hero'),
    2 => array('alternate-angle', 'alternate front angle'),
    3 => array('printed-detail', 'printed detail'),
    4 => array('side-profile', 'side profile'),
    5 => array('top-view', 'top view'),
    6 => array('feature-callouts', 'annotated feature callouts'),
);

$products = array(
    array(
        'slug' => 'custom-christmas-tree-cutout-gift-box',
        'title' => 'Custom Christmas Tree Cutout Gift Box',
        'prefix' => 'christmas-tree-cutout-gift-box',
        'keyword' => 'christmas tree cutout gift box',
        'seo_title' => 'Christmas Tree Cutout Gift Box | VPN Packaging',
        'seo_description' => 'Custom Christmas tree cutout gift box concept with layered front and pull loop. Confirm the backing, closure, size and load by sample.',
        'short' => 'Develop a custom Christmas tree cutout gift box for seasonal retail gifts, campaign kits or corporate gifting that need a distinctive front and a clear opening cue. This concept pairs a red lid with a green tree feature and front pull loop; the images do not confirm whether the feature is a backed opening, inset, applied panel or a functional pull. Define the product size, weight, opening sequence, backing and insert before approving the dieline. Paperboard or rigid-style construction, print coverage, metallic effects and loop materials can be specified against your sample brief. It is intended for brands, gift-set buyers and distributors planning a seasonal presentation. The supplied images are AI-generated design visualizations, not a production sample. The standard MOQ is 1000 boxes; price, material, lead time and final scope are confirmed in the written quote.',
        'seo_category' => 'christmas-packaging',
        'categories' => array('christmas-packaging', 'gift-paper-boxes'),
        'tags' => array('Christmas tree cutout gift box', 'holiday gift packaging', 'seasonal presentation box', 'custom Christmas box', 'tree design packaging'),
        'related_one' => 'custom-green-double-door-christmas-gift-box',
        'related_one_text' => 'double-door Christmas gift box',
        'related_two' => 'custom-white-red-christmas-lid-base-gift-box',
        'related_two_text' => 'lift-off lid and base gift box',
        'feature' => 'Christmas presentation-box concept with red lid, tree-shaped feature, green base and front pull loop; confirm the structure by sample.',
        'industrial_use' => 'Seasonal retail, corporate gifting and gift-set presentation; packed item and shipping route must be specified.',
        'paper_type' => 'Select paperboard, wrapped board or another suitable build from the packed-product brief and approved sample.',
        'box_type' => 'Layered lid-and-base presentation concept; exact cutout, backing and opening construction require confirmation.',
        'shape' => 'Rectangular presentation box with a centered tree-shaped lid feature.',
        'liner_type' => 'Interior insert and lining are not verified in the visual package; specify them from product fit.',
        'color' => 'Concept palette: red, dark green, gold-colored details and cream lettering.',
        'visual_reference' => 'Christmas tree cutout gift-box design concept; not a confirmed production model.',
        'content_file' => 'christmas-tree-cutout-gift-box.html',
        'caption_detail' => 'Printed tree feature and front edge on the Christmas tree cutout gift-box concept; construction is subject to sample approval.',
    ),
    array(
        'slug' => 'custom-green-double-door-christmas-gift-box',
        'title' => 'Custom Green Double Door Christmas Gift Box',
        'prefix' => 'green-double-door-christmas-gift-box',
        'keyword' => 'double door Christmas gift box',
        'seo_title' => 'Green Double Door Christmas Gift Box | VPN Packaging',
        'seo_description' => 'Green double-door Christmas gift-box concept with paired top panels and ribbon. Confirm closure, fit, insert and materials by sample.',
        'short' => 'Plan a green double door Christmas gift box for coordinated gift sets, seasonal retail launches or corporate gifting that benefits from a two-panel reveal. The concept shows paired decorated lid panels, a center meeting line, a red ribbon and a red lower base. It does not verify hinges, panel retention, ribbon function, board grade or the pictured contents. Specify the opening motion, center seam, filled weight, insert layout and artwork safe areas before production sampling. The format can be developed with custom print, paper or board choices and a ribbon detail selected for the brief. It is for brand owners, gift-set assemblers and packaging buyers planning a seasonal presentation. Images are AI-generated visualizations, not a manufactured sample. Standard MOQ: 1000 boxes; confirm final material, price, schedule and order scope by written quote.',
        'seo_category' => 'christmas-packaging',
        'categories' => array('christmas-packaging', 'rigid-boxes', 'gift-paper-boxes', 'corporate-gift-packaging'),
        'tags' => array('double door Christmas gift box', 'holiday gift set box', 'Christmas ribbon box', 'premium seasonal packaging', 'custom presentation box'),
        'related_one' => 'custom-white-red-christmas-lid-base-gift-box',
        'related_one_text' => 'Christmas lid-and-base gift box',
        'related_two' => 'custom-christmas-tree-cutout-gift-box',
        'related_two_text' => 'tree cutout gift-box concept',
        'feature' => 'Christmas gift-set concept with two decorated top panels, center seam, red ribbon and red base; confirm the opening mechanism by sample.',
        'industrial_use' => 'Seasonal gift sets, retail presentation and corporate gifting; actual contents and shipping configuration must be specified.',
        'paper_type' => 'Select wrapped board or folding paperboard construction from the required opening, weight and print brief.',
        'box_type' => 'Paired-panel lid concept; hinge, fold, retention and any underlying tray require confirmation.',
        'shape' => 'Rectangular gift presentation box with two top panels meeting near the center.',
        'liner_type' => 'Red interior is shown in the concept; final liner, divider and product fitment are not specified.',
        'color' => 'Concept palette: evergreen, red, cream, gold-colored details and seasonal illustrations.',
        'visual_reference' => 'Green double-door Christmas gift-box design concept; not a confirmed production model.',
        'content_file' => 'green-double-door-christmas-gift-box.html',
        'caption_detail' => 'Ribbon and center meeting line on the green double-door Christmas gift-box concept; closure details need a production drawing.',
    ),
    array(
        'slug' => 'custom-red-christmas-gable-handle-gift-box',
        'title' => 'Custom Red Christmas Gable Handle Gift Box',
        'prefix' => 'red-christmas-gable-handle-gift-box',
        'keyword' => 'Christmas gable gift box with handle',
        'seo_title' => 'Red Christmas Gable Handle Gift Box | VPN Packaging',
        'seo_description' => 'Red Christmas gable gift-box concept with oval carry opening. Confirm reinforcement, packed weight, closure and dimensions by sample.',
        'short' => 'Develop a red Christmas gable handle gift box for event handouts, seasonal retail gifts or lightweight campaign kits that need an integrated carry opening. The concept shows a peaked folded top, an oval hand slot, a broad front panel and cream-colored winter trees near the base. It does not establish handle reinforcement, closure style, board grade or a safe filled weight. Share the product dimensions and maximum load before setting the handle position, clear height and insert. The front, side and gable panels can be planned for a logo, message, barcode and seasonal print. This format is aimed at brand teams, retailers and distributors seeking a carry-style paper carton. Images are AI-generated design visualizations, not test samples. Standard MOQ is 1000 boxes; quote materials, price, schedule and final specifications in writing.',
        'seo_category' => 'christmas-packaging',
        'categories' => array('christmas-packaging', 'folding-carton-boxes', 'gift-paper-boxes'),
        'tags' => array('Christmas gable gift box with handle', 'handled paper gift box', 'holiday carry packaging', 'gable top carton', 'custom Christmas packaging'),
        'related_one' => 'custom-red-green-christmas-mailer-gift-box',
        'related_one_text' => 'Christmas mailer gift box',
        'related_two' => 'custom-green-double-door-christmas-gift-box',
        'related_two_text' => 'double-door gift-set box',
        'feature' => 'Handled Christmas carton concept with peaked gable top, oval hand opening and red printed body; safe load is not established.',
        'industrial_use' => 'Seasonal retail handover, events and lightweight gift kits; filled weight and carry route must be specified.',
        'paper_type' => 'Select folding-carton paperboard grade and any handle reinforcement from filled weight and sample checks.',
        'box_type' => 'Gable-top folding carton concept with hand opening; closure and reinforcement require confirmation.',
        'shape' => 'Rectangular carton with peaked top panels and an oval handle opening.',
        'liner_type' => 'No verified insert or lining is shown; specify fitment and contact layers from the packed item.',
        'color' => 'Concept palette: red with cream and gold-colored winter-tree graphics.',
        'visual_reference' => 'Red gable-handle Christmas gift-box design concept; not a confirmed production model.',
        'content_file' => 'red-christmas-gable-handle-gift-box.html',
        'caption_detail' => 'Oval carry opening and folded gable on the red Christmas handle-box concept; reinforcement is subject to structural review.',
    ),
    array(
        'slug' => 'custom-red-green-christmas-mailer-gift-box',
        'title' => 'Custom Red and Green Christmas Mailer Gift Box',
        'prefix' => 'red-green-christmas-mailer-gift-box',
        'keyword' => 'Christmas mailer gift box',
        'seo_title' => 'Red Green Christmas Mailer Gift Box | VPN Packaging',
        'seo_description' => 'Red and green Christmas mailer concept with printed inner lid. Confirm board, insert, outer shipper, fit and route by sample.',
        'short' => 'Plan a red and green Christmas mailer gift box for e-commerce gifts, corporate kits or seasonal launches that need a clear inside-lid reveal. The concept shows green exterior panels, red side walls and a red inner lid printed with a holiday message. The open scene contains decorative props, not a confirmed product set, and the images do not identify the board or prove parcel performance. Define each item, the insert layout, closure, outer-shipper plan and destination before approving a filled sample. Exterior print, inside-lid messaging and protective components can be scoped around the brief. It is intended for online retailers, brand teams and gift-set assemblers. Images are AI-generated design visualizations, not shipping tests. Standard MOQ is 1000 boxes; confirm materials, price, schedule and order details in a written quote.',
        'seo_category' => 'christmas-packaging',
        'categories' => array('christmas-packaging', 'corrugated-mailer-boxes', 'gift-paper-boxes', 'corporate-gift-packaging'),
        'tags' => array('Christmas mailer gift box', 'holiday ecommerce packaging', 'gift set mailer box', 'printed inner lid box', 'seasonal shipping packaging'),
        'related_one' => 'custom-white-red-christmas-lid-base-gift-box',
        'related_one_text' => 'removable lid-and-base gift box',
        'related_two' => 'custom-red-christmas-gable-handle-gift-box',
        'related_two_text' => 'gable handle gift box',
        'feature' => 'Fold-over Christmas mailer concept with green outer panels, red side walls and printed inner lid; transit performance is not verified.',
        'industrial_use' => 'E-commerce gifts, seasonal campaigns and corporate gift kits; shipping route and products must be specified.',
        'paper_type' => 'Mailer board, flute, liner and print surface to be selected from packed weight, route and approved sample.',
        'box_type' => 'Fold-over mailer-style gift-box concept; lock, board construction and shipper role require confirmation.',
        'shape' => 'Rectangular box with a broad fold-over lid and presentation interior.',
        'liner_type' => 'No fitment is confirmed; select dividers or cushioning after the real product set is defined.',
        'color' => 'Concept palette: evergreen, red, cream, gold-colored print effects and seasonal illustrations.',
        'visual_reference' => 'Red and green Christmas mailer gift-box design concept; not a confirmed production model.',
        'content_file' => 'red-green-christmas-mailer-gift-box.html',
        'caption_detail' => 'Inside-lid message and red-green panels on the mailer gift-box concept; the image does not establish transit performance.',
    ),
    array(
        'slug' => 'custom-white-red-christmas-lid-base-gift-box',
        'title' => 'Custom White and Red Christmas Lid and Base Gift Box',
        'prefix' => 'white-red-christmas-lid-base-gift-box',
        'keyword' => 'Christmas lid and base gift box',
        'seo_title' => 'White and Red Christmas Lid Base Gift Box | VPN',
        'seo_description' => 'White and red Christmas lid-and-base concept with green ribbon. Confirm lid fit, insert, print finish and shipping plan by sample.',
        'short' => 'Develop a white and red Christmas lid and base gift box for seasonal retail, premium gift sets or corporate gifting that call for a simple lift-off reveal. The concept shows a white illustrated top, a red lower tray and a green ribbon crossing the lid. The images do not confirm a rigid-board build, lid tolerance, ribbon attachment or included products. Set the internal dimensions from the packed items, then specify lid depth, opening fit, insert and shipping protection in the sample brief. Artwork, paper or board, printed colors and ribbon details can be customized after the structure is agreed. It is intended for brand owners, gift-set buyers and packaging distributors. Images are AI-generated design visualizations, not production samples. Standard MOQ is 1000 boxes; price, material and lead time are confirmed in writing.',
        'seo_category' => 'christmas-packaging',
        'categories' => array('christmas-packaging', 'rigid-boxes', 'gift-paper-boxes', 'corporate-gift-packaging'),
        'tags' => array('Christmas lid and base gift box', 'holiday presentation box', 'green ribbon gift box', 'seasonal gift set packaging', 'custom lift-off lid box'),
        'related_one' => 'custom-green-double-door-christmas-gift-box',
        'related_one_text' => 'double-door Christmas presentation box',
        'related_two' => 'custom-red-green-christmas-mailer-gift-box',
        'related_two_text' => 'fold-over Christmas mailer gift box',
        'feature' => 'Two-piece Christmas gift-box concept with illustrated white lid, red base and green ribbon; construction and fit require confirmation.',
        'industrial_use' => 'Seasonal retail, coordinated gift sets and corporate gifting; actual contents and transit configuration must be specified.',
        'paper_type' => 'Select wrapped board or paperboard build from the required lid fit, packed weight and approved sample.',
        'box_type' => 'Separate lid-and-base presentation concept; board build, lid overlap and tolerance require confirmation.',
        'shape' => 'Rectangular gift box with separate-looking top and lower tray.',
        'liner_type' => 'Insert and inner lining are not confirmed in the image package; specify from product dimensions.',
        'color' => 'Concept palette: white illustrated lid, red base, dark green ribbon and multicolor holiday motifs.',
        'visual_reference' => 'White-and-red lid-and-base Christmas gift-box design concept; not a confirmed production model.',
        'content_file' => 'white-red-christmas-lid-base-gift-box.html',
        'caption_detail' => 'Illustrated lid, green ribbon and red base on the Christmas lid-and-base concept; material and fit need sample approval.',
    ),
);

function vpn_xmas_gift_20260929_image_files(string $prefix, string $directory): array {
    $files = glob(trailingslashit($directory) . $prefix . '-0*.webp') ?: array();
    $pattern = '/^' . preg_quote($prefix, '/') . '-0[1-6]-(?:hero|alternate-angle|printed-detail|side-profile|top-view|feature-callouts)\.webp$/i';
    $files = array_values(array_filter($files, static function ($file) use ($pattern) {
        return (bool) preg_match($pattern, wp_basename($file));
    }));
    usort($files, static function ($a, $b) { return strnatcasecmp(wp_basename($a), wp_basename($b)); });
    return $files;
}

function vpn_xmas_gift_20260929_copy_assets(array $products, string $source_dir, string $target_dir): array {
    if (!is_dir($target_dir) && !wp_mkdir_p($target_dir)) {
        return array('Upload directory could not be created.');
    }
    $errors = array();
    foreach ($products as $product) {
        foreach (vpn_xmas_gift_20260929_image_files($product['prefix'], $source_dir) as $source) {
            $target = trailingslashit($target_dir) . wp_basename($source);
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

function vpn_xmas_gift_20260929_attachment(string $file, int $parent_id, array $product, int $view, array $roles): int {
    global $wpdb;
    $filename = wp_basename($file);
    $existing_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_vpn_christmas_gift_boxes_20260929_file' AND meta_value = %s ORDER BY post_id DESC LIMIT 1",
        $filename
    ));
    $role = $roles[$view][1] ?? 'product view';
    $alt = $product['title'] . ' for Christmas gifting, ' . $role . '; AI design visualization';
    $caption = 6 === $view
        ? 'Annotated AI-generated design visualization; labels are illustrative and are not production specifications.'
        : 'AI-generated design visualization, ' . $role . '; not a photograph of a manufactured sample.';

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
    update_post_meta((int) $attachment_id, '_vpn_christmas_gift_boxes_20260929_file', $filename);
    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $alt);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata((int) $attachment_id, $file);
    if (is_array($metadata)) {
        wp_update_attachment_metadata((int) $attachment_id, $metadata);
    }
    return (int) $attachment_id;
}

function vpn_xmas_gift_20260929_inline_figure(int $attachment_id, string $caption, string $alt): string {
    $image = wp_get_attachment_image($attachment_id, 'large', false, array(
        'alt' => $alt,
        'loading' => 'lazy',
        'decoding' => 'async',
        'sizes' => '(max-width: 767px) calc(100vw - 36px), 560px',
    ));
    return '<figure class="product-inline-figure product-inline-figure-small">' . $image . '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
}

function vpn_xmas_gift_20260929_product_link(string $slug, string $anchor): string {
    $post = get_page_by_path($slug, OBJECT, 'product');
    return '<a href="' . esc_url($post ? get_permalink($post) : home_url('/products/christmas-packaging/')) . '">' . esc_html($anchor) . '</a>';
}

$category_ids = array();
$failures = array();
foreach ($products as $product) {
    $content_file = $content_dir . $product['content_file'];
    if (!is_readable($content_file)) {
        $failures[] = $product['slug'] . ': missing content file';
    } else {
        $content = (string) file_get_contents($content_file);
        $word_count = str_word_count(wp_strip_all_tags($content));
        if ($word_count < 1500 || $word_count > 2000) {
            $failures[] = $product['slug'] . ': long description word count is ' . $word_count . ', expected 1500-2000';
        }
        if (preg_match('/<h1\b/i', $content) || 3 !== substr_count($content, '{{FIGURE_')) {
            $failures[] = $product['slug'] . ': long description must have no H1 and three inline figure slots';
        }
        $known_tokens = array(
            '{{FIGURE_OPEN}}', '{{FIGURE_DETAIL}}', '{{FIGURE_TOP}}', '{{CATEGORY_LINK}}',
            '{{RELATED_ONE_URL}}', '{{RELATED_TWO_URL}}', '{{GUIDE_URL}}', '{{CONTACT_URL}}',
        );
        if (preg_match('/\{\{[A-Z0-9_]+\}\}/', str_replace($known_tokens, '', $content))) {
            $failures[] = $product['slug'] . ': unexpected unresolved content placeholder';
        }
    }
    $short_words = str_word_count(wp_strip_all_tags($product['short']));
    if ($short_words < 120 || $short_words > 180) {
        $failures[] = $product['slug'] . ': short description word count is ' . $short_words . ', expected 120-180';
    }
    if (strlen($product['seo_title']) > 60 || strlen($product['seo_description']) > 155 || !$product['keyword']) {
        $failures[] = $product['slug'] . ': SEO title, description or focus keyword is invalid';
    }
    foreach ($product['categories'] as $category_slug) {
        if (!isset($category_ids[$category_slug])) {
            $term = get_term_by('slug', $category_slug, 'product_cat');
            if (!$term || is_wp_error($term)) {
                $failures[] = 'Missing product category: ' . $category_slug;
            } else {
                $category_ids[$category_slug] = (int) $term->term_id;
            }
        }
    }
    $files = vpn_xmas_gift_20260929_image_files($product['prefix'], $bundle_dir);
    if (6 !== count($files)) {
        $failures[] = $product['slug'] . ': expected six source images, found ' . count($files);
    }
    foreach ($files as $file) {
        $info = @getimagesize($file);
        if (!$info || 'image/webp' !== $info['mime'] || 450 !== $info[0] || 570 !== $info[1] || filesize($file) > 102400) {
            $failures[] = 'Invalid WebP source: ' . wp_basename($file);
        }
    }
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
    if ($existing && $marker !== (string) get_post_meta($existing->ID, '_vpn_sample_import', true)) {
        $failures[] = $product['slug'] . ': slug already belongs to a product outside this batch';
    }
}
$christmas_term = get_term_by('slug', 'christmas-packaging', 'product_cat');
if (!$christmas_term || is_wp_error($christmas_term)) {
    $failures[] = 'Primary Christmas product category is missing';
} else {
    $category_ids['christmas-packaging'] = (int) $christmas_term->term_id;
}
if ($failures) {
    fwrite(STDERR, implode(PHP_EOL, array_unique($failures)) . PHP_EOL);
    exit(1);
}

$asset_errors = vpn_xmas_gift_20260929_copy_assets($products, $bundle_dir, $upload_dir);
if ($asset_errors) {
    fwrite(STDERR, implode(PHP_EOL, $asset_errors) . PHP_EOL);
    exit(1);
}

// Create all product records first so cross-links between the five products resolve.
$product_ids = array();
foreach ($products as $product) {
    $existing = get_page_by_path($product['slug'], OBJECT, 'product');
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
    $files = vpn_xmas_gift_20260929_image_files($product['prefix'], $upload_dir);
    if (6 !== count($files)) {
        $failures[] = $product['slug'] . ': expected six uploaded images, found ' . count($files);
        continue;
    }
    $attachment_ids = array();
    foreach ($files as $index => $file) {
        $attachment_id = vpn_xmas_gift_20260929_attachment($file, $product_id, $product, $index + 1, $image_roles);
        if (!$attachment_id) {
            $failures[] = $product['slug'] . ': image attachment failed for ' . wp_basename($file);
        } else {
            $attachment_ids[] = $attachment_id;
        }
    }
    if (6 !== count($attachment_ids)) {
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
        array('label' => 'Accessories', 'value' => 'Insert, ribbon, card, seal and other components must be specified and quoted separately.'),
        array('label' => 'Custom Order', 'value' => 'Accept; confirm the structure and specification by approved sample.'),
        array('label' => 'Liner Type', 'value' => $product['liner_type']),
        array('label' => 'Logo Printing', 'value' => 'Customer logo and artwork to be placed on the approved dieline.'),
        array('label' => 'Printing Handling', 'value' => 'Custom print and finish to be selected from the artwork, material and approved physical sample.'),
        array('label' => 'Color', 'value' => $product['color']),
        array('label' => 'Size', 'value' => 'Customized to the measured product and approved internal fit.'),
        array('label' => 'Thickness', 'value' => 'Board grade and thickness to be selected from the packed-product brief and confirmed by sample.'),
        array('label' => 'Single Piece Price', 'value' => 'Quote after structure, material, quantity, finish, packing and destination are specified.'),
        array('label' => 'Minimum Order Quantity (MOQ)', 'value' => '1000 boxes; confirm eligible specifications and quantity breaks in the written quotation.'),
        array('label' => 'Product Name', 'value' => $product['title']),
        array('label' => 'Design', 'value' => 'Customer-specific artwork and structure; supplied images are AI-generated design visualizations, not production samples.'),
    ));
    update_post_meta($product_id, 'rank_math_focus_keyword', $product['keyword']);
    update_post_meta($product_id, 'rank_math_title', $product['seo_title']);
    update_post_meta($product_id, 'rank_math_description', $product['seo_description']);
    update_post_meta($product_id, 'rank_math_primary_product_cat', $category_ids[$product['seo_category']]);
    update_post_meta($product_id, 'rank_math_robots', array('index', 'follow'));
    set_post_thumbnail($product_id, $attachment_ids[0]);
    update_post_meta($product_id, '_product_image_gallery', implode(',', array_slice($attachment_ids, 1)));

    $content = (string) file_get_contents($content_dir . $product['content_file']);
    $figures = array(
        '{{FIGURE_OPEN}}' => vpn_xmas_gift_20260929_inline_figure($attachment_ids[1], 'Alternate view of the ' . strtolower($product['title']) . '; AI design visualization.', $product['title'] . ' alternate view for seasonal gift packaging, AI design visualization'),
        '{{FIGURE_DETAIL}}' => vpn_xmas_gift_20260929_inline_figure($attachment_ids[2], $product['caption_detail'], $product['title'] . ' printed detail for holiday gift packaging, AI design visualization'),
        '{{FIGURE_TOP}}' => vpn_xmas_gift_20260929_inline_figure($attachment_ids[4], 'Top view of the ' . strtolower($product['title']) . '; confirm panel positions on the production dieline.', $product['title'] . ' top artwork view for seasonal packaging, AI design visualization'),
    );
    $content = str_replace(array_keys($figures), array_values($figures), $content);
    $replacements = array(
        '{{CATEGORY_LINK}}' => '<a href="' . esc_url(home_url('/products/christmas-packaging/')) . '">Christmas gift-box and paper-bag collection</a>',
        '{{RELATED_ONE_URL}}' => vpn_xmas_gift_20260929_product_link($product['related_one'], $product['related_one_text']),
        '{{RELATED_TWO_URL}}' => vpn_xmas_gift_20260929_product_link($product['related_two'], $product['related_two_text']),
        '{{GUIDE_URL}}' => '<a href="' . esc_url(home_url('/christmas-packaging-ideas/')) . '">Christmas packaging design and approval guide</a>',
        '{{CONTACT_URL}}' => '<a href="' . esc_url(home_url('/contact/#quote')) . '">request a Christmas packaging quote</a>',
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
    $imported[] = array(
        'id' => $product_id,
        'slug' => $product['slug'],
        'url' => get_permalink($product_id),
        'images' => count($attachment_ids),
        'short_words' => str_word_count(wp_strip_all_tags($product['short'])),
        'long_words' => str_word_count(wp_strip_all_tags($content)),
    );
}

echo wp_json_encode(array('scope' => 'current WordPress site', 'marker' => $marker, 'expected_products' => count($products), 'imported' => $imported, 'failures' => $failures), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if ($failures || count($imported) !== count($products)) {
    exit(1);
}
